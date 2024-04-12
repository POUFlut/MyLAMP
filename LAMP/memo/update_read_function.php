<?php
	/*
	* Modify: MAN50 HaoYu Liang#3555
	* File Name: update_read_function.php
	* Purpose: 操作Memo相關Function
	* Class: 1
	* Class Name: MEMO
	* Function: 10
	*/
	
	//宣告命名空間
	namespace myApp;
	use \Exception;
	
	//引用設定文件
	const _TABLE_NAME = "mausers";
	const _TABLE_NAME2 = "memo_upload_histroy";
	const _TABLE_NAME3 = "memo_read_histroy";
	const _TABLE_NAME5 = "operation";
	
	//Class Start
	class MEMO{
		
		/*
		* Function Name: update_read_flag
		* Parameter Type: String, String Array
		* Action: 更新MEMO必須閱讀的清單
		*/
		public static function update_read_flag(string $username, array $form_data): bool{
			$sqlStr = "UPDATE " . _TABLE_NAME3 . " SET need_flag = '0' WHERE username = ?";
			
			try{
				\myApp\DB::executeSQL($sqlStr, ["s", $username]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			$sqlStr = "SELECT id FROM " . _TABLE_NAME2 .
					  " WHERE (";
			
			foreach ($form_data as $key => $value){
				$sqlStr .= "operation = ? OR ";
				$need_array[$key] = $key;
			}
			
			$sqlStr = substr($sqlStr, 0, -3);
			$sqlStr .= ") AND enable = '1'";
			
			try{
				$result = \myApp\DB::executeSQL($sqlStr, [str_repeat("s", count($need_array)), ...array_values($need_array)]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			if ($result -> num_rows > 0){
				$sqlStr = "UPDATE " . _TABLE_NAME3 . " SET need_flag = '1' WHERE username = ? AND (";
				while ($row = $result -> fetch_assoc()){
					$sqlStr .= "memo_id = '" . $row["id"] . "' OR ";
				}
				
				$sqlStr = substr($sqlStr, 0, -3);
				$sqlStr .= ")";
				
				try{
					$result = \myApp\DB::executeSQL($sqlStr, ["s", $username]);
				} catch (Exception $e){
					throw new Exception($e -> getMessage());
				}
			}
			
			return true;
		}
		
		/*
		* Function Name: delete_read_history
		* Parameter Type: String, String Array
		* Action: 刪除閱讀清單
		*/
		public static function delete_read_history(string $username): bool{
			$sqlStr = "DELETE FROM " . _TABLE_NAME3 . " WHERE username = ?";
			
			try{
				\myApp\DB::executeSQL($sqlStr, ["s", $username]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			$sqlStr = "UPDATE " . _TABLE_NAME2 . " SET create_username = NULL WHERE create_username = ?";
			try{
				\myApp\DB::executeSQL($sqlStr, ["s", $username]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			$sqlStr = "UPDATE " . _TABLE_NAME2 . " SET update_username = NULL WHERE update_username = ?";
			try{
				\myApp\DB::executeSQL($sqlStr, ["s", $username]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return true;
		}
		
		/*
		* Function Name: selectUploadHistory
		* Parameter Type: String file name, String operation name
		* Action: 查詢上傳紀錄(指定)
		*/
		public static function selectUploadHistory(string $file_name, string $operation): ?\mysqli_result{
			$sqlStr = "SELECT * " .
					  "FROM " . _TABLE_NAME2 . " " .
					  "WHERE memo_title = ? AND operation = ? AND enable = '1'";
			
			try{
				$results = \myApp\DB::executeSQL($sqlStr, ["ss", $file_name, $operation]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return $results;
		}
		
		/*
		* Function Name: selectUploadHistory
		* Parameter Type: N/A
		* Action: 查詢上傳紀錄(All)
		*/
		public static function selectUploadHistoryAll(): ?\mysqli_result{
			$sqlStr = "SELECT * FROM " . _TABLE_NAME2 . " WHERE enable = '1'";
			
			try{
				$conn = \myApp\DB::createSQLConnection();
				$results = $conn -> query($sqlStr);
				$conn -> close();
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return $results;
		}
		
		/*
		* Function Name: insertUploadHistory
		* Parameter Type: String create username, String update username, String create date, String update date, String operation, String memo title
		* Action: 建立上傳紀錄
		*/
		public static function insertUploadHistory(string $create_username, string $update_username, string $create_date, string $update_date, string $operation, string $file_name): bool{
			$sqlStr = "INSERT INTO " . _TABLE_NAME2 . " " .
					  "(create_username, update_username, create_date, update_date, operation, memo_title, enable) " .
					  "VALUES (?, ?, ?, ?, ?, ?, ?)";
			
			try{
				\myApp\DB::executeSQL($sqlStr, ["sssssss", $create_username, $update_username, $create_date, $update_date, $operation, $file_name, "1"]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return true;
		}
		
		/*
		* Function Name: updateUploadHistory
		* Parameter Type: String update username, String update date, String memo title
		* Action: 更新上傳紀錄
		*/
		public static function updateUploadHistory(string $update_username, string $update_date, string $memo_title): bool{
			$sqlStr = "UPDATE " . _TABLE_NAME2 . " " .
					  "SET update_username = ?, update_date = ? " . 
					  "WHERE memo_title = ?";
			
			try{
				\myApp\DB::executeSQL($sqlStr, ["sss", $update_username, $update_date, $file_name]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return true;
		}
		
		/*
		* Function Name: selectReadHistory
		* Parameter Type: String file name, String operation name
		* Action: 查詢閱讀紀錄
		*/
		public static function selectReadHistory(string $memo_id, string $username): ?\mysqli_result{
			$sqlStr = "SELECT * " . 
					  "FROM " . _TABLE_NAME3 . " " .
					  "WHERE memo_id = ? AND username = ?";
			
			try{
				$results = \myApp\DB::executeSQL($sqlStr, ["ss", $memo_id, $username]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return $results;
		}
		
		/*
		* Function Name: insertReadHistory
		* Parameter Type: String username, String memo_id, String need_flag
		* Action: 建立閱讀紀錄
		*/
		public static function insertReadHistory(string $username, string $memo_id, string $need_flag): bool{
			$sqlStr = "INSERT INTO " . _TABLE_NAME3 . " " .
					  "(username, memo_id, need_flag) " .
					  "VALUES (?, ?, ?)";
			
			try{
				\myApp\DB::executeSQL($sqlStr, ["sss", $username, $memo_id, $need_flag]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return true;
		}
		
		/*
		* Function Name: updateReadHistory
		* Parameter Type: String memo id, String username
		* Action: 更新上傳紀錄
		*/
		public static function updateReadHistory(string $need_flag, string $memo_id, string $username): bool{
			$sqlStr = "UPDATE " . _TABLE_NAME3 . " " .
					  "SET is_read = NULL, read_date = NULL, need_flag = ? " . 
					  "WHERE memo_id = ? AND username = ?";
			
			try{
				\myApp\DB::executeSQL($sqlStr, ["sss", $need_flag, $memo_id, $username]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return true;
		}
		
		/*
		* Function Name: getUserSummaryTable
		* Parameter Type: String username
		* Action: 取得總表
		*/
		public static function getUserSummaryTable(string $username, string $operation, string $memo_title): ?\mysqli_result{
			$sqlStr = "SELECT a.memo_title, a.update_date, a.operation, b.is_read, b.need_flag, b.read_date, b.memo_id, c.realname, d.operation_name_zh " . 
					  "FROM " . _TABLE_NAME2 . " a, " . _TABLE_NAME3 . " b, " .  _TABLE_NAME . " c, " . _TABLE_NAME5 . " d " .
					  "WHERE a.id = b.memo_id AND a.enable = 1 AND b.username = c.username AND a.operation = d.operation_name AND b.username LIKE ? AND a.operation LIKE ? AND a.memo_title LIKE ? " . 
					  "ORDER BY c.realname ASC, b.need_flag ASC, b.is_read DESC";
			
			try{
				$results = \myApp\DB::executeSQL($sqlStr, ["sss", $username, $operation, $memo_title]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return $results;
		}
		
		/*
		* Function Name: getUserNotReadSummaryTable
		* Parameter Type: String username
		* Action: 取得未閱讀總表
		*/
		public static function getUserNotReadSummaryTable(string $username): ?\mysqli_result{
			$sqlStr = "SELECT a.memo_title, a.update_date, a.operation, b.memo_id, b.is_read, b.username, b.read_date, c.realname, d.operation_name_zh " . 
					  "FROM " . _TABLE_NAME2 . " a, " . _TABLE_NAME3 . " b, " .  _TABLE_NAME . " c, " . _TABLE_NAME5 . " d " .
					  "WHERE a.id = b.memo_id AND a.enable = 1 AND b.username = c.username AND b.username LIKE ? AND b.is_read IS NULL AND b.need_flag = '1' AND a.operation = d.operation_name " . 
					  "ORDER BY c.realname ASC, b.need_flag ASC, b.is_read DESC";
			
			try{
				$results = \myApp\DB::executeSQL($sqlStr, ["s", $username]);
			} catch (Exception $e){
				throw new Exception($e -> getMessage());
			}
			
			return $results;
		}
	}
	
?>