<?php
	//引用設定文件
	require_once "../config.php";  
	require_once "../sessionControl.php";  
	require_once "../sql.php";
	require_once "../authenticator.php";
	
	$user = new \myApp\User();
	$errors = [];
	
	//確認是否已登入
	if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)){
		header("location: ../login.php");
		exit();
	}
	
	if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
		//檢查認證是否到期
		try{
			if ($user -> loginTimeOut()){
				echo ("<script>alert('登入逾時')</script>;");  //建立彈窗提示
				echo ("<script>window.location.href = '../login.php';</script>");  //導向登入頁面
			}
		} catch (Exception $e){
			$errors["SQL"] = $e -> getMessage();
		}
	}
	
	//確認權限
	if ($_SESSION["permission"] < 3){
		echo ("<script>alert('權限不足')</script>;");  //建立彈窗提示
		echo ("<script>window.close();</script>");
		exit();
	}
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST'){
		$folder_path = urldecode(trim($_POST["folder_path"] ?? ''));
		$path_account = trim($_POST["path_account"] ?? '');
		$path_password = trim($_POST["path_password"] ?? '');
		$path_type_post = trim($_POST["path_type"] ?? '');
		$folder_name = urldecode(trim($_POST["folder_name"] ?? ''));
		$path_account_permission = trim($_POST["path_account_permission"] ?? '');
		$path_access_permission_post = trim($_POST["path_access_permission"] ?? '');
		
		if ($folder_path == ""){
			$errors["folder_path"] = "請輸入路徑";
		}
		
		if (empty($errors)){
			try{
				\myApp\DB::insertFolderDetail($folder_name, $folder_path, $path_account, $path_password, $path_account_permission, $path_access_permission_post, $path_type_post);
				echo ("<script>alert('新增成功!')</script>;");
				echo ("<script>window.close();</script>;");
				exit();
			} catch (Exception $e){
				$errors["insert"] = $e -> getMessage();
			}
		}
	}
	
	$comment = "<select class = 'custom-select' name = \"folder_name\" id = 'folder_name'>";
	$path_type = "<select class = 'custom-select' name = \"path_type\" id = 'path_type'>";
	$path_access_permission = "<select class = 'custom-select' name = \"path_access_permission\" id = 'path_access_permission'>";
	try{
		$folder_path_list = \myApp\DB::getFolderPath();
		while($row_folder_path_list = $folder_path_list -> fetch_assoc()){
			$comment .= "<option value = '" . urlencode($row_folder_path_list["folder_name"]) . "'";
			if($row_folder_path_list["folder_name"] == $folder_name){
				$comment .= " selected";
			}
			$comment .= ">" . $row_folder_path_list["comment"] . "</option>";
		}
		$comment .= "</select>";
		
		$path_type_list = \myApp\DB::getFolderPathType();
		while($row_path_type_list = $path_type_list -> fetch_assoc()){
			$path_type .= "<option value = '" . $row_path_type_list["path_type"] . "' ";
			if ($row_path_type_list["path_type"] == $path_type_post){
				$path_type .= "selected";
			}
			
			$path_type .= ">" . $row_path_type_list["path_type"] . "</option>";
		}
		$path_type .= "</select>";
		
		$path_access_permission_list = \myApp\DB::getPermissionList();
		while($row_path_access_permission_list = $path_access_permission_list -> fetch_assoc()){
			$path_access_permission .= "<option value = '" . $row_path_access_permission_list["permission"] . "' ";
			if ($row_path_access_permission_list["permission"] == $path_access_permission_post){
				$path_access_permission .= "selected";
			}
			
			$path_access_permission .= ">" . $row_path_access_permission_list["permission_zh"] . "以上</option>";
		}
		$path_access_permission .= "</select>";
	} catch (Exception $e){
		$errors["unknow"] = $e -> getMessage();
	}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
	<meta http-equiv = "X-UA-Compatible" content = "IE=edge">
	<link rel = "icon" href = "../icon.png" type = "image/png">
	<link rel="stylesheet" href="../bootstrap/4.0.0/css/bootstrap.min.css">
	<link rel="stylesheet" href="../jquery/ui/1.13.2/themes/base/jquery-ui.css">
	<script type="text/javascript" src="../jquery/jquery.min.js"></script>
	<script type="text/javascript" src="../jquery/jquery-ui.min.js"></script>
    <title>新增路徑</title>
</head>
<body>
    <div class = "container mt-5">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" accept-charset = "utf-8">
			<div class = 'input-group mb-3'>
				<div class = 'input-group-prepend'>
					<label class = 'input-group-text' for = 'folder_name'>資料夾</label>
				</div>
				<?php
					echo $comment;
				?>
            </div>
			
			<div class = "control-group">
                <label for = "folder_path">路徑</label>
                <input class="form-control <?php echo (!empty($errors["folder_path"])) ? 'is-invalid' : ''; ?>" type="text" name="folder_path" id = "folder_path" value="<?php echo $folder_path; ?>">
				<span class="invalid-feedback"><?php echo $errors["folder_path"]; ?></span>
            </div><br/>
			
			<div class = "control-group">
                <label for = "path_account">帳號</label>
                <input class="form-control" type="text" name="path_account" id = "path_account" value="<?php echo $path_account; ?>" placeholder = "選填">
            </div><br/>
			
			<div class = "control-group">
                <label for = "path_password">密碼</label>
                <input class="form-control" type="text" name="path_password" id = "path_password" value="<?php echo $path_password; ?>" placeholder = "選填">
            </div><br/>
			
			<div class = 'input-group mb-3'>
				<div class = 'input-group-prepend'>
					<label class = 'input-group-text' for = 'path_type'>連線類型</label>
				</div>
				<?php
					echo $path_type;
				?>
            </div>
			
			<div class = 'input-group mb-3'>
				<div class = 'input-group-prepend'>
					<label class = 'input-group-text' for = 'path_account_permission'>權限</label>
				</div>
				<select class = 'custom-select' name = 'path_account_permission' id = 'path_type'>
					<option value = '唯讀' <?php if ($path_account_permission == "唯讀") echo " selected" ?>>唯讀</option>
					<option value = '讀寫' <?php if ($path_account_permission == "讀寫") echo " selected" ?>>讀寫</option>
				</select>
            </div>
			
			<div class = 'input-group mb-3'>
				<div class = 'input-group-prepend'>
					<label class = 'input-group-text' for = 'path_access_permission'>可查詢等級</label>
				</div>
				<?php
					echo $path_access_permission;
				?>
            </div>
				
            <div>
                <input class = "btn btn-primary" type="submit" value="新增">
                <input class = "btn btn-secondary" type="reset" value="重置">
            </div>
        </form>
		<?php if (!empty($errors["insert"])) echo "<div class='alert alert-danger'>" . $errors["insert"] . "</div>" ?>
    </div>    
</body>
</html>