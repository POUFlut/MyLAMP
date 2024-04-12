<?php
	//引用設定文件
	require_once "../config.php";  
	require_once "../sessionControl.php";  
	require_once "../sql.php";
	require_once "../authenticator.php";
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
	
	$folder_name = ($_SERVER['REQUEST_METHOD'] === 'POST') ? trim(urldecode($_POST["folder_name"]) ?? '') : trim(urldecode($_GET["folder_name"]) ?? '');
	$folder_path = ($_SERVER['REQUEST_METHOD'] === 'POST') ? trim(urldecode($_POST["folder_path"]) ?? '') : trim(urldecode($_GET["folder_path"]) ?? '');
	$path_account = ($_SERVER['REQUEST_METHOD'] === 'POST') ? trim($_POST["path_account"] ?? '') : trim($_GET["path_account"] ?? '');
	$errors = [];
	
	if (empty($folder_name) || empty($folder_path)){
		$errors["SQL"] = "資料不全";
	}
	
	if (empty($errors)){
		if ($_SERVER['REQUEST_METHOD'] === 'POST'){
			//初始化變數
			$comment = trim($_POST["comment"] ?? '');
			$operation_name = trim($_POST["operation_name"] ?? '');
			$folder_type = trim($_POST["folder_type"] ?? '');
			$path_password = trim($_POST["path_password"] ?? '');
			$path_account_permission = trim($_POST["path_account_permission"] ?? '');
			$path_access_permission = trim($_POST["path_access_permission"] ?? '');
			$path_type = trim($_POST["path_type"] ?? '');
			$path_account_org = trim($_POST["path_account_org"] ?? '');
			$folder_path_org = trim($_POST["folder_path_org"] ?? '');
			if (empty($comment) || empty($folder_name) || empty($folder_path)){  //檢查資料完整性
				$errors["input"] = "資料不完整";
			}
			
			//將資料寫入資料庫
			if (empty($errors)){
				try{
					\myApp\DB::updateFolderDetail($folder_name, $folder_path, $comment, $operation_name, $folder_type, $path_password, $path_account_permission, $path_access_permission, $path_type, $path_account, $path_account_org, $folder_path_org);
					echo ("<script>alert('修改成功')</script>");  //建立彈窗提示
					//echo ("<script>window.close();</script>");
				} catch (Exception $e){
					$errors["update"] = $e -> getMessage();
				}
			}
		}
		
		try{
			$folder_detail = \myApp\DB::getFolderDetail($folder_name, $folder_path, $path_account);
			$permission_list = \myApp\DB::getPermissionList();
			$folder_path_type_list = \myApp\DB::getFolderPathType();
			$folder_type_list = \myApp\DB::getFolderType();
			$operation_list = \myApp\DB::getOperationList();
			
			$folder_detail_form = "";
			$row = $folder_detail -> fetch_assoc();
			$folder_detail_form .= "<div>";
			$folder_detail_form .= "<input type = 'hidden' name = 'folder_name' value = '" . $folder_name . "'>";
			$folder_detail_form .= "<input type = 'hidden' name = 'folder_path_org' value = '" . $folder_path . "'>";
			$folder_detail_form .= "<input type = 'hidden' name = 'path_account_org' value = '" . $path_account . "'>";
			$folder_detail_form .= "<div class='form-group'><label>資料夾說明:</label><input class='form-control' type = 'textbox' name = 'comment' value = '" . $row["comment"] . "'></div>";
			$folder_detail_form .= "<div class = 'input-group mb-3'><div class = 'input-group-prepend'><label class = 'input-group-text' for = 'operation_name'>作業單元:</label></div><select class = 'custom-select' name = \"operation_name\" id = 'operation_name'>";
			while($row_operation_list = $operation_list -> fetch_assoc()){
				$folder_detail_form .= "<option value = '" . $row_operation_list["operation_name"] . "' ";
				if ($row_operation_list["operation_name"] == $row["operation_name"]){
					$folder_detail_form .= "selected";
				}
				$folder_detail_form .= ">" . $row_operation_list["operation_name_zh"] . "</option>";
			}
			$folder_detail_form .= "</select></div>";
			
			$folder_detail_form .= "<div class = 'input-group mb-3'><div class = 'input-group-prepend'><label class = 'input-group-text' for = 'folder_type'>路徑類型:</label></div><select class = 'custom-select' name = \"folder_type\" id = 'folder_type'>";
			while($row_folder_type_list = $folder_type_list -> fetch_assoc()){
				$folder_detail_form .= "<option value = '" . $row_folder_type_list["folder_type"] . "' ";
				if ($row_folder_type_list["folder_type"] == $row["folder_type"]){
					$folder_detail_form .= "selected";
				}
				$folder_detail_form .= ">" . $row_folder_type_list["folder_type_zh"] . "</option>";
			}
			$folder_detail_form .= "</select></div>";
			
			$folder_detail_form .= "<div class='form-group'><label>路徑:</label><input class='form-control' type = 'textbox' name = 'folder_path' value = '" . $row["folder_path"] . "'></div>";
			$folder_detail_form .= "<div class='form-group'><label>帳號:</label><input class='form-control' type = 'textbox' name = 'path_account' value = '" . $row["path_account"] . "'></div>";
			$folder_detail_form .= "<div class='form-group'><label>密碼:</label><input class='form-control' type = 'textbox' name = 'path_password' value = '" . $row["path_password"] . "'></div>";
			
			$folder_detail_form .= "<div class = 'input-group mb-3'><div class = 'input-group-prepend'><label class = 'input-group-text' for = 'path_account_permission'>瀏覽權限:</label></div><select class = 'custom-select' name = \"path_account_permission\" id = 'path_account_permission'>";
			$folder_detail_form .= "<option value = '唯讀' ";
			if ($row["path_account_permission"] == "唯讀"){
				$folder_detail_form .= "selected";
			}
			$folder_detail_form .= ">唯讀</option>";
			$folder_detail_form .= "<option value = '讀寫' ";
			if ($row["path_account_permission"] == "讀寫"){
				$folder_detail_form .= "selected";
			}
			$folder_detail_form .= ">讀寫</option>";
			$folder_detail_form .= "</select></div>";
			
			$folder_detail_form .= "<div class = 'input-group mb-3'><div class = 'input-group-prepend'><label class = 'input-group-text' for = 'path_type'>路徑類型:</label></div><select class = 'custom-select' name = \"path_type\" id = 'path_type'>";
			while($row_folder_path_type = $folder_path_type_list -> fetch_assoc()){
				$folder_detail_form .= "<option value = '" . $row_folder_path_type["path_type"] . "' ";
				if ($row_folder_path_type["path_type"] == $row["path_type"]){
					$folder_detail_form .= "selected";
				}
				$folder_detail_form .= ">" . $row_folder_path_type["path_type"] . "</option>";
			}
			$folder_detail_form .= "</select></div>";
			
			$folder_detail_form .= "<div class = 'input-group mb-3'><div class = 'input-group-prepend'><label class = 'input-group-text' for = 'path_access_permission'>可查詢權限:</label></div><select class = 'custom-select' name = \"path_access_permission\" id = 'path_access_permission'>";
			while($row_permission = $permission_list -> fetch_assoc()){
				$folder_detail_form .= "<option value = '" . $row_permission["permission"] . "' ";
				if ($row_permission["permission"] == $row["path_access_permission"]){
					$folder_detail_form .= "selected";
				}
				$folder_detail_form .= ">" . $row_permission["permission_zh"] . "</option>";
			}
			$folder_detail_form .= "</select></div>";
			$folder_detail_form .= "</div>";
			
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
	<link rel = "icon" href = "../icon.png" type = "image/png">
	<link rel="stylesheet" href="../bootstrap/4.0.0/css/bootstrap.min.css">
	<link rel="stylesheet" href="../jquery/ui/1.13.2/themes/base/jquery-ui.css">
	<script type="text/javascript" src="../jquery/jquery.min.js"></script>
	<script type="text/javascript" src="../jquery/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../bootstrap/4.0.0/js/bootstrap.min.js"></script>
	<title>編輯路徑</title>
</head>
<body>
    <div class = 'container mt-5 mb-3'>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" accept-charset = "utf-8">
            <?php echo $folder_detail_form; ?>
			<input class = "btn btn-primary" type = "submit" value = "修改">
			<input class = "btn btn-danger" type = "button" onclick = "delete_folder( <?php echo '\'' . urlencode($folder_name) . '\', \'' . urlencode($folder_path) . '\', \'' . urlencode($path_account) . '\''; ?> )" value = "刪除">
        </form>
    </div>
	<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="confirmationModalLabel">刪除路徑</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div id = "modal-body" class="modal-body">
					是否確定刪除?
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
					<button type="button" class="btn btn-danger" id="confirmDelete">Yes</button>
				</div>
			</div>
		</div>
	</div>
	<?php if (!empty($errors["update"])) echo "<div class='alert alert-danger'>" . $errors["update"] . "</div>" ?>
	<?php if (!empty($errors["input"])) echo "<div class='alert alert-danger'>" . $errors["input"] . "</div>" ?>
	<?php if (!empty($errors["SQL"])) echo "<div class='alert alert-danger'>" . $errors["SQL"] . "</div>" ?>
</body>
<script>
	const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const confirmDeleteButton = document.getElementById('confirmDelete');
	var folder_name = null;
	var folder_path = null;
	var path_account = null;
	
	function delete_folder(folder_name,folder_path,path_account){
		
		window.folder_name = folder_name;
		window.folder_path = folder_path;
		window.path_account = path_account;
		
		document.getElementById('modal-body').innerHTML = "是否確定刪除路徑 " + decodeURIComponent(folder_path) + " ?<br>※僅會刪除此連線路徑和帳號的組合";
		confirmationModal.show();
		
	}
	
	confirmDeleteButton.addEventListener('click', function(){
		handleYesButtonClick();
	});
	
	function handleYesButtonClick() {
		
		if (window.username !== null){
			var xhr = new XMLHttpRequest();  //建立XMLHttpRequest物件
		
			xhr.open('POST', 'delete_path.php');  //設置Request資料(Method, URL, *async)
			xhr.setRequestHeader('Content-Type', 'application/json');
			
			//設置Time Out
			xhr.timeout = 60000; //單位:ms
			xhr.ontimeout = function(){
				alert('請求超時!');
			};
			
			//設置Response監聽
			xhr.onreadystatechange = function(){
				if (xhr.readyState === 4 && xhr.status === 200){
					if(xhr.responseText.trim() == "1"){
						alert('刪除成功!');
					} else {
						alert('刪除失敗，請重試!錯誤：' + xhr.responseText);
					}
					
					confirmationModal.hide();
					window.close();
				};
			};
			
			//建立JSON格式物件
			var data = {
				pfolder_name: window.folder_name,
				pfolder_path: window.folder_path,
				ppath_account: window.path_account
			};
			
			//Request
			xhr.send(JSON.stringify(data));
		} else {
			alert('獲取號失敗!請重試!');
			confirmationModal.hide();
		};
	}
</script>
</html>
