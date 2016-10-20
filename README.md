# swarm-blueprint-blueprintue

AUTHORS
Developed at NHTV Breda University of Applied Sciences at IGAD (International Game Architecture and Design) by:
Resul Çelik (Resul_Celik@hotmail.com), Robbie Grigg (grigg.r@nhtv.nl)

DESCRIPTION
Plugin for Swarm (web-layer to Perforce) that allows for the viewing of Unreal Engine 4 (UE4) Blueprints. When it finds Blueprint files in the text format (COFF format) with the .COPY extension this will display the Blueprint within the web-page.

This uses BlueprintUE.com (http://blueprintue.com) - you will need to contact them for an API license so that you can connect and submit Blueprints which can be done through their website.

An example of this in operation can be seen here: https://youtu.be/3iQLJbWPNCs

CURRENT LIMITATIONS
- Blueprint needs to be in the COFF file format which is done by exporting the Blueprint in the editor (right-click on a Blueprint->Asset Action->Export, you get a .COPY file which contains the Blueprint in COFF format). Future improvements may include a serverside tool to extract the Blueprint COFF file automatically out of the .UASSET file.
- Latest version of UE4 may result in the Blueprint not displaying properly.


