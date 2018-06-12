<?php

ob_end_clean();

header('Content-Type: application/json');

include("predict_single.php");

if(isset ($_POST['ids']) && isset ($_POST['t_min']) && isset ($_POST['t_max']) && !empty($_POST['t_max']) && isset ($_POST['offset'])){
	function merge_sorted_arrays_by_field ($merge_arrays, $sort_field, $sort_desc = false, $limit = 0) 
	{ 
		$array_count = count($merge_arrays); 
		
		// fast special cases... 
		switch ($array_count) 
		{ 
			case 0: return array(); 
			case 1: return $limit ? array_slice(reset($merge_arrays), 0, $limit) : reset($merge_arrays); 
		} 
		
		if ($limit === 0) 
			$limit = PHP_INT_MAX; 
		
		// rekey merge_arrays array 0->N 
		$merge_arrays = array_values($merge_arrays); 
		$best_array = false; 
		$best_value = false; 
		
		$results = array(); 
		
		// move sort order logic outside the inner loop to speed things up 
		if ($sort_desc) 
		{ 
			for ($i = 0; $i < $limit; ++$i) 
			{ 
				for ($j = 0; $j < $array_count; ++$j) 
				{ 
					// if the array $merge_arrays[$j] is empty, skip to next 
					if (false === ($current_value = $merge_arrays[$j])) 
						continue; 

					// if we don't have a value for this round, or if the current value is bigger...
					if ($best_value === false || $current_value[0][$sort_field] > $best_value[0][$sort_field]) 
					{ 
						$best_array = $j; 
						$best_value = $current_value; 
					} 
				} 
				
				// all arrays empty? 
				if ($best_value === false) 
					break; 
				
				$results[] = $best_value; 
				$merge_arrays[$best_array]=false;
				$best_value = false; 
			} 
		} 
		else 
		{ 
			for ($i = 0; $i < $limit; ++$i) 
			{ 
				for ($j = 0; $j < $array_count; ++$j) 
				{ 
					if (false === ($current_value = $merge_arrays[$j])) 
						continue; 
					
					// if we don't have a value for this round, or if the current value is smaller... 
					if ($best_value === false || $current_value[0][$sort_field] < $best_value[0][$sort_field]) 
					{ 
						$best_array = $j; 
						$best_value = $current_value; 
					} 
				} 
				
				// all arrays empty? 
				if ($best_value === false) 
					break; 
				
				$results[] = $best_value; 
				$merge_arrays[$best_array]=false;
				$best_value = false; 
			} 
		} 
		
		return $results; 
	} 

	$json_data=[];
	$t_min=$_POST['t_min'];
	$t_max=$_POST['t_max'];
	$offset=$_POST['offset'];
	if(empty($_POST['ids'])) echo json_encode('');
	else{
		$ids = explode(",", $_POST['ids']);
		$max = 0;
		
		for($i=0; $i<count($ids); $i++){
			$json = predict_single($ids[$i], $t_min, $t_max, $offset);
			$json_data[$i] = json_decode($json, true);
			$local_max=floatval($json_data[$i][0]["max"]);
			if($local_max>$max) $max=$local_max;
		}

		$sorted = merge_sorted_arrays_by_field ($json_data, "total", false, $limit = 0);
		if(count($ids)==1){
			$tmp=$sorted;
			unset($sorted);
			$sorted[0]=$tmp;
		}
		$sorted[] = (string)$max;
		echo json_encode($sorted);
	}
}
die();
?>