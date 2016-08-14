<?php
	/**
	* 对文件进行操作
	* @author 周启航
	*/
	class dir {

		/**
		* string:当前操作的文件夹（相对于项目文件夹）
		*/
		private $handle_dir = null;

		/**
		* string:根文件夹document_root
		*/
		private $root_dir = null;

		/**
		* string:当前操作文件夹路径
		*/
		private $dir = null;

		/**
		* array:存储文件和文件夹信息
		*/
		private $data = null;
		
		/**
		* 构造方法：完成路径的初始化和文件数据的初始化
		* @param $dir string 当前操作的路径
		*/
		function dir($dir=null) {
			$this->root_dir = dirname($_SERVER['SCRIPT_FILENAME']).'/';
			if ($dir && is_dir($dir)) {
				if (stripos($dir, '.') !== 0) {
					$this->handle_dir = $dir.'/';;
				} else {
					$this->handle_dir = '';
				}
			} else {
				$this->handle_dir = '';
			}
			$this->dir = $this->root_dir.$this->handle_dir;
			$this->setFiles();
			$this->setDirCount();
			$this->setFilesInfo();
			$this->setDirsInfo();

			// echo $this->formatSize(5199);
		}

		/**
		* 获取文件信息
		* @return array 当前路径下的文件和文件夹信息
		*/
		public function getAllFiles() {
			return $this->data;
		}

		/**
		* 获取上一级目录的path
		* @return string 上一级目录的path
		*/
		public function getPrevDir() {
			$arr = explode('/', $this->handle_dir);
			$url = '';
			for ($i=0; $i < count($arr)-2; $i++) { 
				if ($i==0) {
					$url .= $arr[$i];
				} else {
					$url .= '/'.$arr[$i];
				}
			}
			return $url;
		}


		/**
		* 遍历当前目录，以获取当前目录下的文件夹和文件
		*/
		private function setFiles() {
			if (is_dir($this->dir)) {
				$handle = opendir($this->dir);
				while (($item = readdir($handle)) !== false) {
					if (stripos($item, '.') !== 0) {
						if (is_dir($this->dir.'/'.$item)) {
							$file['dirs'][] = array('name'=>$item,'url'=>$this->handle_dir.$item);
						} else if (is_file($this->dir.'/'.$item)) {
							// $file['files'][]['name'] = $item;
							$file['files'][] = array('name'=>$item,'url'=>$this->handle_dir.$item);
						}
					}
				}
				closedir($handle);
				$this->data = $file;
			}
		}
		/**
		* 获取文件夹的详细信息
		*/
		private function setDirsInfo() {
			if (is_array($this->data['dirs'])) {
				foreach ($this->data['dirs'] as $k => $v) {
					// 创建时间
					$this->data['dirs'][$k]['create_time'] = date('Y-m-d H:i:s', filectime($this->dir.$v['name']));
					// 修改时间
					$this->data['dirs'][$k]['alert_time'] = date('Y-m-d H:i:s', filemtime($this->dir.$v['name']));
					// 上次访问时间
					$this->data['dirs'][$k]['last_time'] = date('Y-m-d H:i:s', fileatime($this->dir.$v['name']));
					// 文件类型
					$this->data['dirs'][$k]['type'] = filetype($this->dir.$v['name'])=='file' ? '文件' : '目录';
					// 读权限
					$this->data['dirs'][$k]['readable'] = is_readable($this->dir.$v['name']) ? '可读' : '不可读';
					// 写权限
					$this->data['dirs'][$k]['writable'] = is_writable($this->dir.$v['name']) ? '可写' : '不可写';
					// 文件大小
					$this->data['dirs'][$k]['size'] = $this->formatSize($this->getSizeOfFileOrDir($this->dir.$v['name']));
				}
			}
		}

		/**
		* 获取文件的详细信息
		*/
		private function setFilesInfo() {
			if (is_array($this->data['files'])) {
				foreach ($this->data['files'] as $k => $v) {
					// 创建时间
					$this->data['files'][$k]['create_time'] = date('Y-m-d H:i:s', filectime($this->dir.$v['name']));
					// 修改时间
					$this->data['files'][$k]['alert_time'] = date('Y-m-d H:i:s', filemtime($this->dir.$v['name']));
					// 上次访问时间
					$this->data['files'][$k]['last_time'] = date('Y-m-d H:i:s', fileatime($this->dir.$v['name']));
					// 文件类型
					$this->data['files'][$k]['type'] = filetype($this->dir.$v['name'])=='file' ? '文件' : '目录';
					// 读权限
					$this->data['files'][$k]['readable'] = is_readable($this->dir.$v['name']) ? '可读' : '不可读';
					// 写权限
					$this->data['files'][$k]['writable'] = is_writable($this->dir.$v['name']) ? '可写' : '不可写';
					// 文件大小
					$this->data['files'][$k]['size'] = $this->formatSize($this->getSizeOfFileOrDir($this->dir.$v['name']));

				}
			}
		}

		/**
		* 获取文件或文件夹的大小
		* @param $path 文件或文件夹的路径
		* @return number 文件或文件夹的大小
		*/
		private function getSizeOfFileOrDir($path) {
			if (is_file($path)) {
				return filesize($path);
			}

			if (is_dir($path)) {
				$num;
				$handle = opendir($path);
				while (($item = readdir($handle)) !== false) {
					if (stripos($item, '.') !== 0) {
						if (is_file($path.'/'.$item)) {
							$num += filesize($path.'/'.$item);
						} else if (is_dir($path.'/'.$item)) {
							$num += $this->getSizeOfFileOrDir($path.'/'.$item);
						}
					}
				}
				return $num;
			}
		}

		/**
		* 将B转换成KB, MB, TB
		* @param $size 文件或文件夹大小（单位为B）
		* @return number 转换后的文件或文件夹大小
		*/
		private function formatSize($size) {
			$nuits = array('B', 'KB', 'MB', 'TB');
			$nuit = 0;
			$size = $size ? $size : 0;
			if ($size >= 1024*1024*1024) {
				$size = round($size/(1024*1024*1024), 2);
				$nuit = 3;
			} else if ($size >= 1024*1024) {
				$size = round($size/(1024*1024), 2);
				$nuit = 2;
			} else if ($size >=1024) {
				// KB
				$size = round($size/1024, 2);
				$nuit = 1;
			}
			return $size.$nuits[$nuit];
		}

		/**
		* 获取文件夹中包含的项目数量
		*/
		private function setDirCount() {
			if (is_array($this->data['dirs'])) {
				foreach ($this->data['dirs'] as $k => $v) {
					$handle = opendir($this->dir.'/'.$v['name']);
					$count = 0;
					while (($item = readdir($handle)) !== false) {
						if (stripos($item, '.') !== 0) {
							$count++;
						}
					}
					closedir($handle);
					$this->data['dirs'][$k]['count'] = $count;
				}
			}
		}

		/**
		* 重命名文件
		* @param $oldName 文件原名
		* @param $name 更改后的文件名
		* @return string 操作结果
		*/
		public function rename($oldName, $name) {
			// 检查两者是否相同
			if ($oldName == $name) {
				return "请输入不同的名字！";
			}

			// 检查文件名是否合法，或者是否已经存在此文件
			if (($str = $this->checkFileName($name)) !== true) {
				return $str;
			}

			// 重命名文件
			if (rename($this->dir.$oldName, $this->dir.$name)) {
				return "文件重命名成功！";
			} else {
				return "文件重命名失败！";
			}
		}

		/**
		* 检查文件名是否合法
		* @param $name 要检查的文件名
		* @return string or bool string:非法原因,bool:检查通过
		*/
		private function checkFileName($name) {
			// 查看文件名是否合法
			if (preg_match("/[\/,\*,<>,\?\|]/", $name)) {
				return "文件名非法！";
			}

			// 查看是否已经存在相应文件
			if (file_exists($this->dir.$name)) {
				return "已存在此文件！";
			}

			return true;
		}

		/**
		* 获取文件内容
		* @param $path 文件相对于项目文件夹的路径
		* @return string or bool string:文件内容,bool:操作失败
		*/
		public function getFileContent($path) {
			$this->handle_dir = $path;
			$this->dir = $this->root_dir.$this->handle_dir;
			// 检查文件是否存在
			if (!is_file($this->dir)) {
				return false;
			}

			// 检查是否存在读权限
			if (!is_readable($this->dir)) {
				return false;
			}

			// 读文件
			$str = file_get_contents($this->dir);
			if ($str) {
				return $str;
			} else {
				return false;
			}
		}

		/**
		* 编辑文件内容
		* @param $path 文件相对于项目文件夹的路径
		* @param $data 更改后的文件内容
		* @return bool 更改成功返回true,更改失败返回false
		*/
		public function editFile($path, $data) {
			$this->handle_dir = $path;
			$this->dir = $this->root_dir.$this->handle_dir;
			// 检查文件是否存在
			if (!is_file($this->dir)) {
				return false;
			}

			// 检查是否存在写权限
			if (!is_writable($this->dir)) {
				return false;
			}

			//写文件
			$res = file_put_contents($this->dir, $data);
			if ($res) {
				return true;
			} else {
				return false;
			}
		}

		/**
		* 删除文件
		* @param $filename 要删除的文件名
		* @return bool 删除成功返回true,删除失败返回false
		*/
		Public function delete ($filename) {
			return unlink($this->dir.$filename);
		}

		/**
		* 编辑文件内容
		* @param $file 上传文件数组$_FILES['file']
		* @return bool 上传成功返回true,上传失败返回false
		*/
		public function upload($file=null) {
			$uploaddir = $this->dir;
			//检查是否存在相同的文件
			$filename = basename($file['name']);
			$i;
			while (is_file($this->dir.$i.$filename)) {
				$i++;
			}
			$filename = $i.$filename;
			$uploadfile = $uploaddir.$filename;

			if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
				return true;
			} else {
				return false;
			}
		}
	}
?>