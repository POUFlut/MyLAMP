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
		$folder_type_post = trim($_POST["folder_type"] ?? '');
		$operation_name_post = trim($_POST["operation_name"] ?? '');
		$comment = trim($_POST["comment"] ?? '');
		
		if ($folder_path == ""){
			$errors["folder_path"] = "請輸入路徑";
		}
		
		if ($folder_name == ""){
			$errors["folder_name"] = "請輸入資料夾名稱";
		}
		
		if ($comment == ""){
			$errors["comment"] = "請輸入中文說明";
		}
		
		if (empty($errors)){
			try{
				if (\myApp\DB::checkFolderExist($folder_name)){
					echo ("<script>alert('資料夾已存在!')</script>;");
					$errors["insert"] = "資料夾已存在";
				};
			} catch (Exception $e){
				$errors["insert"] = $e -> getMessage();
			}
		}
		
		if(empty($errors)){
			try{
				\myApp\DB::insertFolder($folder_name, $comment, $folder_type_post, $operation_name_post);
				\myApp\DB::insertFolderDetail($folder_name, $folder_path, $path_account, $path_password, $path_account_permission, $path_access_permission_post, $path_type_post);
				echo ("<script>alert('新增成功!')</script>;");
				echo ("<script>window.close()</script>;");
				exit();
			} catch (Exception $e){
				$errors["insert"] = $e -> getMessage();
			}
		}
	}
	
	$path_type = "<select class = 'custom-select' name = \"path_type\" id = 'path_type'>";
	$path_access_permission = "<select class = 'custom-select' name = \"path_access_permission\" id = 'path_access_permission'>";
	$folder_type = "<select class = 'custom-select' name = \"folder_type\" id = 'folder_type'>";
	$operation_name = "<select class = 'custom-select' name = \"operation_name\" id = 'operation_name'>";
	try{
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
		
		$folder_type_list = \myApp\DB::getFolderType();
		while($row_folder_type_list = $folder_type_list -> fetch_assoc()){
			$folder_type .= "<option value = '" . $row_folder_type_list["folder_type"] . "' ";
			if ($row_folder_type_list["folder_type"] == $folder_type_post){
				$folder_type .= "selected";
			}
			
			$folder_type .= ">" . $row_folder_type_list["folder_type_zh"] . "</option>";
		}
		$folder_type .= "</select>";
		
		$operation_name_list = \myApp\DB::getOperationList();
		while($row_operation_name_list = $operation_name_list -> fetch_assoc()){
			$operation_name .= "<option value = '" . $row_operation_name_list["operation_name"] . "' ";
			if ($row_operation_name_list["operation_name"] == $operation_name_post){
				$operation_name .= "selected";
			}
			
			$operation_name .= ">" . $row_operation_name_list["operation_name_zh"] . "</option>";
		}
		$operation_name .= "</select>";
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
    <title>新增資料夾</title>
</head>
<body>
    <div class = "container mt-5">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" accept-charset = "utf-8">
			<div class = "control-group">
                <label for = "folder_name">資料夾名稱(FTP位置請填IP)</label>
                <input class="form-control <?php echo (!empty($errors["folder_name"])) ? 'is-invalid' : ''; ?>" type="text" name="folder_name" id = "folder_name" value="<?php echo $folder_name; ?>">
				<span class="invalid-feedback"><?php echo $errors["folder_name"]; ?></span>
            </div><br/>
			
			<div class = "control-group">
                <label for = "folder_name">資料夾中文說明</label>
                <input class="form-control <?php echo (!empty($errors["comment"])) ? 'is-invalid' : ''; ?>" type="text" name="comment" id = "comment" value="<?php echo $comment; ?>">
				<span class="invalid-feedback"><?php echo $errors["comment"]; ?></span>
            </div><br/>
			
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
					<label class = 'input-group-text' for = 'folder_type'>資料夾類型</label>
				</div>
				<?php
					echo $folder_type;
				?>
            </div>
			
			<div class = 'input-group mb-3'>
				<div class = 'input-group-prepend'>
					<label class = 'input-group-text' for = 'operation_name'>所屬作業單元</label>
				</div>
				<?php
					echo $operation_name;
				?>
            </div>
			
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