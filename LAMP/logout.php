<?php
	//引用設定文件
	require_once "config.php";
	require_once "sessionControl.php";
	
	try{
		\myApp\sessionControl::deleteSessionData();
	} catch (Exception $e){
		echo $e -> getMessage();
		exit();
	}
	 
	//導向首頁
	header("location: index.php");
?>