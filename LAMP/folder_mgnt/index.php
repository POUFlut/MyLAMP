<?php
	//引用設定文件
	require_once "../config.php";  
	require_once "../sessionControl.php";  
	require_once "../sql.php";
	require_once "../authenticator.php";
	const _TABLE_NAME4 = "folder_path";
	const _TABLE_NAME5 = "folder_path_detail";
	
	//確認是否已登入
	if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)){
		//header("location: ../login.php");
		//exit();
		$_SESSION["permission"] = 1;
		$_SESSION["permission_zh"] = "遊客";
	}
	
	if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
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
	}
	
	//取得operation list
	$operation_list = \MyApp\DB::getOperationList();
	$operation_form = "<div class='form-group'>作業單元：<div class='form-check form-check-inline'><label class='form-check-label'><input class='form-check-input' type='radio' name='operation_name' value = '%' checked >All</label></div>";
	while ($row = $operation_list -> fetch_assoc()){
		$operation_form .= "<div class='form-check form-check-inline'><label class='form-check-label'><input class='form-check-input' type='radio' name='operation_name' value = '" . $row["operation_name"] . "'>" . $row["operation_name_zh"] . "</label></div>";
	}
	$operation_form .= "</div>";
	
	//取得folder type list
	$folder_type_list = \MyApp\DB::getFolderType();
	$folder_type_form = "<div class='form-group'>類型：<div class='form-check form-check-inline'><label class='form-check-label'><input  class='form-check-input' type='radio' name='folder_type' value = '%' checked >All</label></div>";
	while ($row = $folder_type_list -> fetch_assoc()){
		$folder_type_form .= "<div class='form-check form-check-inline'><label class='form-check-label'><input class='form-check-input' type='radio' name='folder_type' value = '" . $row["folder_type"] . "'>" . $row["folder_type_zh"] . "</label></div>";
	}
	$folder_type_form .= "</div>";
	
	$permission_form = "<div class='form-group'>權限：<div class='form-check form-check-inline'><label class='form-check-label'><input class='form-check-input' type='radio' name='path_permission' value = '%' checked >All</label></div>";
	$permission_form .= "<div class='form-check form-check-inline'><label class='form-check-label'><input class='form-check-input' type='radio' name='path_permission' value = '讀寫' >讀寫</label></div>";
	$permission_form .= "<div class='form-check form-check-inline'><label class='form-check-label'><input class='form-check-input' type='radio' name='path_permission' value = '唯讀' >唯讀</label></div>";
	$permission_form .= "</div>";
	
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
		<title>網路磁碟路徑</title>
	</head>
	<body class = "containers">
		<?php require "../nav.php"; ?>
		<div class="container mt-5">
			
			<form id = "filter_form">
				<?php 
					echo $operation_form;
					echo $folder_type_form;
					echo $permission_form;
				?>
				<div class='form-group'>
					<input type = "button" class = "btn btn-primary" onclick = "filter()" value = "搜尋">
					<?php 
						if ($_SESSION["permission"] >= 3){
							echo "<input type = \"button\" class = \"btn btn-info\" onclick = \"showCreatePath()\" value = \"新增\">";
						}
					?>
				</div>
			</form>
		</div>
		
		<div>
			<table class = "table table-bordered text-center align-middle" id = "path_table">
			</table>
		</div>
		
		<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="confirmationModalLabel">建立類型</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div id = "modal-body" class="modal-body">
					請選擇新增的類型
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" onclick = "createFolder()">全新資料夾</button>
					<button type="button" class="btn btn-secondary" onclick = "createPath()">已有的資料夾增加路徑</button>
				</div>
			</div>
		</div>
	</div>
	<?php require "../footer.php" ?>
	</body>
	<script type = "text/javascript" >
		const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
		
		function showCreatePath(){
			confirmationModal.show();
		}
		
		function createPath(){
			//開新視窗
			var newWin = window.open("createPath.php", "_blank", "location = no, width = 400, height = 400, scrollbars = yes");
			
			//檢查新視窗是否關閉
			var checkInterval = setInterval(
				function(){
					if(newWin.closed){
						var masks = document.querySelectorAll('a');
						if (masks[0].style.pointerEvents == 'none'){
							for (var i = 0;i < links.length;i++){
								links[i].style.pointerEvents = 'auto';
							}
						}
						
						clearInterval(checkInterval);
						confirmationModal.hide();
						document.getElementById("searchBtn").click();
					}
				}
			, 100);
		}
		
		function createFolder(){
			//開新視窗
			var newWin = window.open("createFolder.php", "_blank", "location = no, width = 400, height = 400, scrollbars = yes");
			
			//檢查新視窗是否關閉
			var checkInterval = setInterval(
				function(){
					if(newWin.closed){
						var masks = document.querySelectorAll('a');
						if (masks[0].style.pointerEvents == 'none'){
							for (var i = 0;i < links.length;i++){
								links[i].style.pointerEvents = 'auto';
							}
						}
						
						clearInterval(checkInterval);
						confirmationModal.hide();
						document.getElementById("searchBtn").click();
					}
				}
			, 100);
		}
		
		function filter(){
			try{
				var form = document.getElementById("filter_form");
				var formData = new FormData(form);
				var filter_operation = formData.get("operation_name");
				var filter_type = formData.get("folder_type");
				var filter_path_permission = formData.get("path_permission");
			} catch (err) {
				var operation = document.getElementsByName("operation_name");
				for (var i = 0;i < operation.length;i++){
					if(operation[i].checked){
						filter_operation = operation[i].value;
						break;
					}
				}
				
				var type = document.getElementsByName("folder_type");
				for (i = 0;i < type.length;i++){
					if(type[i].checked){
						filter_type = type[i].value;
						break;
					}
				}
				
				var permission = document.getElementsByName("path_permission");
				for (i = 0;i < permission.length;i++){
					if(permission[i].checked){
						filter_path_permission = permission[i].value;
						break;
					}
				}
			}
			
			var xhr = new XMLHttpRequest();  //建立XMLHttpRequest物件
				
			xhr.open('POST', 'filter_path.php');  //設置Request資料(Method, URL, *async)
			xhr.setRequestHeader('Content-Type', 'application/json');
			
			//提示訊息
			document.getElementById("path_table").innerHTML = '讀取中...'
			
			//設置Time Out
			xhr.timeout = 60000; //單位:ms
			xhr.ontimeout = function(){
				document.getElementById("path_table").innerHTML = '請求超時!';
			};
			
			//設置Response監聽
			xhr.onreadystatechange = function(){
				if (xhr.readyState === 4 && xhr.status === 200){
					document.getElementById("path_table").innerHTML = xhr.responseText;
				};
			};
			
			//建立JSON格式物件
			var data = {
				pfilter_operation: filter_operation,
				pfilter_type: filter_type,
				ppath_permission: filter_path_permission
			};
			
			//Request
			xhr.send(JSON.stringify(data));
			
		}
		
		function maintain_folder(folder_name, folder_path, path_account){
			//停用所有超連結
			var links = document.querySelectorAll('a');
			for (var i = 0;i < links.length;i++){
				links[i].style.pointerEvents = 'none';
			}
			
			//開新視窗
			var newWin = window.open("maintain_folder.php?folder_name=" + folder_name + "&folder_path=" + folder_path + "&path_account=" + path_account, "_blank", "location = no, width = 400, height = 400, scrollbars = yes");
			
			//檢查新視窗是否關閉
			var checkInterval = setInterval(
				function(){
					if(newWin.closed){
						var masks = document.querySelectorAll('a');
						if (masks[0].style.pointerEvents == 'none'){
							for (var i = 0;i < links.length;i++){
								links[i].style.pointerEvents = 'auto';
							}
						}
						
						clearInterval(checkInterval);
						document.getElementById("searchBtn").click();
						//window.location.reload();
					}
				}
			, 100);
		}
		
		function copyPath(pindex, bindex){
			var btn = document.getElementById(bindex);
			var path = document.getElementById(pindex).textContent;
			var textarea = document.createElement("textarea");
			textarea.value = path;
			textarea.id = "tempText";
			
			try{
				document.body.appendChild(textarea);
				textarea = document.getElementById("tempText");
				textarea.select();
				document.execCommand("copy");
					btn.focus({
					preventScroll: true
				});
				document.body.removeChild(textarea);
				//alert("已複製");
				btn.textContent = "已複製";
			} catch(err){
				console.error("copy fail", err);
			}
		}
		
	</script>

</html>








