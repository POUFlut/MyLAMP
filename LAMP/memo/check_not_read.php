<?php

	//引用設定文件
	require_once "../config.php";
	require_once "../sql.php";
	require_once "../authenticator.php";
	require_once "update_read_function.php";
	//const _TABLE_NAME = "mausers";
	//const _TABLE_NAME2 = "memo_upload_histroy";
	//const _TABLE_NAME3 = "memo_read_histroy";
	
	//儲存傳送來的資料
	($_SERVER['REQUEST_METHOD'] === 'POST') ? $username = trim($_POST["username"] ?? '') : $username = trim($_GET["username"] ?? '');
	$errors = [];
	
	if (!empty($username)){
		//檢查帳號是否存在
		try{
			$user = new \myApp\User();
			if(!$user -> check_account($username)){
				$errors["SQL"] = "Not Exist Account";
			}
		} catch (Exception $e){
			$errors["SQL"] = "SQL error: " . $e -> getMessage();
		}
		
		if (empty($errors)){
			//$sqlStr = "SELECT a.memo_title, a.update_date, a.operation, b.memo_id, b.is_read, b.username, b.read_date, c.realname " . 
			//		  "FROM " . _TABLE_NAME2 . " a, " . _TABLE_NAME3 . " b, " .  _TABLE_NAME . " c " .
			//		  "WHERE a.id = b.memo_id AND a.enable = 1 AND b.username = c.username AND b.username = ? AND b.is_read IS NULL AND b.need_flag = '1' " . 
			//		  "ORDER BY c.realname ASC, b.need_flag ASC, b.is_read DESC";
			try{
				//$result = \myApp\DB::executeSQL($sqlStr, ["s", $username]);
				$result = \myApp\MEMO::getUserNotReadSummaryTable($username);
				$row = $result -> fetch_assoc();
				if (!empty($row)) $realname = $row["realname"];
			} catch (Exception $e){
				$errors["SQL"] = "SQl error: " . $e -> getMessage();
			}
		}
	} else {
		$errors["SQL"] = "NULL Username";
	}
	
	if (empty($errors)){
		if ($_SERVER['REQUEST_METHOD'] === 'POST'){
			if (!empty($row["memo_id"])){
				echo "Y";
			} else {
				echo "N";
			}
			exit();
		}
		
		if ($_SERVER['REQUEST_METHOD'] === 'GET'){
			$table = "<table class = 'table table-bordered text-center align-middle mt-3'>" .
					 "<thead><tr><th scope='col'>交接項目" .
					 "</th><th scope='col'>作業單元" .
					 "</th><th scope='col'>上傳時間" .
					 "</th><th scope='col'>是否閱讀" .
					 "</th><th scope='col'>閱讀時間</th></tr></thead><tbody>";
			do {
				if (!empty($row)){
					$table .= "<tr><td><a href = 'javascript:void(0)' onclick = \"updateRead('" . 
							  $row["memo_id"] . "','" . $row["username"] . "','" . $row["memo_title"] .
							  "','" . $row["operation"] . "')\">" . $row["memo_title"] . "</a>" .
							  "</td><td>" . $row["operation_name_zh"] .
							  "</td><td>" . $row["update_date"] .
							  "</td><td id = '" . $row["memo_id"] . "'>" . (empty($row["is_read"]) ? "N" : "Y") .
							  "</td><td id = '" . $row["memo_id"] . "_times'>" . $row["read_date"] . "</td>";
				}
			} while($row = $result -> fetch_assoc());
			
			$table .= "</tbody></table>";
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
	<meta http-equiv = "X-UA-Compatible" content = "IE=edge">
	<link rel="stylesheet" href="../bootstrap/4.0.0/css/bootstrap.min.css">
	<link rel="stylesheet" href="../jquery/ui/1.13.2/themes/base/jquery-ui.css">
	<script type="text/javascript" src="../jquery/jquery.min.js"></script>
	<script type="text/javascript" src="../jquery/jquery-ui.min.js"></script>
    <title>首頁</title>
</head>
<body class = "containers">
    <h2>您好!<?php echo $realname; ?>，以下是未閱讀的清單!</h2>
	<?php echo $table;echo $errors["SQL"];
	?>
</body>

<script type = "text/javascript" >
	function updateRead(id, username, title, operation){
		
		var xhr = new XMLHttpRequest();  //建立XMLHttpRequest物件
			
		xhr.open('POST', 'update_read_state.php');  //設置Request資料(Method, URL, *async)
		xhr.setRequestHeader('Content-Type', 'application/json');
		
		//提示訊息
		document.getElementById(id).innerHTML = '開啟中...'
		
		//設置Time Out
		xhr.timeout = 60000; //單位:ms
		xhr.ontimeout = function(){
			document.getElementById(id).innerHTML = '請求超時!';
		};
		
		//設置Response監聽
		xhr.onreadystatechange = function(){
			if (xhr.readyState === 4 && xhr.status === 200){
				var jsonObj = JSON.parse(xhr.responseText);
				if (jsonObj.read == "Y") {
					document.getElementById(id).innerHTML = "Y";
					document.getElementById(id + '_times').innerHTML = jsonObj.times;
					window.open("uploads/" + operation + "/" + title, "_blank");
				}
			};
		};
		
		//建立JSON格式物件
		var data = {
			pid: id ,
			pusername: username
		};
		
		//Request
		xhr.send(JSON.stringify(data));
		
	}
</script>
</html>