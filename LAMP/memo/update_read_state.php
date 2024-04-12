<?php
	//引用設定文件
	require_once "../config.php";  
	require_once "../sql.php";
	require_once "../authenticator.php";
	const _TABLE_NAME3 = "memo_read_histroy";
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST'){
		$jsonData = json_decode(file_get_contents('php://input'));
		
		//檢查是否POST ID
		if (!$jsonData -> pid){
			$errors["update"] = "NULL ID";
		}
		
		//檢查是否POST Username
		if (!$jsonData -> pusername){
			$errors["update"] = "NULL Username";
		}
		
		//更新SQL
		if (empty($errors)) {
			$datetime = date("Y-m-d H:i:s",time());
			$sqlStr = "UPDATE " . _TABLE_NAME3 . " " .
					  "SET is_read = '1', read_date = ? " .
					  "WHERE memo_id = ? AND username = ?";
			try{
				\myApp\DB::executeSQL($sqlStr, ["sss", $datetime, $jsonData -> pid, $jsonData -> pusername]);
			} catch (Exception $e){
				$errors["update"] = $e -> getMessage();
			}
		}
		
		//Response
		if (empty($errors)){
			echo '{"read":"Y","times":"' . $datetime . '"}';
		} else {
			echo $errors["update"];
		}
		exit();
	}
?>