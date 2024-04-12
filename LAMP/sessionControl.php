<?php
	/*
	* Modify: MAN50 HaoYu Liang#3555
	* File Name: sessionControl.php
	* Purpose: PHP Session 控制
	* Class: 1
	* Class Name: sessionControl
	* Function: 4
	*/
	
	//宣告命名空間
	namespace myApp;
	use \Exception;
	
	//引用設定文件
	require_once "config.php";
	
	//Class Start
	class sessionControl{
		
		/*
		* Function Name: createSessionData
		* Parameter Type: String Array
		* Action: 建立Session資料
		*/
		public static function crerteSessionData($sessionData): bool{
			try{
				foreach ($sessionData as $key => $value){
					$_SESSION[$key] = $value;
				}
			} catch (Exception $e){
				throw new Exception("Session Create fail");
			}
			
			return true;
		}
		
		/*
		* Function Name: deleteSessionData
		* Parameter Type: N/A
		* Action: 清除Session資料
		*/
		public static function deleteSessionData(): bool{
			
			try{
				//清空session
				session_unset();
			} catch (Exception $e){
				throw new Exception("Session Unset fail");
			}
			
			try{
				//刪除session
				session_destroy();
			} catch (Exception $e){
				throw new Exception("Session Destroy fail");
			}
			
			return true;
		}
		
		/*
		* Function Name: checkSessionTimeOut
		* Parameter Type: N/A
		* Action: 檢查認證是否到期
		*/
		public static function checkSessionTimeOut(): bool{
			//閒置時間限制
			$sessionLifeTime = 3600;
			
			if (time() - $_SESSION["lastActivity"] > $sessionLifeTime){
				self::deleteSessionData();  //呼叫刪除Session
				return true;
			}
			
			return false;
		}
		
		/*
		* Function Name: resetSessionActivate
		* Parameter Type: N/A
		* Action: 重設活動時間
		*/
		public static function resetSessionActivate(): bool{
			try{
				$_SESSION["lastActivity"] = time();
			} catch (Exception $e){
				throw new Exception("Reset Activity Time Fail");
			}
			
			return true;
		}
	}
	
	
?>