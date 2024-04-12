<?php
	if ($_SERVER['REQUEST_METHOD'] === 'POST'){
		session_start();
		if (empty($_POST)){
			echo "<script>alert('未傳送輸入資料!請通知工程師!')</script>";
			echo "未傳送輸入資料!請通知工程師!";
			exit();
		}
		
		//製作暫存檔
		if (!isset($_POST["filename"])){
			echo "<script>alert('未傳送檔名!請通知工程師!')</script>";
			echo "未傳送檔名!請通知工程師!";
			exit();
		}
		$filename = '/tmp/webserver/' . $_POST["filename"];
		$content = "";
		
		foreach($_POST as $key => $value){
			$content .= $key . ":" . $value . "\n";
		}
		
		//建立暫存檔案
		$file = fopen($filename,"w");
		
		//寫入暫存檔案
		fwrite($file, $content);
		fclose($file);  //關閉檔案
		
		$error = error_get_last();
		if ($error !== null){
			echo "<script>alert('儲存失敗!請重新執行程式!')</script>";
			echo "儲存失敗!請重新執行程式!";
		} else {
			echo "<script>alert('儲存成功!請關閉瀏覽器!')</script>";
			echo "儲存成功!請關閉瀏覽器!";
		}
	}
?>