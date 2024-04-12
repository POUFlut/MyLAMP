<?php
	//引用設定文件
	require_once "../config.php";  
	require_once "../sql.php";
	require_once "../authenticator.php";
	const _TABLE_NAME5 = "operation";
	const _TABLE_NAME6 = "folder_path";
	const _TABLE_NAME7 = "folder_path_detail_list";
	const _TABLE_NAME8 = "folder_type";
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST'){
		$jsonData = json_decode(file_get_contents('php://input'));
		
		//檢查認證是否到期
		if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
			$user = new \myApp\User();
			try{
				if ($user -> loginTimeOut()){
					$errors["post"] = "Time out";
				}
			} catch (Exception $e){
				$errors["post"] = "Time out";
			}
		}
		
		//檢查是否POST Word
		if (!$jsonData -> pfilter_operation){
			$errors["post"] = "NULL Word";
		}
		
		//檢查是否POST Type
		if (!$jsonData -> pfilter_type){
			$errors["post"] = "NULL type";
		}
		
		//檢查是否POST Permission
		if (!$jsonData -> ppath_permission){
			$errors["post"] = "NULL permission";
		}
		
		$table = "";
		//取得資料
		if (empty($errors)){
			$sqlStr = "SELECT * FROM " . _TABLE_NAME6 . " A, " . _TABLE_NAME7 . " B, " . _TABLE_NAME5 . " C, " . _TABLE_NAME8 . " D " . 
					  "WHERE A.operation_name LIKE ? AND A.folder_name = B.folder_name AND B.path_access_permission <= ? AND A.folder_type LIKE ? AND A.operation_name = C.operation_name AND A.folder_type = D.folder_type AND A.enable = '1' AND B.path_account_permission LIKE ? ORDER BY A.folder_name";
			$table = "<thead><tr><th scope='col'>作業單元</th>" . 
					 "<th scope='col'>資料夾</th>" .
					 "<th scope='col'>類型</th>" . 
					 "<th scope='col'>路徑</th>" . 
					 ($_SESSION["permission"] > 1 ? "<th scope='col'>帳號</th>" : "") .
					 ($_SESSION["permission"] > 1 ? "<th scope='col'>密碼</th>" : "") .
					 "<th scope='col'>權限</th>" .
					 "<th scope='col'>類型</th>" . 
					 "<th scope='col'></th>" . 
					 ($_SESSION["permission"] >= 3 ? "<th scope='col'></th></tr></thead>" : "</tr></thead><tbody>");
			try{
				$result = \myApp\DB::executeSQL($sqlStr, ["ssss", "%" . $jsonData -> pfilter_operation . "%", $_SESSION["permission"], "%" . $jsonData -> pfilter_type . "%", "%" . $jsonData -> ppath_permission . "%"]);
				if(!empty($result)){
					$index = 1;
					while ($row = $result -> fetch_assoc()){
						$table .= "<tr><td>" . $row["operation_name_zh"] . "</td>" .
								  "<td>" . $row["comment"] . "</td>" .
								  "<td>" . $row["folder_type_zh"] . "</td>" .
								  "<td id = 'path" . $index . "'><a href='" . $row["folder_path"] . "'>" . $row["folder_path"] . "</a></td>" .
								  ($_SESSION["permission"] > 1 ? "<td>" . $row["path_account"] . "</td>" : "") .
								  ($_SESSION["permission"] > 1 ? "<td>" . $row["path_password"] . "</td>" : "") .
								  "<td>" . $row["path_account_permission"] . "</td>" .
								  "<td>" . $row["path_type"] . "</td>" .
								  "<td><button class = 'btn btn-info btn-sm' id = 'btn" . $index . "' onclick = 'copyPath(\"path" . $index . "\", \"btn" . $index . "\")'>複製</button></td>";
						if ($_SESSION["permission"] >= 3){
							$table .= "<td><a class = 'btn btn-primary btn-sm' href = 'javascript:void(0)' onclick = \"maintain_folder('" . urlencode($row["folder_name"]) . "', '" . urlencode($row["folder_path"]) . "', '" . $row["path_account"] . "')\">編輯</td></tr>";
						} else {
							$table .= "</tr>";
						}
						$index += 1;
					}
				}
				$table .= "</tbody>";
			} catch (Exception $e){
				$errors["post"] = $e -> getMessage();
			}
		}
		
		//Response
		if (empty($errors)){
			echo $table;
		} else {
			echo $errors["post"];
		}
		exit();
	}
?>