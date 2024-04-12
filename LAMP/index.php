<?php
	require_once "config.php";  //引用設定文件
	
	//確認是否已登入
	if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)){
		header("location: login.php");
		exit();
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
    <title>首頁</title>
</head>
<body class = "containers">
	<?php require "nav.php"; ?>
	<?php require "footer.php" ?>
</body>
</html>
