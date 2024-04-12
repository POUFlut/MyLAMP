<?php
	//使用POST觸發
	if ($_SERVER['REQUEST_METHOD'] === 'GET'){
		
		session_start();
		
		//儲存傳送來的資料
		$part_num = trim($_GET["****"] ?? '');
		$part_num = strtoupper($part_num);
		$part_num_split = substr($part_num, 0, 9);
		
		//set python file name
		$pythonFile = "oracle.py";
		
		//檢查料號是否存在
		$query = "\"SELECT **** FROM **** WHERE **** = '" . $part_num . "'\"";
		$command = "python " . $pythonFile . " " . $query;
		$result = exec($command);
		
		//不存在echo 0
		if (empty($result)){
			echo 0;
			exit();
		}
		
		//搜尋疊板資料(Core)
		$query = "\"SELECT ****, NVL2(****, ****, 0) ****, NVL2(****, ****, 0) **** FROM **** WHERE NOT (**** IS NULL AND **** IS NULL) AND NOT **** LIKE ((NVL2(****, ****, 0) - NVL2(****, ****, 0) + 1)) AND **** LIKE '" . $part_num_split . "%'\"";
		$command = "python " . $pythonFile . " " . $query;
		$result = exec($command);
		
		//decode result
		$json_result = json_decode($result);
		
		//製作暫存檔
		$filename = '/tmp/webserver/' . session_id();
		$content = "";
		
		foreach ($json_result as $item){
			$content .= $item -> **** . "," . $item -> **** . "," . $item -> **** . "\n";
		}
		
		//建立暫存檔案
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