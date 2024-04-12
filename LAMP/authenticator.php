<?php
	/*
	* Modify: MAN50 HaoYu Liang#3555
	* File Name: authenticator.php
	* Purpose: 使用者相關
	* Class: 1
	* Class Name: User
	* Function: 9
	*/
	
	//宣告命名空間
	namespace myApp;
	use \Exception;
	
	//引用設定文件
	require_once "sql.php";
	require_once "sessionControl.php";
	const _TABLE_NAME = "mausers";
	const _TABLE_NAME4 = "user_operation";
	const _TABLE_NAME5 = "operation";
	const _TABLE_NAME8= "permission";
	
	//Class Start
	class User{
		/*
		* Function Name: authenticate
		* Parameter Type: String, String
		* Action: 登入驗證
		*/
		public function authenticate(string $username, string $password): bool{			
			//嘗試執行SQL Command
			try{
				$result = self::getUsers($username);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			if ($result -> num_rows == 1){
				$user = $result -> fetch_assoc();
				
				if (password_verify($password, $user["password"])){  //認證成功
					//儲存登入紀錄
					$sessionData["loggedin"] = true;
					$sessionData["id"] = $user["id"];
					$sessionData["username"] = $user["username"];
					$sessionData["realname"] = $user["realname"];
					$sessionData["permission"] = $user["permission"];
					$sessionData["lastActivity"] = time();
					$sessionData["permission_zh"] = $user["permission_zh"];
					$sessionData["secretary_fg"] = $user["secretary_fg"];
					
					try{
						\myApp\sessionControl::crerteSessionData($sessionData);
					} catch (Exception $e){
						throw new Exception($e -> getMessage());
					}
				} else {  //認證失敗
					throw new Exception("登入失敗!錯誤的帳號或密碼!");
				}
			} else {
				throw new Exception("登入失敗!錯誤的帳號或密碼!");
			}
			
			return true;
		}
		
		/*
		* Function Name: check_account
		* Parameter Type: String
		* Action: 檢查帳號是否存在
		*/
		public function check_account(string $username): bool{
			//嘗試執行SQL Command
			try{
				$result = self::getUsers($username);
				
				if (!$result -> num_rows == 0){  //已有帳號
					return true;
				}
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return false;
		}
		
		/*
		* Function Name: registAccount
		* Parameter Type: String, String, String, String Array
		* Action: 註冊帳號
		*/
		public function registAccount(string $realname, string $username, string $password, array $form_data, int $permission): bool{

			$sqlStr = "INSERT INTO " . _TABLE_NAME . " (realname, username, password, permission) VALUES (?, ?, ?, ?)";
			$hash_password = password_hash($password, PASSWORD_DEFAULT);  //密碼加密
			
			//嘗試執行SQL Command
			try{
				$result = \myApp\DB::executeSQL($sqlStr, [str_repeat("s", 4), $realname, $username, $hash_password, $permission ]);
				foreach ($form_data as $key => $value){
					$sqlStr = "INSERT INTO " . _TABLE_NAME4 . " " .
							  "(username, operation_name) VALUES (?, ?)";
					\myApp\DB::executeSQL($sqlStr, ["ss", $username, $key]);
				}
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return true;
		}
		
		/*
		* Function Name: loginTimeOut
		* Parameter Type: N/A
		* Action: 登入逾時
		*/
		public static function loginTimeOut(): bool{
			try{
				if (\myApp\sessionControl::checkSessionTimeOut()){
					return true;
				} else {
					try{
						\myApp\sessionControl::resetSessionActivate();
						return false;
					} catch (Exception $e){
						throw new Exception($e -> getMessage());
					}
				}
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			} 
		}
		
		/*
		* Function Name: getUsers
		* Parameter Type: (Optional)String
		* Action: 查詢User
		*/
		public function getUsers(string $username = ""): ?\mysqli_result{
			if (empty($username)){
				$sqlStr = "SELECT * FROM " . _TABLE_NAME . " A, " . _TABLE_NAME8 . " B WHERE A.permission = B.permission";
				try{
					$conn = \myApp\DB::createSQLConnection();
					$result = $conn -> query($sqlStr);
					$conn -> close();
				} catch (Exception $e){
					throw new Exception($e -> getMessage());
				}
			} else {
				$sqlStr = "SELECT * FROM " . _TABLE_NAME . " A, " . _TABLE_NAME8 . " B WHERE A.username = ? AND A.permission = B.permission";
				try{
					$result = \myApp\DB::executeSQL($sqlStr, ["s", $username]);
				} catch (Exception $e){
					throw new Exception($e -> getMessage());
				}
			}
			
			return $result;
		}
		
		/*
		* Function Name: getUsersOperation
		* Parameter Type: String
		* Action: 查詢User作業單元
		*/
		public function getUsersOperation(string $username): ?\mysqli_result{
			$sqlStr = "SELECT A.username, A.operation_name, B.operation_name_zh " . 
					  "FROM " . _TABLE_NAME4 . " A, " . _TABLE_NAME5 . " B " . 
					  "WHERE A.username = ? AND A.operation_name = B.operation_name";
			try{
				$result = \myApp\DB::executeSQL($sqlStr, ["s", $username]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return $result;
		}
		
		/*
		* Function Name: checkUsersOperation
		* Parameter Type: String, String
		* Action: 確認User是否屬於特定單元
		*/
		public function checkUsersOperation(string $username, string $operation): bool{
			$sqlStr = "SELECT operation_name " . 
					  "FROM " . _TABLE_NAME4 . " " . 
					  "WHERE username = ? AND operation_name = ?";
			try{
				$result = \myApp\DB::executeSQL($sqlStr, ["ss", $username, $operation]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			if($result == null){
				return false;
			} else {
				return true;
			}
		}
		
		/*
		* Function Name: updateUser
		* Parameter Type: String
		* Action: 更新User基本資料
		*/
		public function updateUser(string $realname, string $username, string $permission, array $form_data, string $password): bool{
			try{
				$sqlStr = "UPDATE " . _TABLE_NAME . " " .
						  "SET realname = ?, permission = ?, password = ? " .
						  "WHERE username = ?";
				//$result = \myApp\DB::executeSQL($sqlStr, [str_repeat("sss", 3 + count($form_data)), $realname, $permission, ...array_values($form_data), $username]);
				$hash_password = password_hash($password, PASSWORD_DEFAULT);  //密碼加密
				\myApp\DB::executeSQL($sqlStr, ["ssss", $realname, $permission, $hash_password, $username]);
				
				$sqlStr = "DELETE FROM " . _TABLE_NAME4 . " " .
						  " WHERE username = ?";
				\myApp\DB::executeSQL($sqlStr, ["s", $username]);
				
				foreach ($form_data as $key => $value){
					$sqlStr = "INSERT INTO " . _TABLE_NAME4 . " " .
							  "(username, operation_name) VALUES (?, ?)";
					\myApp\DB::executeSQL($sqlStr, ["ss", $username, $value]);
				}
				
				
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return true;
		}
		
		/*
		* Function Name: deleteUser
		* Parameter Type: String
		* Action: 刪除User基本資料
		*/
		public function deleteUser(string $username): bool{
			try{
				$sqlStr = "DELETE FROM " . _TABLE_NAME4 . " " .
						  " WHERE username = ?";
				\myApp\DB::executeSQL($sqlStr, ["s", $username]);
				
				$sqlStr = "DELETE FROM " . _TABLE_NAME . " " .
						  " WHERE username = ?";
				\myApp\DB::executeSQL($sqlStr, ["s", $username]);
				
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return true;
		}
	}
	
?>