<?php

$username = "USER";
$password = "PWD";
$hostname = "HOST";
$dbhandle = mysqli_connect($hostname, $username, $password)
	or die("Unable to connect to MySQL");	
$selected = mysqli_select_db($dbhandle, "DATABASE");

/*$files = [];
if ($handle = opendir('.')) {
    while (false !== ($file = readdir($handle)))
    {
        if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'json')
        {
            array_push($files, $file);
        }
    }
    closedir($handle);
}

foreach($files as $i=>$file)*/
$file = "FILENAME";
	$json = file_get_contents($file);
	$json_a = json_decode($json, true);
	foreach ($json_a as $key => $val) {
		foreach ($val as $key2 => $val2) {
			if($key2=="nom") $nom=$val2;
			if($key2=="coor_1") $lat=$val2;
			if($key2=="coor_2") $lng=$val2;
		}
		add($dbhandle, $nom, $lat, $lng, "");
	}
//}

function add($dbhandle, $name, $y, $x, $url){
	$x0 = round($x*4)/4-0.125; #real for database-0.125
	$x1 = $x0 + 0.25; #database -0.125
	$y0 = round($y*4)/4-0.125; # database -0.125
	$y1 = $y0 + 0.25; #database -0.125
	
	$X0=$x0-0.125;
	$Y0=$y0-0.125;
	$X1=$x1-0.125;
	$Y1=$y1-0.125;

	$c00=($x1-$x)*($y1-$y)/($x1-$x0)/($y1-$y0);
	$c10=($x-$x0)*($y1-$y)/($x1-$x0)/($y1-$y0);
	$c01=($x1-$x)*($y-$y0)/($x1-$x0)/($y1-$y0);
	$c11=($x-$x0)*($y-$y0)/($x1-$x0)/($y1-$y0);

	$id00=id($dbhandle,$Y0,$X0);
	$id10=id($dbhandle,$Y1,$X0);
	$id01=id($dbhandle,$Y0,$X1);
	$id11=id($dbhandle,$Y1,$X1);

	add_mysql($dbhandle,$name,$y,$x,$id00,$id01,$id10,$id11,$c00,$c01,$c10,$c11,$url);
}

function id($dbhandle,$latitude,$longitude){
	try {	
		$query = "SELECT id
				FROM coordinates
				WHERE latitude = '".$latitude."'
				AND longitude = '".$longitude."';";
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
			return null;
	}
}

function add_mysql($dbhandle,$name,$latitude,$longitude,$id00,$id01,$id10,$id11,$c00,$c01,$c10,$c11,$url){
	try {	
		$query = "INSERT into spots
				values('','".mysqli_real_escape_string($dbhandle, $name)."','".$latitude."','".$longitude."','".$id00."','".$id01."','".$id10."','".$id11."','".$c00."','".$c01."','".$c10."','".$c11."','".$url."')";
		$result = mysqli_query($dbhandle, $query);
		if (!$result) {
			throw new Exception(mysqli_error($dbhandle));      
		}
	}
	catch (Exception $e) {
			echo $e->getMessage();
			return null;
	}
}

?>