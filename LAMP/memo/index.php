<?php
	//引用設定文件
	require_once "../config.php";  
	require_once "../sessionControl.php";  
	require_once "../sql.php";
	require_once "../authenticator.php";
	require_once "update_read_function.php";
	
	
	//確認是否已登入
	if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)){
		header("location: ../login.php");
		exit();
	}
	
	$user = new \myApp\User();
	//檢查認證是否到期
	try{
		if ($user -> loginTimeOut()){
			echo ("<script>alert('登入逾時')</script>;");  //建立彈窗提示
			echo ("<script>window.location.href = '../login.php';</script>");  //導向登入頁面
			exit();
		}
	} catch (Exception $e){
		echo ("<script>alert('" . $e -> getMessage() . "')</script>;");  //建立彈窗提示
	}
	
	if ($_SERVER['REQUEST_METHOD'] === 'GET'){
		//搜尋上傳紀錄
		try{
			$result = \myApp\MEMO::getUserSummaryTable($_SESSION["username"], "%", "%");
		} catch (Exception $e){
			echo ("<script>alert('" . $e -> getMessage() . "')</script>;");  //建立彈窗提示
		}
		
		$table = "<table class = 'table table-bordered text-center align-middle'>" .
				 "<thead><tr><th scope='col'>交接項目" .
				 "</th><th scope='col'>作業單元" .
				 "</th><th scope='col'>上傳時間" .
				 "</th><th scope='col'>是否閱讀" .
				 "</th><th scope='col'>閱讀時間</th></tr></thead><tbody>";
		if (!empty($result)){
			while($row = $result -> fetch_assoc()){
				$table .= "<tr><td><a href = 'javascript:void(0)' onclick = \"updateRead('" . 
						  $row["memo_id"] . "','" . $_SESSION["username"] . "','" . $row["memo_title"] .
						  "','" . $row["operation"] . "')\">" . $row["memo_title"] . "</a>" .
						  "</td><td>" . $row["operation_name_zh"] .
						  "</td><td>" . $row["update_date"] .
						  "</td><td id = '" . $row["memo_id"] . "'>" . (empty($row["is_read"]) ? "N" : "Y") .
						  "</td><td id = '" . $row["memo_id"] . "_times'>" . $row["read_date"] . "</td>";
			}
		}
		$table .= "</tbody></table>";
	}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
	<meta http-equiv = "X-UA-Compatible" content = "IE=edge">
	<link rel = "icon" href = "../icon.png" type = "image/png">
	<link rel="stylesheet" href="../bootstrap/4.0.0/css/bootstrap.min.css">
	<link rel="stylesheet" href="../jquery/ui/1.13.2/themes/base/jquery-ui.css">
	<script type="text/javascript" src="../jquery/jquery.min.js"></script>
	<script type="text/javascript" src="../jquery/jquery-ui.min.js"></script>
    <title>MEMO交接</title>
</head>
<body class = "containers">
    <?php require "../nav.php"; ?>
	<div class = "container mt-3 mb-3">
		<?php
			if ($_SESSION["permission"] >= 3 || $_SESSION["secretary_fg"] == 1){
				echo "<a class = \"btn btn-info\" href='memo_all.php'>查詢閱讀紀錄</a>";
			}
		?>
		<a class = "btn btn-primary" href="upload.php">上傳MEMO交接檔案</a>
	</div>
	<?php echo $table; ?>
	<?php require "../footer.php" ?>
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
