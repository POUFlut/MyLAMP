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
	
	//檢查帳號是否存在
	$username = trim($_SESSION["username"]);
	
	try{
		if(!$user -> check_account($username)){
			try{
				\myApp\sessionControl::deleteSessionData();
				echo ("<script>alert('登入認證異常，請重新登入')</script>;");  //建立彈窗提示
				echo ("<script>window.location.href = 'login.php';</script>");  //導向登入頁面
				exit();
			} catch (Exception $e){
				echo ("<script>alert('" . $e -> getMessage() . "')</script>;");  //建立彈窗提示
			}
		}
	} catch (Exception $e){
		echo ("<script>alert('" . $e -> getMessage() . "')</script>;");  //建立彈窗提示
	}
	
	//搜尋所屬作業單元
	//嘗試執行SQL Command
	try{
		$result = $user -> getUsersOperation($username);
	} catch (Exception $e){
		echo ("<script>alert('" . $e -> getMessage() . "')</script>;");  //建立彈窗提示
	}
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST'){
		//初始化變數
		$operation = trim($_POST["operation"] ?? '');
		$file_name = trim($_FILES["fileToUpload"]["name"] ?? '');
		$errors = [];
		$target_dir = "uploads/";
		
		//檢查是否選擇作業單元
		if (empty($operation)){
			$errors["upload"] = "請選擇上傳的作業單元";
		}
		
		//檢查是否有上傳檔案
		if (empty($file_name)){
			$errors["upload"] = "請確認檔案是否小於2MB";
		}
		
		//檢查是否為PDF
		if (empty($errors)){
			$target_file = $target_dir . $operation . "/" . $file_name;
			$fileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
			if ($fileType != "pdf"){
				$errors["upload"] = "只可上傳PDF檔案";
			}
		}
		
		if (empty($errors)){
			try{
				$result_post = \myApp\MEMO::selectUploadHistory($file_name, $operation);
			} catch (Exception $e){
				$errors["upload"] = $e -> getMessage();
			}
		}
		
		if (empty($errors)){
			//建立上傳紀錄
			$datatime = date("Y-m-d H:i:s",time());
			if (empty($result_post)){
				try{
					\myApp\MEMO::insertUploadHistory($username, $username, $datatime, $datatime, $operation, $file_name);
				} catch (Exception $e){
					$errors["upload"] = $e -> getMessage();
				}
			} else {
				try{
					\myApp\MEMO::updateUploadHistory($username, $datatime, $file_name);
				} catch (Exception $e){
					$errors["upload"] = $e -> getMessage();
				}
			}
		}
		
		//取得閱讀紀錄ID
		if (empty($errors)){
			//取得ID
			try{
				$result_post = \myApp\MEMO::selectUploadHistory($file_name, $operation);
				$row = $result_post -> fetch_assoc();
				$id = $row["id"];
			} catch (Exception $e){
				$errors["upload"] = $e -> getMessage();
			}
			
			//取得username list
			try{
				$result_post = $user -> getUsers();
			} catch (Exception $e){
				$errors["upload"] = $e -> getMessage();
			}
		}
		
		//更新閱讀紀錄
		if (empty($errors)){
			while($row = $result_post -> fetch_assoc()){
				try{
					$result_post2 = \myApp\MEMO::selectUploadHistory($id, $row["username"]);
				} catch (Exception $e){
					$errors["upload"] = $e -> getMessage() . $id;
				}
				
				//確認是必須閱讀
				try{
					$need_flag = $user -> checkUsersOperation($row["username"], $operation);
					if($need_flag == false){
						$need_flag = 0;
					} else {
						$need_flag = 1;
					}
				} catch (Exception $e){
					$errors["upload"] = $e -> getMessage() . $id;
				}
				
				//寫入資料
				if (empty($errors)){
					if (empty($result_post2)){
						try{
							\myApp\MEMO::insertReadHistory($row["username"], $id, $need_flag);
						} catch (Exception $e){
							$errors["upload"] = $e -> getMessage() . $id;
						}
					} else {
						try{
							\myApp\MEMO::updateReadHistory($need_flag, $id, $row["username"]);
						} catch (Exception $e){
							$errors["upload"] = $e -> getMessage();
						}
					}
				}
			}
		}
		
		if (empty($errors)){
			try{
				mkdir($target_dir . $operation);
				move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
				echo ("<script>alert('上傳成功')</script>");  //建立彈窗提示
			} catch (Exception $e){
				$errors["upload"] = $e -> getMessage();
			}
		}
		
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
    <title>上傳交接</title>
</head>
<body>
    <?php require "../nav.php"; ?>
	<div class = "container mt-5">
		<form action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method = "post" enctype = "multipart/form-data">
			<div class = 'input-group mb-3'>
				<div class = 'input-group-prepend'>
					<label class = 'input-group-text' for = 'operation'>作業單元</label>
				</div>
				<select class = 'custom-select' name = "operation" id = "operation">
					<?php
						while ($row_get = $result -> fetch_assoc()){
							echo "<option value = '" . $row_get["operation_name"] . "'>" . $row_get["operation_name_zh"] . "</option>";
						};
					?>
				</select>
			</div>
			<div class="card">
				<div class="card-header">
					上傳檔案
				</div>
				<div class="card-body">
					<div class="form-group">
						<label for="file">※僅可上傳PDF格式</label>
						<input type="file" class="form-control-file" name="fileToUpload">
					</div>
					<button type="submit" class="btn btn-primary">上傳</button>
				</div>
			</div>
		</form>
		<?php if (!empty($errors["upload"])) echo "<div class='alert alert-danger'>" . $errors["upload"] . "</div>" ?>
	</div>
	<?php require "../footer.php" ?>
</body>
</html>
