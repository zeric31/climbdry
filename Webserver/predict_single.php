<?php

function predict_single($id, $t_min, $t_max, $offset){
	$username = "USER";
	$password = "PWD";
	$hostname = "HOST";
	$dbhandle = mysqli_connect($hostname, $username, $password)
		or die("Unable to connect to MySQL");	
	$selected = mysqli_select_db($dbhandle, "DATABASE");
	
	$id = mysqli_real_escape_string($dbhandle, $id);
	
	$actual_table = actual_table();
	$parameters=parameters($dbhandle,$id);
	if(is_null($parameters)) return json_encode('');
	$results = [];
	$real_results=[];

	$SW = prediction_daily($dbhandle, $actual_table, $parameters["ids"][0], $t_min, $t_max, $offset);
	$SE = prediction_daily($dbhandle, $actual_table, $parameters["ids"][1], $t_min, $t_max, $offset);
	$NW = prediction_daily($dbhandle, $actual_table, $parameters["ids"][2], $t_min, $t_max, $offset);
	$NE = prediction_daily($dbhandle, $actual_table, $parameters["ids"][3], $t_min, $t_max, $offset);
	
	$counter=0;
	$total_rainfall=0;
	$max=0;
	foreach(array_keys($SW) as $key){
		$daily_rainfall = $SW[$key]*$parameters["coeffs"][0]+$SE[$key]*$parameters["coeffs"][1]+$NW[$key]*$parameters["coeffs"][2]+$NE[$key]*$parameters["coeffs"][3];
		$results[$counter] = array("label" => $key, "y" => (string)$daily_rainfall);
		if($daily_rainfall>$max) $max=$daily_rainfall;
		$total_rainfall += $daily_rainfall;
		$counter++;
	}
	$real_results[0] = array("id" => (string)$id, "name" => $parameters["name"], "url" => $parameters["url"], "total" => (string)$total_rainfall, "max" => (string)$max);
	$real_results[1] = $results;
	return json_encode($real_results);
}

function prediction_daily($dbhandle, $table, $id, $t_min, $t_max, $offset){
	try {	
		$query = "SELECT *
				FROM `".$table."`
				WHERE id = '".$id."';";
		$result = mysqli_query($dbhandle, $query);
		if (!$result) {
			throw new Exception(mysqli_error($dbhandle));      
		}
		else {
			$row = mysqli_fetch_array($result);
			
			$t_sql = substr($table, 0, 4)."-".substr($table, 4, 2)."-".substr($table, 6, 2)." ".substr($table, -2).":00";
			$datetime = new DateTime($t_sql);
			if($offset>0) $datetime->add(new DateInterval("PT".$offset."H"));
			elseif($offset<0) $datetime->sub(new DateInterval("PT".abs($offset)."H"));
			$datetime->add(new DateInterval("PT".$t_min."H"));
			$day_aggr = $datetime->format('D');
			$t=$t_min;
			$rainfall=0;
			while($t<$t_max){
				$t+=6;
				$rainfall+=$row["H".$t];
				$results[$day_aggr]=$rainfall;
				$datetime->add(new DateInterval("PT6H"));
				$day = $datetime->format('D');
				if($day !== $day_aggr){
					$day_aggr = $day;
					$rainfall=0;
				}
			}
			return $results;
		}
	}
	catch (Exception $e) {
			echo $e->getMessage();
			return null;
	}
}

function predict_single_chart($id, $t_min, $t_max, $offset){
	$username = "USER";
	$password = "PWD";
	$hostname = "HOST";
	$dbhandle = mysqli_connect($hostname, $username, $password)
		or die("Unable to connect to MySQL");	
	$selected = mysqli_select_db($dbhandle, "DATABASE");
	
	$id = mysqli_real_escape_string($dbhandle, $id);
	
	$actual_table = actual_table();
	$parameters=parameters($dbhandle,$id);
	if(is_null($parameters)) return json_encode('');
	$data = [];
	$labels=[];
	$results=[];

	$SW = prediction_hourly($dbhandle, $actual_table, $parameters["ids"][0], $t_min, $t_max, $offset);
	$SE = prediction_hourly($dbhandle, $actual_table, $parameters["ids"][1], $t_min, $t_max, $offset);
	$NW = prediction_hourly($dbhandle, $actual_table, $parameters["ids"][2], $t_min, $t_max, $offset);
	$NE = prediction_hourly($dbhandle, $actual_table, $parameters["ids"][3], $t_min, $t_max, $offset);
	
	$counter=0;
	$total_rainfall=0;
	$max=0;
	foreach(array_keys($SW) as $key){
		$rainfall = $SW[$key]*$parameters["coeffs"][0]+$SE[$key]*$parameters["coeffs"][1]+$NW[$key]*$parameters["coeffs"][2]+$NE[$key]*$parameters["coeffs"][3];
		array_push($data, round($rainfall,1));
		array_push($labels, $key);
		if($rainfall>$max) $max=$rainfall;
		$total_rainfall += $rainfall;
		$counter++;
	}
	$results[0] = array("id" => (string)$id, "name" => $parameters["name"], "url" => $parameters["url"], "total" => (string)$total_rainfall, "max" => (string)$max);
	$results[1] = $labels;
	$results[2] = $data;
	return json_encode($results);
}

function prediction_hourly($dbhandle, $table, $id, $t_min, $t_max, $offset){
	try {	
		$query = "SELECT *
				FROM `".$table."`
				WHERE id = '".$id."';";
		$result = mysqli_query($dbhandle, $query);
		if (!$result) {
			throw new Exception(mysqli_error($dbhandle));      
		}
		else {
			$row = mysqli_fetch_array($result);
			$t_sql = substr($table, 0, 4)."-".substr($table, 4, 2)."-".substr($table, 6, 2)." ".substr($table, -2).":00";
			$datetime = new DateTime($t_sql);
			if($offset>0) $datetime->add(new DateInterval("PT".$offset."H"));
			elseif($offset<0) $datetime->sub(new DateInterval("PT".abs($offset)."H"));
			$datetime->add(new DateInterval("PT".$t_min."H"));
			$results=[];
			for($i=$t_min; $i<$t_max; $i=$i+6){
				$rainfall=$row["H".($i+6)];
				$from = $datetime->format('D H:i');
				$datetime->add(new DateInterval("PT6H"));
				$to = $datetime->format('D H:i');
				$results=array_merge($results,array($from." - ".$to => $rainfall));
			}
			return $results;
		}
	}
	catch (Exception $e) {
			echo $e->getMessage();
			return null;
	}
}

function parameters($dbhandle, $id){
	try {	
		$query = "SELECT *
				FROM `spots`
				WHERE id = '".$id."';";
		$result = mysqli_query($dbhandle, $query);
		if (!$result) {
			throw new Exception(mysqli_error($dbhandle));      
		}
		else {
			if(!mysqli_num_rows($result)) return null;
			$row = mysqli_fetch_row($result);		
			return array("ids" => array($row[4],$row[5],$row[6],$row[7]),
						"coeffs" => array($row[8],$row[9],$row[10],$row[11]),
						"name" => $row[1],
						"url" => $row[12]);
		}
	}
	catch (Exception $e) {
			echo $e->getMessage();
			return null;
	}
}

function actual_table(){
	if(!file_exists('./upload/actual_table.txt')) return null;
	$fh = fopen('./upload/actual_table.txt','r');
	if(($actual_table = fgets($fh)) == FALSE) return null;
	fclose($fh);
	return $actual_table;
}
?>
