<?php

$username = "USER";
$password = "PWD";
$hostname = "HOST";
$dbhandle = mysqli_connect($hostname, $username, $password)
	or die("Unable to connect to MySQL");	
$selected = mysqli_select_db($dbhandle, "DATABASE");


function geojson_spot($dbhandle){
	try {	
		$query = "SELECT *
				FROM `spots`";
		$result = mysqli_query($dbhandle, $query);
		if (!$result) {
			throw new Exception(mysqli_error($dbhandle));      
		}
		else {		
			$geojson = array(
			   'type'      => 'FeatureCollection',
			   'features'  => array()
			);
			# Loop through rows to build feature arrays
			while($row = mysqli_fetch_array($result)) {
				$feature = array(
					'id' => $row['id'],
					'type' => 'Feature', 
					'geometry' => array(
						'type' => 'Point',
						# Pass Longitude and Latitude Columns here
						'coordinates' => array($row['lng'], $row['lat'])
					),
					# Pass other attribute columns here
					'properties' => array(
						'name' => $row['name'],
						'url' => $row['url'],
						)
					);
				# Add feature arrays to feature collection array
				array_push($geojson['features'], $feature);
			}

			return json_encode($geojson);;
		}
	}
	catch (Exception $e) {
			echo $e->getMessage();
			return null;
	}
}

$fp = fopen('list.json', 'w');
fwrite($fp, geojson_spot($dbhandle));
fclose($fp);

?>
