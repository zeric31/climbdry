<?php
if(isset ($_GET['date']) && !empty($_GET['date'])){
	
	$username = "USER";
	$password = "PWD";
	$hostname = "HOST";
	$dbhandle = mysqli_connect($hostname, $username, $password)
		or die("Unable to connect to MySQL");	
	$selected = mysqli_select_db($dbhandle, "DATABASE");
	
	function success(){
		$fh = fopen("success", "w");
		if (!is_resource($fh)) {
			return false;
		}
		fclose($fh);
		return true;
	}
	
	function table_exists($dbhandle, $name){
		try {	
			$query = "SELECT *
					FROM information_schema.tables
					WHERE table_schema = 'DATABASE'
					AND table_name = '".$name."'
					LIMIT 1;";
			$result = mysqli_query($dbhandle, $query);
			if (!$result) {
				throw new Exception(mysqli_error($dbhandle));      
			}
			else {
				if (count(mysqli_fetch_assoc($result)) > 0) return true;
				return false;
			}
		}
		catch (Exception $e) {
				echo $e->getMessage();
				return false;
		}
	}
	
	function rows($dbhandle,$name){
		try {	
			$query = "SELECT COUNT(*)
					FROM `".$name."`";
			$result = mysqli_query($dbhandle, $query);
			if (!$result) {
				throw new Exception(mysqli_error($dbhandle));      
			}
			else {
				return mysqli_fetch_row($result)[0];
			}
		}
		catch (Exception $e) {
				echo $e->getMessage();
				return 0;
		}
	}
	#function delete_success(){
	#	if(file_exists("success")) {
	#		unlink("success");
	#	}
	#	return;
	#}
	
	function actual_table(){
		if(!file_exists('actual_table.txt')) return null;
		$fh = fopen('actual_table.txt','r');
		if(($actual_table = fgets($fh)) == FALSE) return null;
		fclose($fh);
		return $actual_table;
	}
	
	function new_actual_table($new_table){
		if(file_exists('actual_table.txt')) unlink("actual_table.txt");
		$fh = fopen('actual_table.txt','w');
		fwrite($fh, $new_table);
		fclose($fh);
		return;
	}
	
	function add_file_to_mysql($dbhandle, $fileName, $date){
		$file = fopen($fileName, "r");
		$names=fgetcsv($file);
		try {	
			$query = "CREATE TABLE `".$date."` (
				id INT NOT NULL AUTO_INCREMENT,
				H" . $names[0] . " DECIMAL(7,5 ) NULL,
				H" . $names[1] . " DECIMAL(7,5 ) NULL,
				H" . $names[2] . " DECIMAL(7,5 ) NULL,
				H" . $names[3] . " DECIMAL(7,5 ) NULL,
				H" . $names[4] . " DECIMAL(7,5 ) NULL,
				H" . $names[5] . " DECIMAL(7,5 ) NULL,
				H" . $names[6] . " DECIMAL(7,5 ) NULL,
				H" . $names[7] . " DECIMAL(7,5 ) NULL,
				H" . $names[8] . " DECIMAL(7,5 ) NULL,
				H" . $names[9] . " DECIMAL(7,5 ) NULL,
				H" . $names[10] . " DECIMAL(7,5 ) NULL,
				H" . $names[11] . " DECIMAL(7,5 ) NULL,
				H" . $names[12] . " DECIMAL(7,5 ) NULL,
				H" . $names[13] . " DECIMAL(7,5 ) NULL,
				H" . $names[14] . " DECIMAL(7,5 ) NULL,
				H" . $names[15] . " DECIMAL(7,5 ) NULL,
				H" . $names[16] . " DECIMAL(7,5 ) NULL,
				H" . $names[17] . " DECIMAL(7,5 ) NULL,
				H" . $names[18] . " DECIMAL(7,5 ) NULL,
				H" . $names[19] . " DECIMAL(7,5 ) NULL,
				PRIMARY KEY (id)
			)";
			$result = mysqli_query($dbhandle, $query);
			if (!$result) {
				throw new Exception(mysqli_error($dbhandle));      
			}
		}
		catch (Exception $e) {
				echo $e->getMessage();
				return false;
		}

		while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {	
			set_time_limit(30);
			try {
				$query = "INSERT INTO `".$date."` (H" . $names[0] . ",H" . $names[1] . ",H" . $names[2] . ",H" . $names[3] . ",H" . $names[4] . ",H" . $names[5] . ",H" . $names[6] . ",H" . $names[7] . ",H" . $names[8] . ",H" . $names[9] . ",H" . $names[10] . ",H" . $names[11] . ",H" . $names[12] . ",H" . $names[13] . ",H" . $names[14] . ",H" . $names[15] . ",H" . $names[16] . ",H" . $names[17] . ",H" . $names[18] . ",H" . $names[19] . ")
						  values ('" . $column[0] . "','" . $column[1] . "','" . $column[2] . "','" . $column[3] . "','" . $column[4] . "','" . $column[5] . "','" . $column[6] . "','" . $column[7] . "','" . $column[8] . "','" . $column[9] . "','" . $column[10] . "','" . $column[11] . "','" . $column[12] . "','" . $column[13] . "','" . $column[14] . "','" . $column[15] . "','" . $column[16] . "','" . $column[17] . "','" . $column[18] . "','" . $column[19] . "')";
				$result = mysqli_query($dbhandle, $query);
				if (!$result) {
					throw new Exception(mysqli_error($dbhandle));      
				}
			}
			catch (Exception $e) {
				echo $e->getMessage();
				return false;
			}
		}
		return true;
	}
	
	function delete_table($dbhandle,$name){
		try {
			$query = "DROP TABLE `".$name."`";
			$result = mysqli_query($dbhandle, $query);
			if (!$result) {
				throw new Exception(mysqli_error($dbhandle));      
			}
		}
		catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
		return true;
	}
	
	$date = mysqli_real_escape_string($dbhandle, $_GET['date']);
	$fileName = $date.".csv";
	$actual_table = actual_table();

	if(!file_exists($fileName)) return false;
	if(file_exists("success")) return true;
	
	if(table_exists($dbhandle, $date)){
		$a=rows($dbhandle, $date);
		sleep(1);
		$b=rows($dbhandle, $date);
		if ($b>$a) exit;
		else delete_table($dbhandle,$date);
	}

	if(add_file_to_mysql($dbhandle,$fileName, $date)) {
		if(isset($actual_table) && !empty($actual_table)) delete_table($dbhandle,$actual_table);
		new_actual_table($date);
		success();
	}
	else {
		delete_table($dbhandle,$date);
	}
}
?>
