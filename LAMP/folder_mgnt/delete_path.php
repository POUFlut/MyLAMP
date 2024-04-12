<?php
	//引用設定文件
	require_once "../config.php";  
	require_once "../sessionControl.php";  
	require_once "../sql.php";
	require_once "../authenticator.php";
	
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
	if ($_SESSION["permission"] < 4){
		echo '權限不足';
		exit();
	}
	
	$jsonData = json_decode(file_get_contents('php://input'));
	$folder_name = trim($jsonData -> pfolder_name ?? '');
	$folder_path = trim($jsonData -> pfolder_path ?? '');
	$path_account = trim($jsonData -> ppath_account ?? '');
	if (empty($folder_name) || empty(folder_path)){
		echo '資料缺失';
		exit();
	}
	
	try{
		\myApp\DB::deleteFolderDetail(urldecode($folder_name), urldecode($folder_path), urldecode($path_account));
		echo 1;
	} catch (Exception $e){
		echo $e -> getMessage();
	}
	
	exit();
?>