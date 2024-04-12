<?php
	//引用設定文件
	require_once "config.php";  
	require_once "sessionControl.php";  
	require_once "sql.php";
	require_once "authenticator.php";
	require_once "memo/update_read_function.php";
	const _TABLE_NAME = "mausers";
	
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
			echo ("<script>window.close();</script>");  //關閉視窗
			exit();
		}
	} catch (Exception $e){
		echo ("<script>alert('" . $e -> getMessage() . "')</script>;");  //建立彈窗提示
	}
	
	($_SERVER['REQUEST_METHOD'] === 'POST') ? $username = trim($_POST["username"] ?? '') : $username = trim($_GET["username"] ?? '');
	$errors = [];
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$password = trim($_POST["password"] ?? '');
	}
	
	if (empty($username)){
		$errors["SQL"] = "NULL Username";
	}
	
	if (empty($errors)){
		if ($_SERVER['REQUEST_METHOD'] === 'POST'){
			//初始化變數
			$realname = trim($_POST["realname"] ?? '');
			if (empty($realname)){  //檢查中文姓名是否輸入
				$errors["realname"] = "請輸入姓名";
			} elseif (!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $realname)){  //檢查姓名是否是中文
				$errors["realname"] = "請使用中文姓名";
			}
			
			if (empty($password)){
				$errors["SQL"] = "NULL Password";
			}
			
			$user = new \myApp\User();
			$user_detail = $user -> getUsers($username);
			$row = $user_detail -> fetch_assoc();
			$permission = trim($_POST["permission"] ?? '');
			if (empty($permission)){
				$errors["permission"] = "請輸入權限等級";
			} elseif ($_SESSION["permission"] < $permission && $username !== $_SESSION["username"]){
				$errors["permission"] = "error permission";
				echo ("<script>alert('不可修改更高階管理員的資訊')</script>");  //建立彈窗提示
				echo ("<script>window.location.href = 'maintain_user.php?username=" . $username . "';</script>");
			} elseif ($_SESSION["permission"] == $row["permission"] && $username !== $_SESSION["username"]){
				$errors["permission"] = "error permission";
				echo ("<script>alert('不可修改同階管理員的資訊')</script>");  //建立彈窗提示
				echo ("<script>window.location.href = 'maintain_user.php?username=" . $username . "';</script>");
			} elseif ($permission == 5 && $row["permission"] < 5){
				$errors["permission"] = "error permission";
				echo ("<script>alert('不可修改成系統管理員，請通知系統管理員協助')</script>");  //建立彈窗提示
				echo ("<script>window.location.href = 'maintain_user.php?username=" . $username . "';</script>");
			}
			
			$form_data = [];
			$pattern = "*operation_*";
			foreach ($_POST as $key => $value){
				if (fnmatch($pattern, $key) && $value == 1){
					$form_data[substr($key,10)] = substr($key,10);
				}
			}
			
			if (empty($form_data)){
				echo ("<script>alert('請至少選一項作業單元')</script>");  //建立彈窗提示
				echo ("<script>window.location.href = 'maintain_user.php?username=" . $username . "';</script>");
				$errors["update"] = "請至少選一項作業單元";
			}
			
			//將帳號資料寫入資料庫
			if (empty($errors)){
				try{
					$user -> updateUser($realname, $username, $permission, $form_data, $password);
					\myApp\MEMO::update_read_flag($username, $form_data);
					echo ("<script>alert('修改成功')</script>");  //建立彈窗提示
					//echo ("<script>window.close();</script>");
				} catch (Exception $e){
					$errors["update"] = $e -> getMessage();
				}
			}
		}
		
		try{
			$user = new \myApp\User();
			$user_detail = $user -> getUsers($username);
			$user_operation = $user -> getUsersOperation($username);
			$row = $user_detail -> fetch_assoc();
			$permission_list = \MyApp\DB::getPermissionList();
			if (!empty($user_operation)){
				while ($temp = $user_operation -> fetch_assoc()){
					$row2[$temp["operation_name"]] = 1;
				}
			}
			$operation_list = \myApp\DB::getOperationList();
			
			while ($row3 = $operation_list -> fetch_assoc()){
				$operation_form .= "<div class='form-group'>" . $row3["operation_name_zh"] . "：";
				$operation_form .= "<div class='form-check form-check-inline'><label class='form-check-label'><input type='radio' class='form-check-input' name='operation_" . $row3["operation_name"] . "' value='1' ";
				if (!empty($row2[$row3["operation_name"]])){
					$operation_form .= "checked ";
				}
				$operation_form .= "/>是</label></div>";
				$operation_form .= "<div class='form-check form-check-inline'><label class='form-check-label'><input type='radio' class='form-check-input' name='operation_" . $row3["operation_name"] . "' value='0' ";
				if (empty($row2[$row3["operation_name"]])){
					$operation_form .= "checked ";
				}
				$operation_form .= "/>否</label></div>";
				$operation_form .= "</div>";
			}
		} catch (Exception $e){
			$errors["SQL"] = $e -> getMessage();
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
	<script type="text/javascript" src="jquery/jquery.min.js"></script>
	<script type="text/javascript" src="jquery/jquery-ui.min.js"></script>
    <title>編輯帳號</title>
</head>
<body>
    <div class = "container mt-5">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" accept-charset = "utf-8">
            <div class = "control-group">
                <label for = "username">帳號</label>
                <input class="form-control" type="text" name="username" id = "username" value="<?php echo $username; ?>" readonly >
                <span class="invalid-feedback"><?php echo $errors["username"]; ?></span>
            </div><br/>
			<div class = "control-group">
                <label for = "username">密碼</label>
				<input type="password" name="password" class="form-control <?php echo (!empty($errors["password"])) ? 'is-invalid' : ''; ?>"/> 
				<span class="invalid-feedback"><?php echo $errors["password"]; ?></span>
            </div><br/>
			<div class = "control-group">
                <label for = "realname">姓名</label>
                <input class="form-control" type="text" name="realname" id = "realname" value="<?php echo $row["realname"]; ?>">
                <span class="invalid-feedback"><?php echo $errors["realname"]; ?></span>
            </div><br/>
			
			<div class = 'input-group mb-3'>
				<div class = 'input-group-prepend'>
					<label class = 'input-group-text' for = 'permission'>權限等級</label>
				</div>
				<?php
					echo "<select class = 'custom-select' name = \"permission\" id = 'permission'>";
					while ($row_permission_list = $permission_list -> fetch_assoc()){
						$temp = "<option value = '" . $row_permission_list["permission"] . "' ";
						if ($row["permission"] == $row_permission_list["permission"]){
							$temp .= "selected ";
						}
						$temp .= ">" . $row_permission_list["permission_zh"] . "</option>";
						echo $temp;
					}
					
					echo "</select>";
				?>
            </div> 
			<p>作業單元</p>
			<p><?php echo $operation_form; ?></p>			
            <div>
                <input class = "btn btn-primary" type="submit" value="更新">
                <input class = "btn btn-secondary" type="reset" value="重置">
            </div>
        </form>
		<?php if (!empty($errors["update"])) echo "<div class='alert alert-danger'>" . $errors["update"] . "</div>" ?>
		<?php if (!empty($errors["form_data"])) echo "<div class='alert alert-danger'>" . $errors["form_data"] . "</div>" ?>
    </div>    
</body>
</html>
