<?php
	
	//get input parameter
	$part_num = trim($_GET["****"] ?? '');
	$part_num = strtoupper($_GET["****"]);
	
	//set python file name
	$pythonFile = "oracle.py";
	
	//check if part number exist
	$query = "\"SELECT **** FROM **** WHERE **** = '" . $part_num . "'\"";
	$command = "python " . $pythonFile . " " . $query;
	$result = exec($command);
	
	if (empty($result)){
		echo "null";
		exit();
	}
	
	//select 2DID data
	$query = "\"" . "SELECT **** AOI, **** EDI FROM **** WHERE **** = '" . $part_num . "'" . "\"";
	$command = "python " . $pythonFile . " " . $query;
	$result = exec($command);
	
	//decode result
	$json_result = json_decode($result);
	
	foreach ($json_result as $item){
		if (empty($item -> AOI)){
			$aoi = "N";
		} else {
			$aoi = $item -> AOI;
		}
		
		if (empty($item -> EDI)){
			$edi = "N";
		} else {
			$edi = $item -> EDI;
		}
	}
	
	//return result
	$return = "2did,aoi," . $aoi . ",edi," . $edi;
	echo $return;
	
?>