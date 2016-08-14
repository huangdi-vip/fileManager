<?php
	date_default_timezone_set('Asia/Shanghai');
	include_once 'dir.class.php';

	 // 获取当前操作的文件夹
    $dir_path = $_GET['dir_path'];
    
    $file = new dir($dir_path);

    if (!$_GET['filename']) {
    	echo "文件不存在";
    	exit();
    }

    $file->download($_GET['filename']);
?>