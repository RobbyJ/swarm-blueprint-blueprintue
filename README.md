# swarm-blueprint-blueprintue

AUTHORS

Developed at Games @ Breda University of Applied Sciences by:

Resul Çelik (Resul_Celik@hotmail.com), Robbie Grigg (grigg.r@buas.nl)

DESCRIPTION

Plugin for Swarm (web-layer to Perforce) that allows for the viewing of Unreal Engine 4 (UE4) Blueprints. When it finds Blueprint files in the text format (COFF format) with the .COPY extension this will display the Blueprint within the web-page.

This uses BlueprintUE.com (http://blueprintue.com) - you will need to contact them for an API license so that you can connect and submit Blueprints which can be done through their website.

An example of this in operation can be seen here: https://youtu.be/3iQLJbWPNCs

CURRENT LIMITATIONS

- Blueprints need to be in the COFF file format which is done by exporting the Blueprint in the editor (right-click on a Blueprint->Asset Action->Export, you get a .COPY file which contains the Blueprint in COFF format).
- The COFF file needs the following extension where the last three numbers are the version of the engine:  *.uebp424
- Latest version of UE4 may result in the Blueprint not displaying properly.

FUTURE IMPROVEMENTS

- Future improvements may include a serverside tool to extract the Blueprint COFF file automatically out of the .UASSET file and therefore also determining the version automatically.
- A plugin would be great to automate the creation of the COFF text format files of the Blueprints with the version in the file extension.
- If the BlueprintUE.com API could confirm whether a particular file+version has already been uploaded or not then the local file upload caching could be removed.

