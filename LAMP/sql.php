<?php
	/*
	* Modify: MAN50 HaoYu Liang#3555
	* File Name: sql.php
	* Purpose: 資料庫交互
	* Class: 1
	* Class Name: DB
	* Function: 13
	*/
	
	//宣告命名空間
	namespace myApp;
	use \mysqli;
	use \Exception;
	
	//引用設定文件
	require_once "config.php";
	
	//Class Start
	class DB{
		
		/*
		* Function Name: createSQLConnection
		* Parameter Type: String Array
		* Action: 建立SQL連線
		*/
		public static function createSQLConnection(): \mysqli{
			$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
			
			//確認連線是否成功
			if ($conn -> connect_error){
				throw new Exception("Connect SQL fail: " . $conn -> connect_error);
			}
			
			//確認轉換編碼是否成功
			if (!$conn -> set_charset("utf8")){
				$conn -> close();
				throw new Exception("Encoding fail: " . $conn -> error);
			}
			
			return $conn;
		}
		
		/*
		* Function Name: createSQLConnection
		* Parameter Type: String(SQL Command), String Array(params type, ...data)
		* Action: 使用Prepare 方式執行 SQL 命令
		*/
		public static function executeSQL(string $query, array $params): ?\mysqli_result{
			//建立資料庫連線
			$conn = self::createSQLConnection();
			
			//執行Prepare 方法
			$stmt = $conn -> prepare($query);
			
			//確認是否成功
			if (!$stmt){
				throw new Exception("Prepare fail: " . $conn -> error);
			}
			
			//添加參數
			$stmt -> bind_param(...$params);
			
			//執行搜尋&確認是否成功
			if (!$stmt -> execute()){
				throw new Exception("Execute fail: " . $stmt -> error);
			}
			
			//回傳搜尋結果
			$results = $stmt -> get_result();
			$stmt -> close();
			$conn -> close();
			
			if ($results === false){
				return null;
			}
			
			if ($results -> num_rows == 0){
				return null;
			}
			
			return $results;
		}
		
		/*
		* Function Name: getOperationList
		* Parameter Type: N/A
		* Action: 取得Operation Table
		*/
		public static function getOperationList(): ?\mysqli_result{
			//建立資料庫連線
			$conn = self::createSQLConnection();
			
			//執行SQL 方法
			$sqlStr = "SELECT * FROM operation WHERE enable = '1' ORDER BY SEQ";
			$results = $conn -> query($sqlStr);
			$conn -> close();
			
			if ($results === false){
				return null;
			}
			
			if ($results -> num_rows == 0){
				return null;
			}
			
			return $results;
		}
		
		/*
		* Function Name: getPermissionList
		* Parameter Type: N/A
		* Action: 取得Permission Table
		*/
		public static function getPermissionList(): ?\mysqli_result{
			//建立資料庫連線
			$conn = self::createSQLConnection();
			
			//執行SQL 方法
			$sqlStr = "SELECT * FROM permission";
			$results = $conn -> query($sqlStr);
			$conn -> close();
			
			if ($results === false){
				return null;
			}
			
			if ($results -> num_rows == 0){
				return null;
			}
			
			return $results;
		}
		
		/*
		* Function Name: getFolderType
		* Parameter Type: N/A
		* Action: 取得FolderType Table
		*/
		public static function getFolderType(): ?\mysqli_result{
			//建立資料庫連線
			$conn = self::createSQLConnection();
			
			//執行SQL 方法
			$sqlStr = "SELECT * FROM folder_type WHERE enable = '1'";
			$results = $conn -> query($sqlStr);
			$conn -> close();
			
			if ($results === false){
				return null;
			}
			
			if ($results -> num_rows == 0){
				return null;
			}
			
			return $results;
		}
		
		/*
		* Function Name: getFolderPathType
		* Parameter Type: N/A
		* Action: 取得 FolderPathType Table
		*/
		public static function getFolderPathType(): ?\mysqli_result{
			//建立資料庫連線
			$conn = self::createSQLConnection();
			
			//執行SQL 方法
			$sqlStr = "SELECT * FROM folder_path_type ORDER BY id";
			$results = $conn -> query($sqlStr);
			$conn -> close();
			
			if ($results === false){
				return null;
			}
			
			if ($results -> num_rows == 0){
				return null;
			}
			
			return $results;
		}
		
		/*
		* Function Name: getFolderPath
		* Parameter Type: N/A
		* Action: 取得 FolderPath Table
		*/
		public static function getFolderPath(): ?\mysqli_result{
			//建立資料庫連線
			$conn = self::createSQLConnection();
			
			//執行SQL 方法
			$sqlStr = "SELECT * FROM folder_path ORDER BY comment";
			$results = $conn -> query($sqlStr);
			$conn -> close();
			
			if ($results === false){
				return null;
			}
			
			if ($results -> num_rows == 0){
				return null;
			}
			
			return $results;
		}
		
		/*
		* Function Name: getFolderDetail
		* Parameter Type: String folder_name, String folder_path, String Account
		* Action: 取得單筆路徑資料
		*/
		public static function getFolderDetail(string $folder_name, string $folder_path, string $account): ?\mysqli_result{
			
			//執行SQL 方法
			$sqlStr = "SELECT * FROM folder_path A, folder_path_detail_list B, permission C, folder_type D, operation E WHERE B.folder_name = ? AND B.folder_path = ? AND B.path_account = ? AND A.folder_name = B.folder_name AND B.path_access_permission = C.permission AND A.folder_type = D.folder_type AND A.operation_name = E.operation_name";
			
			//嘗試執行SQL Command
			try{
				$results = self::executeSQL($sqlStr, ["sss", $folder_name, $folder_path, $account]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			if ($results === false){
				return null;
			}
			
			if ($results -> num_rows == 0){
				return null;
			}
			
			return $results;
		}
		
		/*
		* Function Name: updateFolderDetail
		* Parameter Type: String folder_name, String folder_path, String comment, String operation_name, String folder_type, String path_password, String path_account_permission, String path_access_permission, String path_type, String path_account, String path_account_org, String folder_path_org
		* Action: 修改Folder Detail
		*/
		public static function updateFolderDetail(string $folder_name, string $folder_path, string $comment, string $operation_name, string $folder_type, string $path_password, string $path_account_permission, string $path_access_permission, string $path_type, string $path_account, string $path_account_org, string $folder_path_org): bool{
			//嘗試執行SQL Command
			try{
				//執行SQL 方法
				$sqlStr = "UPDATE folder_path SET comment = ?, folder_type = ?, operation_name = ? WHERE folder_name = ?";
				
				self::executeSQL($sqlStr, ["ssss", $comment, $folder_type, $operation_name, $folder_name]);
				
				$sqlStr = "UPDATE folder_path_detail_list SET folder_path = ?, path_account = ?, path_password = ?, path_account_permission = ?, path_access_permission = ?, path_type = ? WHERE folder_name = ? AND path_account = ? AND folder_path = ?";
				
				self::executeSQL($sqlStr, [str_repeat("s", 9), $folder_path, $path_account, $path_password, $path_account_permission, $path_access_permission, $path_type, $folder_name, $path_account_org, $folder_path_org]);
				
				
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return true;
		}
		
		/*
		* Function Name: deleteFolderDetail
		* Parameter Type: String folder_name, String folder_path, String path_account
		* Action: 刪除Folder Detail
		*/
		public static function deleteFolderDetail(string $folder_name, string $folder_path, string $path_account): bool{
			//嘗試執行SQL Command
			try{
				//執行SQL 方法
				$sqlStr = "DELETE FROM folder_path_detail_list WHERE folder_name = ? AND folder_path = ? AND path_account = ?";
				
				self::executeSQL($sqlStr, ["sss", $folder_name, $folder_path, $path_account]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return true;
		}
		
		/*
		* Function Name: insertFolderDetail
		* Parameter Type: String folder_name, String folder_path, String comment, String operation_name, String folder_type, String path_password, String path_account_permission, String path_access_permission, String path_type, String path_account, String path_account_org, String folder_path_org
		* Action: 新增Folder Detail
		*/
		public static function insertFolderDetail(string $folder_name, string $folder_path, string $path_account, string $path_password, string $path_account_permission, string $path_access_permission, string $path_type): bool{
			//嘗試執行SQL Command
			try{
				//執行SQL 方法
				$sqlStr = "INSERT INTO folder_path_detail_list (folder_name, folder_path, path_account, path_password, path_account_permission, path_access_permission, path_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
				
				self::executeSQL($sqlStr, ["sssssss", $folder_name, $folder_path, $path_account, $path_password, $path_account_permission, $path_access_permission, $path_type]);
				
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return true;
		}
		
		/*
		* Function Name: checkFolderExist
		* Parameter Type: String folder_name
		* Action: 檢查Folder是否存在
		*/
		public static function checkFolderExist(string $folder_name): bool{
			//嘗試執行SQL Command
			try{
				$sqlStr = "SELECT folder_name FROM folder_path WHERE folder_name = ?";
				$results = self::executeSQL($sqlStr, ["s", $folder_name]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			if ($results === false){
				return false;
			}
			
			if ($results -> num_rows == 0){
				return false;
			}
			
			return true;
		}
		
		/*
		* Function Name: insertFolder
		* Parameter Type: string $folder_name, string $comment, string $folder_type, string $operation_name
		* Action: 新增Folder
		*/
		public static function insertFolder(string $folder_name, string $comment, string $folder_type, string $operation_name): bool{
			//嘗試執行SQL Command
			try{
				//執行SQL 方法
				$sqlStr = "INSERT INTO folder_path (folder_name, comment, folder_type, operation_name, enable) VALUES (?, ?, ?, ?, ?)";
				
				self::executeSQL($sqlStr, ["sssss", $folder_name, $comment, $folder_type, $operation_name, "1"]);
				
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return true;
		}
	}
	
?>