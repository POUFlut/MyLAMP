<?php
	//引用設定文件
	require_once "config.php";  
	require_once "sessionControl.php";  
	require_once "sql.php";
	require_once "authenticator.php";
	require_once "memo/update_read_function.php";
	
	$user = new \myApp\User();
	
	//確認是否已登入
	if (!(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)){
		header("location: ../login.php");
		exit();
	}
	
	if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
		//檢查認證是否到期
		try{
			if ($user -> loginTimeOut()){
				echo '登入逾時';
				exit();
			}
		} catch (Exception $e){
			echo '未知錯誤';
			exit();
		}
	}
	
	//確認權限
	if ($_SESSION["permission"] < 5){
		echo '權限不足';
		exit();
	}
	
	$jsonData = json_decode(file_get_contents('php://input'));
	$username = trim($jsonData -> pusername ?? '');
	if (empty($username)){
		echo '帳號錯誤';
		exit();
	}
	
	try{
		\myApp\MEMO::delete_read_history($username);
		$user -> deleteUser($username);
		echo '1';
	} catch (Exception $e){
		echo $e -> getMessage();
	}
	
	exit();
?>