<?php
	//引用設定文件
	require_once "../config.php";  
	require_once "../sessionControl.php";  
	require_once "../authenticator.php";
	require_once "update_read_function.php";
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST'){
		$jsonData = json_decode(file_get_contents('php://input'));
		
		//檢查認證是否到期
		if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
			$user = new \myApp\User();
			try{
				if ($user -> loginTimeOut()){
					$errors["post"] = "Time out";
				}
			} catch (Exception $e){
				$errors["post"] = "Time out";
			}
		}
		
		//檢查是否POST Word
		if (!$jsonData -> pfilter_operation){
			$errors["post"] = "NULL Word";
		}
		
		//檢查是否POST username
		if (!$jsonData -> pfilter_username){
			$errors["post"] = "NULL username";
		}
		
		//檢查是否POST memo title
		if (!$jsonData -> pfilter_memo_title){
			$errors["post"] = "NULL memo title";
		}
		
		$table = "";
		//取得資料
		if (empty($errors)){
			try{
				$result = \myApp\MEMO::getUserSummaryTable($jsonData -> pfilter_username, $jsonData -> pfilter_operation, $jsonData -> pfilter_memo_title);
			} catch (Exception $e){
				echo ("<script>alert('" . $e -> getMessage() . "')</script>;");  //建立彈窗提示
			}
				
			$table = "<table class = 'table table-bordered text-center align-middle mt-3'>" . 
					 "<thead><tr><th scope='col'>交接項目" .
					 "</th><th scope='col'>作業單元" .
					 "</th><th scope='col'>上傳時間" .
					 "</th><th scope='col'>人員姓名" .
					 "</th><th scope='col'>必須閱讀" .
					 "</th><th scope='col'>是否閱讀" .
					 "</th><th scope='col'>閱讀時間</th></tr></thead><tbody>";
			if (!empty($result)){
				while($row = $result -> fetch_assoc()){
					$table .= "<tr><td>" . $row["memo_title"] . 
							  "</td><td>" . $row["operation_name_zh"] . 
							  "</td><td>" . $row["update_date"] .
							  "</td><td>" . $row["realname"] . 
							  "</td><td>" . (empty($row["need_flag"]) ? "N" : "Y" ) .
							  "</td><td>" . (empty($row["is_read"]) ? "N" : "Y" ) .
							  "</td><td>" . $row["read_date"] . "</td>";
				}
			}
			$table .= "</tbody></table>";
		}
		
		//Response
		if (empty($errors)){
			echo $table;
		} else {
			echo $errors["post"];
		}
		exit();
	}
?>