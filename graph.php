<?php

require 'config.php';

$timestamps = [];
$graphData = [];

$files = scandir(LOG_DIR);

// Disregard files which aren't log files
foreach($files as $key => $file) {
	if (substr($file, 0, 9) != "acars-log") {
		unset($files[$key]);
	}
}

sort($files);

$file = fopen(LOG_DIR . end($files), "r"); // Open last file
if ($file != false) {
	while (!feof($file)) { // Loop through entire file
		$line = fgets($file);
		
		preg_match('/([0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}) ([0-9]{1,2}-[0-9]{1,2}-[0-9]{1,2})/', $line, $matches); // Match log timestamps
		
		if (count($matches) == 3) { // If regex match successful
			array_push($timestamps, $matches[0]); // Add time to array
		}
	}
} else {
	die("Error opening file");
}

// Loop through timestamps and group them by minute
foreach($timestamps as $time) {
	$dt = DateTime::createFromFormat('H:i:s d-m-y', $time);
	$dt->setTime($dt->format('H'), $dt->format('i'), 0); // Remove seconds from date
	$date = $dt->format(DateTime::ATOM); // Format date so JavaScript can parse it
	
	if (isset($graphData[$date])) {
		$graphData[$date]++;
	} else {
		$graphData[$date] = 1;
	}
}

ksort($graphData); // Sort by timestamps in ascending order
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Graph - JAERO Log Visualiser</title>
		
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.js"
		integrity="sha512-zO8oeHCxetPn1Hd9PdDleg5Tw1bAaP0YmNvPY8CwcRyUk7d7/+nyElmFrB6f7vg4f7Fv4sui1mcep8RIEShczg=="
		crossorigin="anonymous"></script>
	</head>
	
	<body>
		<canvas id="chart"></canvas>
		
		<script>
			var times = <?= json_encode(array_keys($graphData)) ?>;
			var data = <?= json_encode(array_values($graphData)) ?>;
			
			// Parse dates
			var labels = [];
			times.forEach(time => labels.push(new Date(time)));
		
			var ctx = document.getElementById('chart');
			
			var line = new Chart(ctx, {
			type: 'bar',
			data: {
				labels: labels,
				
				datasets: [{
					label: 'Messages Per Minute',
					backgroundColor: 'green',
					hoverBackgroundColor: 'black',
					data: data
				}]
			},
			options: {
				scales: {
					xAxes: [{
						type: 'time',
						scaleLabel: {
							display: true,
							labelString: 'Time'
						}
					}]
				}
			}
		});
		</script>
	</body>	
</html>