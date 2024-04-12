<?php
	
	mb_internal_encoding("utf-8");  //宣告編碼
	session_start();  //啟用SESSION
	date_default_timezone_set('Asia/Taipei');  //設定時區
	
	//SQL基本資料
	define('DB_SERVER', '');
	define('DB_USERNAME', '');
	define('DB_PASSWORD', '');
	define('DB_NAME', '');
	
	//開啟偵錯
	//error_reporting(E_ALL);
	//ini_set('display_errors',1);

?>	