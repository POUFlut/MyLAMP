<?php
	//使用POST觸發
	if ($_SERVER['REQUEST_METHOD'] === 'GET'){
		
		session_start();
		
		//儲存傳送來的資料
		$part_num = trim($_GET["****"] ?? '');
		$part_num = strtoupper($part_num);
		
		//set python file name
		$pythonFile = "oracle.py";
		
		//檢查料號是否存在
		$query = "\"SELECT **** FROM **** WHERE **** = '" . $part_num . "'\"";
		$command = "python " . $pythonFile . " " . $query;
		$result = exec($command);
		
		if (empty($result)){
			echo 0;
			exit();
		}	
		
		$query = "\"SELECT DECODE(****, '****', '****', '****', '****', '****', '****', '****', '****', ****) ****, ****, ****, ****, NVL2(****, SUBSTR(****, 1, 3), 'NULL') **** FROM **** WHERE **** = '" . $part_num . "' ORDER BY ****, ****\"";
		$command = "python " . $pythonFile . " " . $query;
		$result = exec($command);
		
		//decode result
		$json_result = json_decode($result);
		
		//製作暫存檔
		$filename = '/tmp/webserver/' . session_id();
		$content = "";
		
		foreach ($json_result as $item){
			$content .= $item -> **** . "," . $item -> **** . "," . $item -> **** . "," . $item -> **** . "," . $item -> **** . "\n";
		}
		
		$file = fopen($filename,"w");
		
		//寫入暫存檔案
		fwrite($file, $content);
		fclose($file);  //關閉檔案
		
		$error = error_get_last();
		if ($error !== null){
			echo 0;
		} else {
			echo session_id();
		}
	}
?>