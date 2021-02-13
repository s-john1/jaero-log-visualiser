<?php

require 'config.php';

$locations = [];

$files = scandir(LOG_DIR);

foreach($files as $file) {

	if (substr($file, 0, 9) == "acars-log") {
		$data = fopen(LOG_DIR . $file, "r");
		
		if ($data != false) {
			while (!feof($data)) { // Loop through entire file
				$line = fgets($data);
				
				preg_match('/Lat = (-?\d*\.?\d+) Long = (-?\d*\.?\d+)/i', $line, $matches);
				
				if (count($matches) == 3) { // If regex match successful
					array_push($locations, [$matches[1], $matches[2]]);
				}
			}
		}
	}
}

if (count($locations) == 0) {
	die("Unable to find any positional data");
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Heatmap = JAERO Log Visualiser</title>
  
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css"
			integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
			crossorigin=""/>
		<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"
			integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew=="
			crossorigin=""></script>
			
		<script src="leaflet-heat.js"></script>
		
		<style>
			#map {
				height: 100%;
			}
			html, body {
				height: 100%;
				margin: 0;
				padding: 0;
			}
		</style>
  </head>
  
	<body>
		<div id="map"></div>
		<script>
			var map = L.map('map').setView([30, 26], 3);

			var tiles = L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors',
			}).addTo(map);
			
			var locations = <?= json_encode($locations) ?>;
			
			var options = {
				radius: 4,
				blur: 3,
				minOpacity: 0.4
			};
			heat = L.heatLayer(locations, options).addTo(map);
		</script>
	</body>
</html>