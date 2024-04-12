<?php
	//引用設定文件
	require_once "config.php";  
	require_once "sessionControl.php";  
	require_once "sql.php";
	require_once "authenticator.php";
	const _TABLE_NAME = "mausers";
	$errors = [];
	
	//確認是否已登入
	if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)){
		header("location: login.php");
		exit();
	}
	
	//確認權限
	if ($_SESSION["permission"] < 3){
		echo ("<script>alert('權限不足!')</script>;");  //建立彈窗提示
		echo ("<script>window.location.href = 'index.php';</script>");  //導向登入頁面
		exit();
	}
	
	$user = new \myApp\User();
	//檢查認證是否到期
	try{
		if ($user -> loginTimeOut()){
			echo ("<script>alert('登入逾時')</script>;");  //建立彈窗提示
			echo ("<script>window.location.href = 'login.php';</script>");  //導向登入頁面
			exit();
		}
	} catch (Exception $e){
		echo ("<script>alert('" . $e -> getMessage() . "')</script>;");  //建立彈窗提示
	}

	if (empty($errors)){
		$table = "<table class = 'table table-bordered text-center align-middle'>" . 
				 "<thead><tr><th scope='col'>姓名" .
				 "</th><th scope='col'>權限等級" .
				 "</th><th scope='col'>帳號</th>";
		
		try{
			$operation_list = \MyApp\DB::getOperationList();
		} catch (Exception $e){
			$errors["SQL"] = $e -> getMessage();
		}
		
		$operation_array = [];
		while ($row = $operation_list -> fetch_assoc()){
			$operation_array[$row["operation_name"]] = $row["operation_name"];
			$table .= "<th scope='col'>" . $row["operation_name_zh"] . "</th>";
		};
		
		$table .= "</tr></thead>";
		
		try{
			$result = $user -> getUsers();
			while($row = $result -> fetch_assoc()){
				$table .= "<tr><td>" . $row["realname"] . 
						  "</td><td>" . $row["permission_zh"] . 
						  "</td><td>" . $row["username"] . "</td>";
				foreach($operation_array as $key => $value){
					$userOperation = $user -> checkUsersOperation($row["username"], $value);
					if($userOperation){
						$table .= "<td>Y</td>";
					} else {
						$table .= "<td>N</td>";
					}
				}
				$table .= "<td><button class = 'btn btn-primary' onclick = \"maintain_user('" . $row["username"] . "', '" . $row["permission"] . "')\">編輯</button></td>";
				
				if ($_SESSION["permission"] >= 5){
					$table .= "<td><button class = 'btn btn-danger' id = '" . $row["username"] . "' onclick = \"delete_user('" . $row["username"] . "', '" . $row["permission"] . "')\">刪除</button></td>";
				}
				
				$table .= "</tr>";
			}
			
			$table .= "</table>";
		} catch (Exception $e){
			$errors["SQL"] = $e -> getMessage();
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
	<meta http-equiv = "X-UA-Compatible" content = "IE=edge">
	<link rel = "icon" href = "icon.png" type = "image/png">
	<link rel="stylesheet" href="bootstrap/4.0.0/css/bootstrap.min.css">
	<link rel="stylesheet" href="jquery/ui/1.13.2/themes/base/jquery-ui.css">
	<script type="text/javascript" src="jquery/jquery.min.js"></script>
	<script type="text/javascript" src="jquery/jquery-ui.min.js"></script>
	<script type="text/javascript" src="bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <title>使用者管理</title>
</head>
<body class = "containers">
    <?php require "nav.php"; ?>
	<div class="container mt-5">
	<?php echo $table; ?>
	</div>
	<?php 
		if($_SESSION["permission"] >= 5){
			require "delete_modal.php";
		}	
	?>
	<?php require "footer.php" ?>
</body>
<script type = "text/javascript" >	

	//開啟編輯視窗
	function maintain_user(username,permission){
		
		//開新視窗
		var newWin = window.open("maintain_user.php?username=" + username, "_blank", "location = no, width = 400, height = 400, scrollbars = yes");
		
		//檢查新視窗是否關閉
		var checkInterval = setInterval(
			function(){
				if(newWin.closed){
					clearInterval(checkInterval);
					window.location.reload();
				}
			}
		, 100);
	}
</script>
</html>
