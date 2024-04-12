<?php
	//引用設定文件
	require_once "../config.php";  
	require_once "../sessionControl.php";  
	require_once "../authenticator.php";
	require_once "update_read_function.php";
	
	//確認是否已登入
	if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)){
		header("location: ../login.php");
		exit();
	}
	
	//確認權限
	if ($_SESSION["permission"] < 3 && $_SESSION["secretary_fg"] !== 1){
		echo ("<script>alert('權限不足!')</script>;");  //建立彈窗提示
		echo ("<script>window.location.href = 'index.php';</script>");  //導向登入頁面
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
	
	//取得operation list
	$operation_list = \MyApp\DB::getOperationList();
	$operation_form = "<div class='form-group'>作業單元：<div class='form-check form-check-inline'><label class='form-check-label'><input class='form-check-input' type='radio' name='operation_name' value = '%' checked >All</label></div>";
	while ($row = $operation_list -> fetch_assoc()){
		$operation_form .= "<div class='form-check form-check-inline'><label class='form-check-label'><input class='form-check-input' type='radio' name='operation_name' value = '" . $row["operation_name"] . "'>" . $row["operation_name_zh"] . "</label></div>";
	}
	$operation_form .= "</div>";
	
	$user_list = $user -> getUsers();
	
	$user_list_form = "<div class = 'input-group mb-3'><div class = 'input-group-prepend'><label class = 'input-group-text' for = 'username'>姓名:</label></div><select class = 'custom-select' name = \"username\" id = 'username'>";
	$user_list_form .= "<option value = '%' >All</option>";
	while($row_user_list = $user_list -> fetch_assoc()){
		$user_list_form .= "<option value = '" . $row_user_list["username"] . "' ";
		$user_list_form .= ">" . $row_user_list["realname"] . "</option>";
	}
	$user_list_form .= "</select></div>";
	
	$memo_title_list = \MyApp\MEMO::selectUploadHistoryAll();
	$memo_title_form = "<div class = 'input-group mb-3'><div class = 'input-group-prepend'><label class = 'input-group-text' for = 'memo_title'>MEMO名稱:</label></div><select class = 'custom-select' name = \"memo_title\" id = 'memo_title'>";
	$memo_title_form .= "<option value = '%' >All</option>";
	while($row_memo_title = $memo_title_list -> fetch_assoc()){
		$memo_title_form .= "<option value = '" . $row_memo_title["memo_title"] . "' ";
		$memo_title_form .= ">" . $row_memo_title["memo_title"] . "</option>";
	}
	$memo_title_form .= "</select></div>";
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
    <title>閱讀紀錄查詢</title>
</head>
<body class = "containers">
	<?php require "../nav.php"; ?>
	<div class="container mt-5">
			
			<form id = "filter_form">
				<?php 
					echo $operation_form;
					echo $user_list_form;
					echo $memo_title_form;
				?>
				<div class='form-group'>
					<input type = "button" class = "btn btn-primary" onclick = "filter()" value = "搜尋">
				</div>
			</form>
		</div>
	
		<div id = "memo_table">
		
		</div>
		<?php require "../footer.php" ?>
</body>
<script>
	function filter(){
		try{
			var form = document.getElementById("filter_form");
			var formData = new FormData(form);
			var filter_operation = formData.get("operation_name");
			var filter_username = formData.get("username");
			var filter_memo_title = formData.get("memo_title");
		} catch (err) {
			console.log(err);
		}
		
		var xhr = new XMLHttpRequest();  //建立XMLHttpRequest物件
			
		xhr.open('POST', 'filter_memo.php');  //設置Request資料(Method, URL, *async)
		xhr.setRequestHeader('Content-Type', 'application/json');
		
		//提示訊息
		document.getElementById("memo_table").innerHTML = '讀取中...'
		
		//設置Time Out
		xhr.timeout = 60000; //單位:ms
		xhr.ontimeout = function(){
			document.getElementById("memo_table").innerHTML = '請求超時!';
		};
		
		//設置Response監聽
		xhr.onreadystatechange = function(){
			if (xhr.readyState === 4 && xhr.status === 200){
				document.getElementById("memo_table").innerHTML = xhr.responseText;
			};
		};
		
		//建立JSON格式物件
		var data = {
			pfilter_operation: filter_operation,
			pfilter_username: filter_username,
			pfilter_memo_title: filter_memo_title
		};
		
		//Request
		xhr.send(JSON.stringify(data));
		
	}
</script>
</html>
