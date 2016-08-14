<?php
    include_once 'dir.class.php';

    date_default_timezone_set('Asia/Shanghai');

    // 获取当前操作的文件夹
    $dir_path = $_GET['dir_path'];
    
    $file = new dir($dir_path);

    // 是否上传文件
    if ($_FILES['file']['name']) {
        $file->upload($_FILES['file']);
    }

    // 列出目录文件（默认为项目跟目录）
    $data = $file->getAllFiles();
    // 上一级目录
    $prev_dir = $file->getPrevDir();
?>
<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
    <title>文件管理</title>

    <!-- Bootstrap css-->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/viewer.min.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.min.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
    <style>
    	.list {
    		list-style: none;
    		margin: 0;
    		padding: 0;
    	}
    	.list li {
    		padding: 3px;
    	}
        .list li a {
            color: #000;
        }
    	.table tr th,td {
    		text-align: center;
    	}
    	.table tr td input {
    		width: 100%;
    	}
        textarea {
            resize: none;
        }
        img {
            width: 40px;
            height: 34px;
            border-radius: 5px;
        }
    </style>
  </head>
  <body>
    <div class="container-fluid">
    	<!-- file lists -->
    	<div class="col-md-2 col-lg-2 visible-lg visible-md well">
    		<ul class="list">
	    		<li><i class="icon-reply"></i> <a href="index.php?dir_path=<?=$prev_dir;?>">返回上一级</a></li>
                <?php
                if (is_array($data['dirs'])) {
                foreach ($data['dirs'] as $v) {
                ?>
                <li><i class="icon-folder-close-alt"></i> <a href="index.php?dir_path=<?=$v['url'];?>"><?=$v['name'];?></a> <span class="badge"><?=$v['count'];?></span></li>
                <?php
                }
                }
                ?>
                <?php
                if (is_array($data['files'])) {
                foreach ($data['files'] as $v) {
                ?>
                <li><i class="icon-file"></i> <?=$v['name'];?> </li>
                <?php
                }
                }
                ?>
                <li><i class="icon-cloud-upload"></i> <a href="#" data-toggle="modal" data-target="#uploadModal">上传文件</a></li>
    		</ul>
    	</div>
    	<!-- file lists end -->

        <!-- file table -->
    	<div class="col-md-10 col-lg-10 visible-lg visible-md" id="img_block">
    		<table class="table">
    			<tr>
    				<th>#</th>
    				<th>名称</th>
    				<th>大小</th>
    				<th>创建日期</th>
    				<th>修改日期</th>
                    <th>上次访问</th>
    				<th>类型</th>
    				<th>权限</th>
                    <th>查看</th>
                    <th>编辑</th>
    				<th>下载</th>
    				<th>删除</th>
    				<th>重命名</th>
    			</tr>
                <?php
                $num = 1;
                if (is_array($data['dirs'])) {
                foreach ($data['dirs'] as $k => $v) {
                ?>
    			<tr>
    				<td><?=$num;?></td>
    				<td><a href="index.php?dir_path=<?=$v['url'];?>"><?=$v['name'];?></a></td>
    				<td><?=$v['size'];?></td>
    				<td><?=$v['create_time'];?></td>
                    <td><?=$v['alert_time'];?></td>
                    <td><?=$v['last_time'];?></td>
                    <td><?=$v['type'];?></td>
                    <td><?=$v['readable'];?> | <?=$v['writable'];?></td>
                    <td><button class="btn" type="btn" onclick="opendir('<?=$v['url'];?>');"><i class="icon-eye-open"></i></button></td>
                    <td><button class="btn" type="btn" onclick="edit();" disabled><i class="icon-edit"></i></button></td>
                    <td><button class="btn" type="btn" disabled><i class="icon-cloud-download"></i></button></td>
                    <td><button class="btn" type="btn" onclick="del();" disabled><i class="icon-trash"></i></button></td>
    				<td><input type="text" class="form-control" value="<?=$v['name'];?>" onchange="rename(this, '<?=$_GET['dir_path'];?>', '<?=$v['name'];?>');"></td>
    			</tr>
                <?php
                $num++;
                }
                }
                ?>
                <?php
                if (is_array($data['files'])) {
                foreach ($data['files'] as $k => $v) {
                ?>
                <tr>
                    <td><?=$num;?></td>
                    <td><?=$v['name'];?></td>
                    <td><?=$v['size'];?></td>
                    <td><?=$v['create_time'];?></td>
                    <td><?=$v['alert_time'];?></td>
                    <td><?=$v['last_time'];?></td>
                    <td><?=$v['type'];?></td>
                    <td><?=$v['readable'];?> | <?=$v['writable'];?></td>
                    <?php
                    $arr = explode('.', basename($v['name']));
                    $file_type = $arr[count($arr)-1];
                    $img_type = array('jpg', 'jpeg', 'png', 'gif');
                    if (in_array($file_type, $img_type)) {
                    ?>
                    <td><img data-original="<?=$v['url'];?>" src="<?=$v['url'];?>"></td>
                    <td><button class="btn" type="btn" onclick="show('<?=$v['url'];?>', 'edit');" disabled><i class="icon-edit"></i></button></td>
                    <?php
                    } else {
                    ?>
                    <td><button class="btn" type="btn" onclick="show('<?=$v['url'];?>', 'show');"><i class="icon-eye-open"></i></button></td>
                    <td><button class="btn" type="btn" onclick="show('<?=$v['url'];?>', 'edit');"><i class="icon-edit"></i></button></td>
                    <?php
                    }
                    ?>
                    
                    <td><a href="download.php?dir_path=<?=$_GET['dir_path'];?>&filename=<?=$v['name'];?>" class="btn btn-default" role="button" target="_black"><i class="icon-cloud-download"></i></a></td>
                    <td><button class="btn" type="btn" onclick="del('<?=$v['name'];?>', '<?=$_GET['dir_path'];?>');"><i class="icon-trash"></i></button></td>
                    <td><input type="text" class="form-control" value="<?=$v['name'];?>" onchange="rename(this, '<?=$_GET['dir_path'];?>', '<?=$v['name'];?>');"></td>
                </tr>
                <?php
                $num++;
                }
                }
                ?>
    		</table>
    	</div>
        <!-- file table end -->
    	<div class="error-text text-center visible-sm visible-xs">
			<h1 class="text-danger">屏幕过小，请使用大屏幕设备访问！</h1>
    	</div>
    </div>
    <!-- modal -->
    <div class="modal fade bs-example-modal-lg" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">查看/编辑文件</h4>
                </div>
                <div class="modal-body" id="code_area">
                    <input type="hidden" id="hidden_url" value="">
                    <textarea id="text" cols="30" rows="30" class="form-control"></textarea>
                </div>
                <div class="modal-footer" id="footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    <button type="button" class="btn btn-primary" id="save" onclick="saveChange();">保存</button>
                </div>
            </div>
        </div>
    </div>
    <!-- modal end -->

    <!-- modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="post" class="form-horizontal" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="uploadModalLabel">上传文件</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label col-sm-2">选择文件</label>
                        <div class="col-sm-10">
                            <input type="file" class="form-control" name="file">
                        </div>
                    </div>
                </div>
                <div class="modal-footer"">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                    <button type="submit" class="btn btn-primary">上传</button>
                </div>
                </form>
            </div>
        </div>
    </div>
    <!-- modal end -->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery.js"></script>

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/viewer.min.js"></script>
    <script>
        // 打开文件夹
        function opendir(url) {
            location.href="index.php?dir_path="+url;
        }
        // 重命名文件或文件夹
    	function rename(obj, path, oldName) {
            if (obj.value != "") {
                if (confirm("确定要修改吗？")) {
                    $.ajax({
                        url: 'ajaxReturn.php',
                        type: 'GET',
                        data: {dir_path: path, old_name: oldName, new_name: obj.value, action: 'rename'},
                    })
                    .done(function(msg) {
                        alert(msg);
                        location.replace(location.href);
                    });
                    
                }
            } else {
                alert("名字不能为空！");
            }
    	}
        // 查看文件内容
        function show(url, action) {

            if (action == "show") {
                var textarea = document.getElementById('text');
                textarea.disabled = true;
                var saveButton = document.getElementById('save');
                saveButton.disabled = true;
            } else if (action == "edit") {
                var textarea = document.getElementById('text');
                textarea.disabled = false;
                var saveButton = document.getElementById('save');
                saveButton.disabled = false;
                var hiddenUrl = document.getElementById('hidden_url');
                hiddenUrl.value = url;
                if (hiddenUrl.value != url) {
                    return false;
                }
            }
            // 请求文件内容
            $.ajax({
                url: 'ajaxReturn.php',
                type: 'GET',
                data: {action: 'show', path: url},
            })
            .done(function(msg) {
                if (msg == "") {
                    alert('读取失败！');
                } else {
                    textarea.value = msg;
                    $("#myModal").modal('show');
                }
            })
            .fail(function() {
                alert('读取失败！');
            });
        }

        function saveChange() {
            var textarea = document.getElementById('text');
            var hiddenUrl = document.getElementById('hidden_url');
            $.ajax({
                url: 'ajaxReturn.php',
                type: 'POST',
                data: {content: textarea.value, path: hiddenUrl.value, action: "edit"},
            })
            .done(function(msg) {
                $("#myModal").modal('hide');
                alert(msg);
            })
            .fail(function() {
                alert("修改失败！");
            });
        }
        function del(name, path) {
            $.ajax({
                url: 'ajaxReturn.php',
                type: 'GET',
                data: {dir_path: path, filename: name, action: 'delete'},
            })
            .done(function(msg) {
                alert(msg);
                location.replace(location.href);
            })
            .fail(function() {
                alert('操作失败！');
            });
        }
        window.onload = function() {
            var viewer = new Viewer(document.getElementById('img_block'), {
                url: 'data-original'
            });
        }
    </script>
  </body>
</html>