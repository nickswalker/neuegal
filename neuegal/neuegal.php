<?php
ini_set("gd.jpeg_ignore_warning", 1);

class NeueGal
{
	var $settings;
	var $vars;
	
	function startTimer() {
		$temp_time = microtime();
		$temp_time = explode(" ", $temp_time);
		$temp_time = $temp_time[1] + $temp_time[0];
		$this->vars['start_time'] = $temp_time;
	}
	
	function endTimer() {
		$temp_time = microtime();
		$temp_time = explode(" ", $temp_time);
		$temp_time = $temp_time[1] + $temp_time[0];
		$this->vars['end_time'] = $temp_time;
		$this->vars['total_time'] = ($this->vars['end_time'] - $this->vars['start_time']);
	}

	function loadSettings() {
	

			if (!is_file('neuegal/settings.php'))
			{
			} else {
				require('neuegal/settings.php');
			}
			

			if (!is_file('neuegal/themes/' . $this->settings['general']['theme'] . '/settings.php'))
			{
				return false;
			} else {
				
				require('neuegal/themes/' . $this->settings['general']['theme'] . '/settings.php');
				$this->setThumbSize();
				return true;
			}



    }

	function loadVars() {
		$this->vars['dir_local'] = realpath(dirname($_SERVER['SCRIPT_FILENAME'])); //								/var/www/pictures
		$this->vars['dir_req'] = $this->cleanPath($_SERVER['QUERY_STRING']); //										photo/landscape
		$this->vars['dir_req_parent'] = dirname($this->vars['dir_req']); //											photo
		$this->vars['dir_root'] = $this->pathInfo($_SERVER['SCRIPT_NAME'], 'dir_path'); //						/pictures

		$this->vars['dir_root_cache'] = $this->settings['advanced']['cache_folder'];//								neuegal/cache
		$this->vars['dir_cache'] = $this->fixPath($this->vars['dir_root_cache']) . $this->vars['dir_req'];//		photo/landscape/cache
		
		if ($this->settings['advanced']['cyrillic_support'] == true) {
			$this->vars['dir_req'] = rawurldecode($this->vars['dir_req']);
		}
		
		$temp_current_req = explode('/', $this->vars['dir_req']);		
		$this->vars['dir_req_current'] = $temp_current_req[count($temp_current_req) - 1];//							landscape
		
		if($this->vars['dir_req'] == $this->vars['dir_req_current'])	{
			$this->vars['dir_parent'] = null;
		}
		else{
			$this->vars['dir_parent'] = $this->getSubDir($this->vars['dir_req'], $this->vars['dir_req_current']);
		}
		
		if(is_file($this->vars['dir_local'].'/'.$this->vars['dir_req'] . $item.'/description.txt')){
			$this->vars['description'] = file_get_contents($this->vars['dir_local'].'/'.$this->vars['dir_req'] . $item.'/description.txt');
		}
		
		
		if ($this->vars['dir_req_parent'] == '.')
		{
			$this->vars['dir_req_parent'] = '';
		}
	}
	function getSubDir($dir, $sub)
	{
	    $temp_array = array_slice(array_diff(explode('/', $dir), explode('/', $sub)), 0, 1);
	    return $temp_array[0];
	}
	function loadLists() {
		$temp_file = file_get_contents('neuegal/file_blacklist.txt');
		$this->vars['file_blacklist'] = explode(",", $temp_file);
		
		$temp_folder = file_get_contents('neuegal/folder_blacklist.txt');
		$this->vars['folder_blacklist'] = explode(",", $temp_folder);
		
		$temp_type = file_get_contents('neuegal/file_types.txt');
		$this->vars['file_types'] = explode(",", $temp_type);
	}
	
	function checkList($item, $list) {		
		foreach($list as $list_item)
		{
			if (strtolower($list_item) == strtolower($item))
			{
				return true;
			}
		}
		
		return false;
	}
	//!Cleanup and Formatting Helpers
	function cleanFileName($dirty_name)	{
		return substr($dirty_name, 0 , -4);
	}
	function cleanPath($path) {
		$path = str_replace('-', ' ', $path);
	
		if (substr($path, 0, 1) == '/')
		{
			$path = substr($path, 1);
		}
		
		if (substr($path, -1, 1) == '/')
		{
			$path = substr($path, 0, -1);
		}
		
		return $path;
	}
	
	function checkExploit($path, $file = false) {
		$real_base = realpath($this->vars['dir_local']);
		
		$path = (DIRECTORY_SEPARATOR === '\\') ? str_replace('/', '\\', $path) : str_replace('\\', '/', $path);
		
		$var_path = $this->vars['dir_local'] . $path;
		$real_var_path = realpath($var_path);
		
		/*echo "Requested: " . $path . "<br>";
		echo "Base real path: " . $real_base . "<br>";
		echo "Requested real path: " . $real_var_path . "<br>";
		echo "Therefore " . $real_base . $path . " should equal " . $real_var_path . "<br>";*/
		
		if ($real_var_path === false || ($real_base . $path) !== $real_var_path) {
			return false;
		} else {
			return true;
		}
	}
	
	function escapeString($string, $action = "add") {
		if ($action == "add") {
			if (get_magic_quotes_gpc()) {
				return $string;
			} else {
				return addslashes($string);
			}
		} elseif ($action == "strip") {
			return stripslashes($string);
		}
	}
	
	function pathInfo($path, $info) {
		$temp = pathinfo($path);
		
		if ($info == 'dir_path')
		{
			return $temp['dirname'];
		} else if ($info == 'full_file_name') {
			return $temp['basename'];
		} else if ($info == 'file_ext') {
			return $temp['extension'];
		} else if ($info == 'file_name') {
			return $temp['filename'];
		}
	}
	
	function fixPath($path) {
		if ($path == '') {
			return '';
		} else if (substr($path, -1) !== '/') {
			return $path . '/';
		} else {
			return $path;
		}
	}
	
	function getDir($dir) {		
		if ($dir_data = $this->getDirData($dir, 'both', true)) {
			if (count($dir_data['file']) > 0) { $this->vars['file_list'] = $dir_data['file']; }
			if (count($dir_data['dir']) > 0) { $this->vars['folder_list'] = $dir_data['dir']; }
	
			$this->cacheDir($dir);
		
			
			return true;
		} else {
			return false;
		}
	}
	
	function getDirData($dir, $type = 'both', $cached = false, $forced_cache = false) {
		//$full_dir: Root folder combined with requested folder with trailing /
		//$dir: Requested folder
		//$dh: Directory Handler
		//$item: File/Dir data during directory scan
		//$fd: Found Directories array
		//$ff: Found Files array
		
		$cache_folder = $this->fixPath($this->vars['dir_root_cache']);
		$dir = $this->fixPath($dir);
		$full_dir = $this->fixPath($this->vars['dir_local']) . $dir;
		$output = array();
		
		if (is_dir($full_dir)) {
			if ($cached == true && $this->settings['advanced']['use_file_cache'] == true && is_file($cache_folder . $dir . 'cache.xml')) {
				if (((time() - filemtime($cache_folder . $dir . 'cache.xml')) < $this->settings['advanced']['expire_file_cache']) || $forced_cache == true) {
					$xml = new SimpleXMLElement(file_get_contents($cache_folder . $dir . 'cache.xml'));
	
					$x = 0;
					
					if (isset($xml->directories))
					{
						foreach($xml->directories->dir as $dirs)
						{
							$fd[$x]['full_path'] = (string)$dirs->path;
							$fd[$x]['dir'] = (string)$dirs->dirname;
							
							$x++;
						}
					}
					
					$x = 0;
					
					if (isset($xml->files))
					{
						foreach($xml->files->file as $files)
						{
							$ff[$x]['full_path'] = (string)$files->path;
							$ff[$x]['file'] = (string)$files->filename;
							$ff[$x]['data'][0] = (integer)$files->data->width;
							$ff[$x]['data'][1] = (integer)$files->data->height;
							$ff[$x]['data'][2] = (integer)$files->data->imagetype;
							$ff[$x]['data'][3] = (string)$files->data->sizetext;
							
							$x++;
						}
					}
				} else {
					return false;
				}
			} else {
				if ($forced_cache == false) {
					if ($dh = opendir($full_dir)) {
						while (($item = readdir($dh)) !== false) {
							if (filetype($full_dir . $item) == 'dir' && $type != 'file' && $this->checkList($item, $this->vars['folder_blacklist']) == false)
							{
							$temp_description= '';
							if(file_exists($full_dir . 'description.txt')){
								$temp_description = file_get_contents($full_dir . 'description.txt' );
							}
								$fd[] = array(
									'full_path'=>$dir . $item,
									'dir'=>$item,
									'description'=> $temp_description
								);
								
								sort($fd);
							} else if (filetype($full_dir . $item) == 'file' && $type != 'dir' && $this->checkList($item, $this->vars['file_blacklist']) == false && $this->checkList($this->pathInfo($item, 'file_ext'), $this->vars['file_types']) == true) {
							$temp_description= '';
							if(file_exists($full_dir . basename($item,'.jpg').'.txt' )){
								
								$temp_description = file_get_contents($full_dir . basename($item,'.jpg').'.txt' );
							}
								$ff[] = array(
									'full_path'=>$dir . $item,
									'file'=>$item,
									'data'=>getimagesize($full_dir . $item),
									'description'=>$temp_description
								);
								
								sort($ff);
							}
						}
						closedir($dh);
					} else {
						return false;
					}
				} else {
					return false;
				}
			}
			
			if ($type == 'both') {
				$output['file'] = $ff;
				$output['dir'] = $fd;
				
				return $output;
			} else if ($type == 'file') {
				$output = $ff;
				
				return $output;
			} else if ($type == 'dir') {
				$output = $fd;
				
				return $output;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	function cacheDir($dir) {
		$cache_folder = $this->fixPath($this->vars['dir_root_cache']);
		$cache_exists = false;
		
		if (count($this->vars['folder_list']) > 0 or count($this->vars['file_list']) > 0) {
			$cache_exists = $this->genCacheDir($dir);
			
			if ($cache_exists == true)
			{
				$xmlstr = "<?xml version='1.0' ?>\n<cache></cache>";
				$xml = new SimpleXMLElement($xmlstr);
				
				if (isset($this->vars['folder_list']))
				{
					$xml_dir = $xml->addChild('directories');
					
					foreach($this->vars['folder_list'] as $dirs)
					{
						$xml_dirs_data = $xml_dir->addChild('dir');
						$xml_dirs_data->addChild('path', $dirs['full_path']);
						$xml_dirs_data->addChild('dirname', $dirs['dir']);
						if (isset($dirs['description'])) {$xml_dirs_data->addChild('description', $dirs['description']);}
					}
				}
				
				if (isset($this->vars['file_list']))
				{
					$xml_files = $xml->addChild('files');
					
					foreach($this->vars['file_list'] as $files)
					{
						$xml_files_data = $xml_files->addChild('file');
						$xml_files_data->addChild('path', $files['full_path']);
						$xml_files_data->addChild('filename', $files['file']);
						
						$xml_data = $xml_files_data->addChild('data');
						$xml_data->addChild('width', $files['data'][0]);
						$xml_data->addChild('height', $files['data'][1]);
						$xml_data->addChild('imagetype', $files['data'][2]);
						$xml_data->addChild('sizetext', $files['data'][3]);
						if (isset($files['description'])) { $xml_data->addChild('description', $files['description']); }
		
					}
				}
				
				$xml->asXML($cache_folder . $dir . 'cache.xml');
				return true;
				
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	function genCacheDir($dir) {
		$cache_folder = $this->fixPath($this->vars['dir_root_cache']);
		
		if (!is_dir($cache_folder . $dir))
		{
			//Create cache folder/s if possible
			$temp_folders = explode('/', substr($dir, 0, -1));
			$prefix = '';
			
			if (count($temp_folders) > 0) {
				foreach($temp_folders as $dirs) {
					if (!is_dir($cache_folder . $prefix . $dirs)) {
						if (mkdir($cache_folder . $prefix . $dirs, 0775))
						{
							chmod($cache_folder . $prefix . $dirs, 0775);
							$prefix .= $dirs . '/';
							$cache_exists = true;
						} else {
							$cache_exists = false;
							break;
						}
					} else {
						$prefix .= $dirs . '/';
						$cache_exists = true;
					}
				}
			}
		} else {
			$cache_exists = true;
		}	
		
		if ($cache_exists == true) {
			return true;
		} else {
			return false;
		}
	}
	
	function genThumbURL($dir, $file_data) {
		$cache_folder = $this->fixPath($this->vars['dir_root_cache']);
		$use_cache = false;
		$file_ext = '';
		$temp_file_ext = '';
		$thumb_width = 0;
		$thumb_height = 0;
		$thumb_size = array();
		
		$file_ext = $this->pathInfo($file_data['full_path'], 'file_ext');
		//$temp_file_ext = strtolower($file_ext);
		
		$dir = $this->fixPath($dir);
		
		//if ($temp_file_ext == 'jpeg' or $temp_file_ext == 'jpg') { $file_ext = 'jpg'; }
		

		
		if ($this->settings['advanced']['use_gd'] == true)
		{		

			$use_cache = false;
			
			if (!is_file($cache_folder . $dir . $this->pathInfo($file_data['full_path'], 'file_name') . '_' . $this->vars['thumb_size'] .  '.' . $file_ext))
			{
				//Cached image does not exist, create if possible
				$use_cache = false;
			} else {
				//Cached image exists, check if correct image size
				list($thumb_width, $thumb_height) = getimagesize($cache_folder . $dir . $this->pathInfo($file_data['full_path'], 'file_name') . '_' . $this->vars['thumb_size'] .  '.' . $file_ext);
				
				$thumb_size = $this->resizedSize($file_data['data'][0], $file_data['data'][1]);
				
				if ($thumb_size[0] != $thumb_width and $thumb_size[1] != $thumb_height)
				{
					//Cached image does not match the current thumbnail size settings, create new thumbnail
					$use_cache = false;
				} else {
					//Cached image does not need updating, use cached thumbnail
					$use_cache = true;
				}
			}
			
			if ($use_cache == true)
			{
				$img_url = $cache_folder . $dir . $this->pathInfo($file_data['full_path'], 'file_name') . '_' . $this->vars['thumb_size'] .  '.' . $file_ext;
			} 
			else {
				$img_url = '?thumb=' . $dir . $this->pathInfo($file_data['full_path'], 'file_name') . '.' . $file_ext . '&size=' . $this->vars['thumb_size'];
			}
		} 
		else {
			$img_url = '?thumb=' . $dir . $this->pathInfo($file_data['full_path'], 'file_name') . '.' . $file_ext . '&size=' . $this->vars['thumb_size'];
		}

		
		if(file_exists($this->fixPath($this->settings['advanced']['thumbs_folder']) . $dir . $this->pathInfo($file_data['full_path'], 'file_name') . '.' . $this->settings['general']['thumb_file_ext'])){
				$img_url = $this->fixPath($this->settings['advanced']['thumbs_folder']) . $dir . $this->pathInfo($file_data['full_path'], 'file_name') . '.' . $this->settings['general']['thumb_file_ext'];
				}
		return $img_url;
	}
	
	function genThumbnail($filename)
	{
		//Creates thumbnail, either dynamically or for cache depending on settings
		
		$filename = $this->escapeString($filename, "strip");
		
		if ($this->checkExploit('/' . $filename, true) == true) {
			$filename = realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/' . $filename;
			
			$temp_path = substr($this->fixPath($this->pathInfo($filename, 'dir_path')), strlen($this->vars['dir_local']));
			if (substr($temp_path, 0, 1) == '/') { $temp_path = substr($temp_path, 1); }
			
			$cache_folder = $this->fixPath($this->vars['dir_root_cache']) . $temp_path;
			
			$create_cache_file = false;
			
			if ($this->settings['advanced']['use_gd'] == true)
			{
				$create_cache_file = $this->genCacheDir($temp_path);
			}
			
			$file_ext = strtolower($this->pathInfo($filename, 'file_ext'));
			
			if ($file_ext == 'jpg' or $file_ext == 'jpeg')
			{
				$image = imagecreatefromjpeg($filename);
				$format = 'jpeg';
			} else if ($file_ext == 'png') {
				$image = imagecreatefrompng($filename);
				$format = 'png';
			} else if ($file_ext == 'gif') {
				$image = imagecreatefromgif($filename);
				$format = 'gif';
			}
			
			$width = imagesx($image);
			$height = imagesy($image);
			
			$new_size = $this->resizedSize($width, $height);
			
			$new_image = ImageCreateTrueColor($new_size[0], $new_size[1]);
			imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_size[0], $new_size[1], $width, $height);

			if ($create_cache_file == false)
			{
				header('Pragma: public');
				header('Cache-Control: maxage=' . $this->settings['advanced']['gd_cache_expire']);
				header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $this->settings['advanced']['gd_cache_expire']) . ' GMT');
				header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
				
				if ($format == 'jpeg')
				{
					header('Content-type: image/jpeg');
					imagejpeg($new_image, null, $this->settings['advanced']['jpeg_quality']);
				} else if ($format == 'png') {
					header('Content-type: image/png');
					imagepng($new_image);
				} else if ($format == 'gif') {
					header('Content-type: image/gif');
					imagegif($new_image);
				}
			} else if ($create_cache_file == true) {
				header('Pragma: public');
				header('Cache-Control: maxage=' . $this->settings['advanced']['gd_cache_expire']);
				header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $this->settings['advanced']['gd_cache_expire']) . ' GMT');
				header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
				
				if ($format == 'jpeg')
				{
					header('Content-type: image/jpeg');
					imagejpeg($new_image, $cache_folder . $this->pathInfo($filename, 'file_name') . '_' . $this->vars['thumb_size'] . '.' . $this->pathInfo($filename, 'file_ext'), $this->settings['advanced']['jpeg_quality']);
					imagejpeg($new_image);
				} else if ($format == 'png') {
					header('Content-type: image/png');
					imagepng($new_image, $cache_folder . $this->pathInfo($filename, 'file_name') . '_' . $this->vars['thumb_size'] . '.' . $this->pathInfo($filename, 'file_ext'));
					imagepng($new_image);
				} else if ($format == 'gif') {
					header('Content-type: image/gif');
					imagegif($new_image, $cache_folder . $this->pathInfo($filename, 'file_name') . '_' . $this->vars['thumb_size'] . '.' . $this->pathInfo($filename, 'file_ext'));
					imagegif($new_image);
				}
			}
			
			imagedestroy($new_image);
		} else {
			echo 'File not found.';
		}
	}
	
	function setThumbSize() {
		
		$this->vars['thumb_size'] = $this->settings['general']['thumb_size'];
		
	}
	
	function outputSettingsArray() {
		echo '<pre>';
		print_r($this->settings);
		echo '</pre>';
	}
	
	function outputVarsArray() {
		echo '<pre>';
		print_r($this->vars);
		echo '</pre>';
	}
	//!Display 
	function showError() {
		echo $this->vars['error'];
	}

	
	
	function showLoadInfo($loadInfoFormat) {
		
		$this->endTimer();
		
		$search = array(
			'{{Version}}',
			'{{LoadTime}}'
			);
		$replace = array(
			$this->vars['version'],
			number_format($this->vars['total_time'], 7)
			);
		
			echo str_replace($search, $replace, $loadInfoFormat);
	

	}
	
	function showGallery($folderFormat, $imageFormat)
	{
		if ($this->vars['dir_req'] != '')
		{
			$request = $this->vars['dir_req'] . '/';
		} else {
			$request = '';
		}
		
		if (isset($this->vars['folder_list']))
		{
			foreach ($this->vars['folder_list'] as $dir)
			{	
				if (is_dir($request . $dir['dir']))
				{
					//Grab the directory info
					if ($this->settings['general']['thumb_folder_use_cache_only'] == true) {
						$dir_data = $this->getDirData($request . $dir['dir'], 'both', true, true);
					} 
					else {
						$dir_data = $this->getDirData($request . $dir['dir'], 'both', true);
					}
					
					if ($this->settings['general']['thumb_folder_shuffle'] == true) { shuffle($dir_data['file']); }
					
					//Grab the first one as a thumb						
					if ($dir_data['file']) {
						$temp_dir_data = $dir_data['file'][0];
						$img_url = $this->genThumbURL($request . $dir['dir'], $temp_dir_data);
					}
					else {
						$img_url = $this->showThemeURL(1) . 'images/no_images.png';
					}

				}
				else {
					$img_url = $this->showThemeURL(1) . 'images/no_images.png';
				}
				
				$search = array(
					'{{ThumbSize}}',
					'{{FolderTitle}}',
					'{{Link}}',
					'{{ThumbURL}}',
					'{{ThemePath}}',
					'{{Description}}'
					);
				$replace = array(
					$this->settings['general']['thumb_size'] . 'px',
					$dir['dir'],
					'?'.$request .str_replace( " ", "-", $dir['dir']),
					$this->escapeString($img_url),
					$this->showThemeURL(1),
					$dir['description']
					);

					echo str_replace($search, $replace, $folderFormat);
			}
		}
		
		if (isset($this->vars['file_list']))
		{
			foreach ($this->vars['file_list'] as $file)
			{		
				$img_url = $this->genThumbURL($request, $file);
				
				$url = $request . $file['file'];
				
			$search = array(
			'{{ThumbSize}}',
			'{{ImageTitle}}',
			'{{Link}}',
			'{{ThumbURL}}',
			'{{ThemePath}}',
			'{{Description}}'
			);
		$replace = array(
			$this->settings['general']['thumb_size'] . 'px',
			$this->cleanFileName($file['file']),
			$url,
			$this->escapeString($img_url),
			$this->showThemeURL(1),
			$file['description']
			);

		echo str_replace($search, $replace, $imageFormat);
		
			}
		}
		
	}
	
	function showUpFolderURL()
	{
		echo '?' . $this->pathInfo($_GET['file'], 'dir_path');
	}
	
	function showThemeURL($format = 0)
	{
		//0 = Output url
		//1 = Return url as string	
		if ($format == 0)
		{
			echo 'neuegal/themes/' . $this->settings['general']['theme'] . '/';
		} else if ($format == 1) {
			return 'neuegal/themes/' . $this->settings['general']['theme'] . '/';
		}
	}
	
	function showTitle($titleFormat)
	{
		$search = array(
			'{{SiteName}}',
			'{{PageTitle}}'
			);
		$replace = array(
			$this->settings['general']['site_name'],
			$this->vars['page_title']
			);
		
		echo str_replace($search, $replace, $titleFormat);
	
	}
	
	function showPage()
	{
		require($this->showThemeURL(1) . 'pages/' . $this->vars['page_requested'] . '.php');
	}

	function getImageDescription($file) {
		if (function_exists( 'exif_read_data' )) {	// Check if the function exists so no fatal error takes place
			$exif = @exif_read_data($file, 0, true);		// @ supresses warnings for Cannon images
			//print_r($exif);
			$output	= $exif['COMPUTED']['UserComment'];
		}
	
		return $output;
	}

	//!Thumbnails
	function resizedSize($width, $height, $return = 2)
	{
		//Returns width, height or an array of width and height for the thumbnail size of a full sized image		
		if ($width > $height)
		{
			$new_height = $this->vars['thumb_size'];
			$new_width = $width * ($this->vars['thumb_size'] / $height);
		} else if ($width < $height) {
			$new_height = $height * ($this->vars['thumb_size'] / $width);
			$new_width = $this->vars['thumb_size'];
		} else if ($width == $height) {
			$new_width = $this->vars['thumb_size'];
			$new_height = $this->vars['thumb_size'];
		}
		
		if ($return == 0)
		{
			//Return width
			return floor($new_width);
		} else if ($return == 1) {
			//Return height
			return floor($new_height);
		} else if ($return == 2) {
			//Return array with width and height
			return array(floor($new_width), floor($new_height));
		}
	}
	

	function initialize()
	{		
		//Debug Mode		
		if ($this->settings['advanced']['debug_mode'] == true)
		{
			error_reporting(E_ALL);
			ini_set('display_errors', '1');
		}
		
		//GZIP Compression
		ini_set('zlib.output_compression', $this->settings['advanced']['use_gzip_compression']);
		ini_set('zlib.output_compression_level', $this->settings['advanced']['gzip_compression_level']);
		
	
		//Load Variables
		$this->loadVars();
		
		//Load Blacklists/Whitelists
		$this->loadLists();
		
		//Display Content
		if (isset($_GET['thumb'])) 
		{
			//Show thumbnail only
			$this->genThumbnail($_GET['thumb']);
		} 
		else {
			//Show folder view	
			if ($this->vars['dir_req'] == '')
			{
				$dir_req = '';
			}
			else {
				$dir_req = $this->vars['dir_req'] . '/';
			}
			
			if ($this->vars['dir_req'] == '' || $this->checkExploit('/' . $this->vars['dir_req']) == true) {
				if (!$this->getDir($dir_req))
				{
					echo 'dir'.$dir_req;
					$this->vars['error'] = 'Folder doesn\'t exist A';
					$this->vars['page_title'] = 'Error';
					$this->vars['page_requested'] = 'error';
				}
				else {

					$this->vars['page_title'] = $this->vars['dir_req_current'];
					$this->vars['page_requested'] = 'folder';
				}
			} 
			else {
				$this->vars['error'] = 'Folder doesn\'t exist B';
				$this->vars['page_title'] = 'Error';
				$this->vars['page_requested'] = 'error';
			}
			require('neuegal/themes/' . $this->settings['general']['theme'] . '/template.php');
			
			if ($this->settings['advanced']['debug_show_all'] == true)
			{
				echo "DEBUG - Page Variables: <br><br>";
				echo "<pre>";
				print_r($this->vars);
				echo "</pre>";
			}
		}
	}
}
?>