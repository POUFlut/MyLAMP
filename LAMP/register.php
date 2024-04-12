<?php
	//引用設定文件
	require_once "config.php";  
	require_once "sessionControl.php";  
	require_once "sql.php";
	require_once "authenticator.php";
	require_once "memo/update_read_function.php";
	const _TABLE_NAME = "mausers";;
	
	//初始化變數
	$realname = trim($_POST["realname"] ?? '');
	$username = trim($_POST["username"] ?? '');
	$password = trim($_POST["password"] ?? '');
	$confirm_password = trim($_POST["confirm_password"] ?? '');
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
			echo ("<script>window.location.href = 'index.php';</script>");  //關閉視窗
			exit();
		}
	} catch (Exception $e){
		echo ("<script>alert('" . $e -> getMessage() . "')</script>;");  //建立彈窗提示
	}
	
	//if($_SERVER["REQUEST_METHOD"] === "GET"){
		try{
			
			$operation_list = \myApp\DB::getOperationList();
			$permission_list = \MyApp\DB::getPermissionList();
			
			while ($row3 = $operation_list -> fetch_assoc()){
				$operation_form .= "<div class='form-group'>" . $row3["operation_name_zh"] . "：";
				$operation_form .= "<div class='form-check form-check-inline'><label class='form-check-label'><input type='radio' class='form-check-input' name='" . $row3["operation_name"] . "' value='1' ";
				if ($row3["operation_name"] == "others"){
					$operation_form .= "checked ";
				}
				$operation_form .= "/>是</label></div>";
				$operation_form .= "<div class='form-check form-check-inline'><label class='form-check-label'><input type='radio' class='form-check-input' name='" . $row3["operation_name"] . "' value='0' ";
				if ($row3["operation_name"] !== "others"){
					$operation_form .= "checked ";
				}
				$operation_form .= "/>否</label></div>";
				$operation_form .= "</div>";
			}
			
			$permission_form = "<select class = 'custom-select' name = \"permission\">";
			while ($row_permission_list = $permission_list -> fetch_assoc()){
				if($row_permission_list["permission"] < 5){
					$temp = "<option value = '" . $row_permission_list["permission"] . "' ";
					if ($row_permission_list["permission"] == 1){
						$temp .= "selected ";
					}
					$temp .= ">" . $row_permission_list["permission_zh"] . "</option>";
					$permission_form .= $temp;
				}
			}
			
			$permission_form .= "</select>";
		} catch (Exception $e){
			$errors["SQL"] = $e -> getMessage();
		}
	//}
	
	if($_SERVER["REQUEST_METHOD"] === "POST"){
		
		if (empty($realname)){  //檢查中文姓名是否輸入
			$errors["realname"] = "請輸入姓名";
		} elseif (!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $realname)){  //檢查姓名是否是中文
			$errors["realname"] = "請使用中文姓名";
		}
		
		if (empty($username)){  //檢查帳號是否輸入
			$errors["username"] = "請輸入帳號";
		} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)){  //檢查帳號是否含有特殊字元
			$errors["username"] = "帳號不可使用特殊字元!";
		}
		
		if (empty($password)){  //檢查密碼是否輸入
			$errors["password"] = "請輸入密碼";
		}
		
		if (empty($confirm_password)){  //檢查確認密碼是否輸入
			$errors["confirm_password"] = "請輸入確認密碼";
		} elseif ($password != $confirm_password){
			$errors["confirm_password"] = "密碼和確認密碼不相同";
		}
		
		//確認作業單元資料
		try{
			$operation_list = \myApp\DB::getOperationList();
			while($row3 = $operation_list -> fetch_assoc()){
				$row_operation_list[$row3["operation_name"]] = $row3["operation_name"];
			}
			$form_data = [];
			foreach ($_POST as $key => $value) {
				if (isset($row_operation_list[$key]) && $value == 1){
					$form_data[$key] = $value;
				}
			}
		} catch (Exception $e){
			$errors["operation"] = $e -> getMessage();
		}
		
		//確認權限等級
		if($_POST["permission"] > 0 && $_POST["permission"] < 4){
			$permission = $_POST["permission"];
		} else {
			$errors["permission"] = "illegal permission";
		}
		
		if (empty($errors)){
			//檢查帳號是否存在
			$user = new \myApp\User();
			try{
				if ($user -> check_account($username)){
					$errors["login"] = "Authenticator Fail!Exist Account";
				}
			} catch (Exception $e){
				$errors["login"] = $e -> getMessage();
			}
			
			//將帳號資料寫入資料庫
			if (empty($errors)){
				try{
					$user -> registAccount($realname, $username, $password, $form_data, $permission);
					echo ("<script>alert('註冊成功')</script>;");  //建立彈窗提示
					echo ("<script>window.location.href = 'login.php';</script>");  //導向登入頁面
					exit();
					
				} catch (Exception $e){
					$errors["login"] = $e -> getMessage();
				}
			}
		}
	}
?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"/>
	<meta http-equiv = "X-UA-Compatible" content = "IE=edge">
	<link rel = "icon" href = "icon.png" type = "image/png">
	<link rel="stylesheet" href="bootstrap/4.0.0/css/bootstrap.min.css">
	<link rel="stylesheet" href="jquery/ui/1.13.2/themes/base/jquery-ui.css">
		<link rel="stylesheet" type = "text/css" href="home.css">
	<script type="text/javascript" src="jquery/jquery.min.js"></script>
	<script type="text/javascript" src="jquery/jquery-ui.min.js"></script>
    <meta charset="utf-8">
    <title>註冊帳號</title>
</head>
<body>
	<div class="wrapper container mt-5">
		<h2 class = "text-center">N50 帳號註冊系統</h2><br/>
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" accept-charset = "utf-8">
			<div class = "control-group">
				<label>姓名</label>
				<input  class="form-control <?php echo (!empty($errors["realname"])) ? 'is-invalid' : ''; ?>" type="text" name="realname" value="<?php echo $realname; ?>">
				<span class="invalid-feedback"><?php echo $errors["realname"]; ?></span>
			</div></br>
			<div class = "control-group">
				<label>帳號</label>
				<input class="form-control <?php echo (!empty($errors["username"])) ? 'is-invalid' : ''; ?>" type="text" name="username" value="<?php echo $username; ?>">
				<span class="invalid-feedback"><?php echo $errors["username"]; ?></span>
			</div></br>
			<div class = "control-group">
				<label>密碼</label>
				<input class="form-control <?php echo (!empty($errors["password"])) ? 'is-invalid' : ''; ?>" type="password" name="password" value="<?php echo $password; ?>">
				<span class="invalid-feedback"><?php echo $errors["password"]; ?></span>
			</div></br>
			<div class = "control-group">
				<label>確認密碼</label>
				<input class="form-control <?php echo (!empty($errors["confirm_password"])) ? 'is-invalid' : ''; ?>" type="password" name="confirm_password" value="<?php echo $confirm_password; ?>">
				<span class="invalid-feedback"><?php echo $errors["confirm_password"]; ?></span>
			</div></br></br>
			<div class = 'input-group'>
				<div class = 'input-group-prepend'>
					<label class = 'input-group-text' for = 'permission'>權限等級</label>
				</div>
				<?php echo $permission_form; ?>
			</div></br>
			<div>
				<h3>所屬作業單元</h3>
				<input class = "btn btn-info btn-sm mb-3" type = "button" value = "全選" onclick = "selectAll()">
				<?php echo $operation_form; ?>
			</div>
			<div>
				<input class = "btn btn-primary" type="submit" value="註冊">
				<input class = "btn btn-secondary" type="reset" value="恢復預設">
				<input class = "btn btn-link btn-sm" type = "button" value = "回到首頁" onclick = "home()">
			</div></br>
			<span><?php echo $errors["form_data"]; ?></span>
			<span><?php echo $errors["login"]; ?></span>
		</form>
	</div>
</body>
<script type = "text/javascript">
	function selectAll(){
		var selection = document.querySelectorAll("input[type='radio']");
		for ( var i = 0;i < selection.length;i++){
			if(selection[i].value == 1){
				selection[i].checked = true;
			}
		}
	}
	
	function home(){
		window.location.href = 'login.php'
	}
</script>
</html>
