<?php
namespace BlueprintVis;

use Files\Format\Handler as FormatHandler;
use Zend\Mvc\MvcEvent;

class Module
{
	public function onBootstrap(MvcEvent $event)
	{
		$application = $event->getApplication();
		$services	 = $application->getServiceManager();
		$formats	 = $services->get('formats');

		$formats->addHandler(
			new FormatHandler(
				// Can-preview callback
				function ($file, $extension, $mimeType, $request) use ($config) 
				{
					// Check whether the extention is supported by this module:
					//   This will look for file extensions of *.uebpVER
					//   e.g. for a blueprint called playercontroller from version 4.19 then this would be: playercontroller.uebp419
					//   NOTE: This expects the COFF blueprint text format.
					return (strcmp(strtolower(substr($extension,0,4)), 'uebp')==0);
				},

				// Render-preview callback
				function ($file, $extension, $mimeType, $request) use ($services) 
				{
					// BlueprintUE Configuration 
					$blueprintRenderURL = 'https://blueprintue.com/render/';
					$blueprintToken = 'TODO: GET YOUR OWN KEY FROM http://blueprintue.com';  // API Key, DO NOT DISTRIBUTE!!!
					$uploadURL = 'https://blueprintue.com/api/upload';
					$uploadMaxSize  = 26214400;	//1048576 1MB * 25; // 25MB
					$uploadExposure = 'unlisted'; //set exposure to private as all the viewing will be done from swarm - options are public, unlisted (private is an option but this will cause it not to display
					$uploadExpiry = 'never'; //set the expiry time which defaults to never. Other options are 3600, 86400, 604800 
					
					// Define the BlueprintUE filename
					$blueprintAuthor = 'unknown';
					$blueprintRevision = 'rev1';
					
					// Get information needed from Perforce
					$fileLogItems = $file->getFilelog();
					$currentFileRevisions = $fileLogItems[ $file->getDepotFilename() ];
					
					for($i = 0; $i < sizeof($currentFileRevisions); $i++)
					{
							if ($currentFileRevisions[$i]['change'] === $file->getStatus('headChange'))
							{
									$blueprintAuthor = $currentFileRevisions[$i]['user'];
									$blueprintRevision = 'rev'.$currentFileRevisions[$i]['rev'];
							}
					}
					$blueprintUEName = urlencode($blueprintAuthor.'-'.$blueprintRevision.'-'.$file->getDepotFilename());
					
					
					// Set up the cache file for recording what is or is not already uploaded to BlueprintUE
					$cacheFileName = realpath(dirname(__FILE__)).'/content/uploaded.rev';
					// Create the path if it does not exist
					$dirname = dirname($cacheFileName);
					if (!is_dir($dirname))
					{
						mkdir($dirname, 0755, true);
					}
					
					// Check if file is already uploaded
					$myFile = fopen($cacheFileName, "w+", TRUE);
					if($myFile === FALSE)
						return 'Cannot create or open file: '.$cacheFileName;
					
					// Check if name of this file is already in the list - been uploaded before
					$fileLine = '';
					$renderString = '';
					// TODO - Request that the API responds to let us know that this file already exists.
					// This can replace the local file caching done here
					while (true)
					{
						// Check if fileline contains the name of this file
						if (strpos($fileLine, $blueprintUEName)!==FALSE)
						{
							$fileEntries = explode("\t", $fileLine);
							if (count($fileEntries)==2)
							{
									$renderString = str_replace(PHP_EOL, '', $fileEntries[1]);
									break;
							}
							else
							{
									fclose($myFile);
								  return 'Uploaded before, but token missing from cache file: '.$cacheFileName;
							}
						}
						// Otherwise check if we are at the end of the file
						else
							if (feof($myFile))
									break;
							else  // else go to the next line
									$fileLine = fgets($myFile);
					}
			
					// Blueprint is already uploaded previously, render that one instead of sending it to the website
					if ($renderString != '')
					{
						fclose($myFile);
						return '<iframe frameborder=0 scrolling=no width=100% height="500px" src="'.$blueprintRenderURL.$renderString.'"></iframe>';
					}
					
					// Blueprint needs to be uploaded to website, get the contents of the file to upload to BlueprintUE
					$contents = $file->getDepotContents(
						array(
							$file::UTF8_CONVERT	=> true,
							$file::UTF8_SANITIZE => true,
							$file::MAX_FILESIZE	=> $uploadMaxSize
						)
					);
					// Replace the ' with /' in the blueprint file
					$contents = str_replace("'", "\'", $contents);

					// Create fields
					$fields = array(
						'title' => $blueprintUEName,
						'exposure' => $uploadExposure,
						'expiration' => $uploadExpiry,
						'version' => urlencode( substr($extension, 4, 1).'.'.substr($extension, 5, 2) ), //the version of the blueprint - assumed this is in the file extension.
						'blueprint'=> urlencode($contents)
					);
				
					$fieldsString = '';
					foreach ($fields as $key=>$value)
							$fieldsString .= $key.'='.$value.'&';
					$fieldsString = rtrim($fieldsString, '&');
			
					if (!function_exists('curl_version'))
					{
							fclose($myFile);
							return 'Curl PHP package is not installed or enabled.';
					}
					
					// Set the url, number of POST vars, POST data
					$c = curl_init();
					curl_setopt($c, CURLOPT_URL, $uploadURL);
					curl_setopt($c, CURLOPT_HTTPHEADER, array('X-Token: '.$blueprintToken));
					curl_setopt($c, CURLOPT_POST, count($fields));
					curl_setopt($c, CURLOPT_POSTFIELDS, $fieldsString);
					curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

					// Execute post of Blueprint to BlueprintUE
					$result = curl_exec($c);
					curl_close($c);
					
					// Check if the site returned an error - then show the error
					if(strpos($result,'error') !== FALSE)
					{
							fclose($myFile);
							return 'Error from BlueprintEU: '. $result;
					}
					// Modify result from BlueprintUE to only get data we need
					$endResult = str_replace("{\"key\":\"","",$result);
					$endResult = str_replace("\"}","",$endResult);
			
					// Place newly created Blueprint in cache file database of Blueprints uploaded
					if (!fwrite($myFile, $blueprintUEName."\t".$endResult.PHP_EOL))
					{
						fclose($myFile);
						return 'Unable to record blueprint entry in file: '.$cacheFileName;
					}
					fclose($myFile);

					// Render the render of the Blueprint
					return '<iframe frameborder=0 scrolling=no width=100% height="500px" src="'.$blueprintRenderURL.$endResult.'"></iframe>';
				}
			),
			'BlueprintVis'
		);
	}

	public function getConfig()
	{
		return array();
	}
    
}
