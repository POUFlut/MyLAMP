<?php
	error_reporting(E_ALL);
    ini_set('display_errors',1);
	
	$content = scandir("uploads/cam");
	
	foreach($content as $item){
		if ($item !== "." && $item !== ".."){
			unlink ("uploads/cam/" . $item);
			//rmdir ("uploads/" . $item);
		}
	}
	
	//rmdir ("uploads/cam");
	//rmdir ("uploads/dfm");
?>