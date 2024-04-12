<?php
	
	//引用設定文件
	require_once "config.php";
	require_once "authenticator.php";
	
	//確認是否已登入
	if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
		header("location: index.php");
		exit();
	}
	
	//初始化變數
	$username = trim($_POST["username"] ?? '');
	$password = trim($_POST["password"] ?? '');
	$errors = [];
	
	if ($_SERVER["REQUEST_METHOD"] == "POST"){
		
		//檢查帳號是否輸入
		if (empty($username)){
			$errors["username"] = "請輸入帳號";
		} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)){  //檢查帳號是否含有特殊字元
			$errors["username"] = "帳號不可使用特殊字元!";
		}
		
		if (empty($password)){  //檢查密碼是否輸入
			$errors["password"] = "請輸入密碼";
		}
		
		if (empty($errors)){
			$check_login = new \myApp\User();
			try{
				$check_login -> authenticate($username, $password);
				header("location: index.php");
				exit();
			} catch (Exception $e){
				$errors["login"] = $e -> getMessage();
			}
		}
	}

?>

<html>
	<head>
		<meta charset = "utf-8" />
		<meta http-equiv = "X-UA-Compatible" content = "IE=edge">
		<title>N50 資訊系統</title>
		<link rel = "icon" href = "icon.png" type = "image/png">
		<link rel="stylesheet" href="bootstrap/4.0.0/css/bootstrap.min.css">
		<link rel="stylesheet" href="jquery/ui/1.13.2/themes/base/jquery-ui.css">
		<link rel="stylesheet" type = "text/css" href="home.css">
		<script type="text/javascript" src="jquery/jquery.min.js"></script>
		<script type="text/javascript" src="jquery/jquery-ui.min.js"></script>
	</head>
	<body>
		<div class="container">
			<div class="wrapper mx-auto rounded p-4 ">
				<h2 class = "text-center">登入系統</h2><br/>
				<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" accept-charset = "utf-8">
					<div class="form-group">
						<label>帳號(工號)</label>
						<input type="text" name="username" class="form-control <?php echo (!empty($errors["username"])) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>"> 
						<span class="invalid-feedback"><?php echo $errors["username"]; ?></span>
					</div>    
					<div class="form-group">
						<label>密碼</label>
						<input type="password" name="password" class="form-control <?php echo (!empty($errors["password"])) ? 'is-invalid' : ''; ?>"/> 
						<span class="invalid-feedback"><?php echo $errors["password"]; ?></span>
					</div>
					<div class="form-group">
						<input class="btn btn-primary" type="submit" value="登入">
					</div>
				</form>
				<?php if (!empty($errors["login"])) echo "<div class='alert alert-danger'>" . $errors["login"] . "</div>" ?>
			</div>
		</div>
	</body>
	<script>
		var isIE = /MSIE|Trident/.test(navigator.userAgent);
		
		if (isIE) {
			alert("若要進行維護(ex.新增使用者、編輯路徑...)操作，請使用Google Chorme，以免發生錯誤!");
		}
	</script>
</html>
 