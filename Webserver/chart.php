<?php

ob_end_clean();

header('Content-Type: application/json');

include("predict_single.php");

if(isset ($_POST['id']) && isset ($_POST['t_min']) && isset ($_POST['t_max']) && !empty($_POST['t_max']) && isset ($_POST['offset'])){
	$id=$_POST['id'];
	$t_min=$_POST['t_min'];
	$t_max=$_POST['t_max'];
	$offset=$_POST['offset'];
	
	if(empty($id)) echo json_encode('');
	else{
		$json = predict_single_chart($id, $t_min, $t_max, $offset);
		if(empty(json_decode($json))) return;
		echo $json;
	}
}

die();
?>