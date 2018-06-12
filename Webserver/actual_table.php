<?php

ob_end_clean();

header('Content-Type: application/json');

if(!file_exists('./upload/actual_table.txt')) return null;
$fh = fopen('./upload/actual_table.txt','r');
if(($actual_table = fgets($fh)) == FALSE) return null;
fclose($fh);
echo json_encode($actual_table);

die();
?>
