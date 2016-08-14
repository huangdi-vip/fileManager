<?php
    date_default_timezone_set('Asia/Shanghai');
    
    include_once 'dir.class.php';
    // 获取当前操作的文件夹
    $dir_path = $_GET['dir_path'];
    
    $file = new dir($dir_path);

    if ($_GET['action'] == 'rename') {
    	$msg = $file->rename($_GET['old_name'], $_GET['new_name']);
        echo $msg;
        exit();
    }

    if ($_GET['action'] == 'show') {
    	$str = $file->getFileContent($_GET['path']);
    	if ($str) {
    		echo $str;
    	} else {
    		echo "";
    	}
    	exit();
    }

    if ($_POST['action'] == 'edit') {
    	$content = $_POST['content'];
    	$res = $file->editFile($_POST['path'], $content);
    	if ($res) {
    		echo "修改成功！";
    	} else {
    		echo "修改失败！";
    	}
    	exit();
    }

    if ($_GET['action'] == 'delete') {
    	$res = $file->delete($_GET['filename']);
    	if ($res) {
    		echo "删除成功！";
    	} else {
    		echo "删除失败！";
    	}
    }
?>