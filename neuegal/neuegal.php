<?php
ini_set("gd.jpeg_ignore_warning", 1);

class NeueGal
{
	var $settings;
	var $vars;

	function loadSettings() {
		if (is_file('neuegal/settings.php')){
			require('neuegal/settings.php');
		}

		if (is_file('neuegal/themes/' . $this->settings['general']['theme'] . '/settings.php')){
			require('neuegal/themes/' . $this->settings['general']['theme'] . '/settings.php');
			
			return true;
		}

    }

	function loadVars() {
		//Directories
		$dir = array();
		$dir['root'] = pathinfo($_SERVER['SCRIPT_NAME'])['dirname']; // The folder where index.php is
		$dir['cache_from_root'] = $dir['root'] . $this->settings['advanced']['cache_folder'];
		
		$dir['current'] = '';
		//We listen to query strings to deduce the folder the user is going for
		// forinstance: nickwalker.us/photos?hello  would mean they want to look into the folder /photos/hello
		if($_SERVER['QUERY_STRING']){
			$dir['current'] .= $_SERVER['QUERY_STRING'];	
			$dir['current_parent'] = dirname($dir['current']);
		}						
		
		$dir['current_from_root'] = $dir['root'] . $dir['current']; 	
		$dir['root_from_server_root'] = dirname($_SERVER['SCRIPT_FILENAME']);
		
		foreach( $dir as $name=>$value){
			$dir[$name] = $this->normalizePath($dir[$name]);
		}
		$this->vars['dir'] = $dir;

		$this->vars['general']['thumb_size'] = $this->settings['general']['thumb_size'];
		$this->vars['general']['page_title'] = $dir['current'];
		//$this->vars['general']['description'] = 

		$temp_file = file_get_contents('neuegal/file_blacklist.txt');
		$this->vars['file_blacklist'] = explode(",", $temp_file);
		
		$temp_folder = file_get_contents('neuegal/folder_blacklist.txt');
		$this->vars['folder_blacklist'] = explode(",", $temp_folder);
		
		$temp_type = file_get_contents('neuegal/file_types.txt');
		$this->vars['file_types'] = explode(",", $temp_type);
		
		// Populates $this->vars['file_list'] and $this->vars['folder_list']
		$this->loadDirectoryInformation($this->vars['dir']['current']);
		var_dump($this->vars);
	}
	function loadDirectoryInformation($dir) {		
		if ($dir_data = $this->getDirData($dir)) {
			if (count($dir_data['file']) > 0) { $this->vars['file_list'] = $dir_data['file']; }
			if (count($dir_data['dir']) > 0) { $this->vars['folder_list'] = $dir_data['dir']; }
	
			$this->cacheDirectory($dir);
		
			
			return true;
		} else {
			return false;
		}
	}
	function initialize(){		
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
		
		//Display Content
		if (isset($_GET['thumb'])) 
		{
			//Show thumbnail only
			$this->generateThumbnail($_GET['thumb']);
		} 
		else {
		
			$settings = $this->settings;
			$vars = $this->vars;
			$dir = $this->vars['dir'];
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
	function showGallery(){
		$return_string = '';
		if (isset($this->vars['folder_list'])){
			foreach ($this->vars['folder_list'] as $dir){
				$return_string .= $this->makeFolder($dir);
			}
		}
		
		if (isset($this->vars['file_list'])){
			foreach ($this->vars['file_list'] as $file){		
				$return_string .= $this->makeFile($file);
		
			}
		}
		
		echo $return_string;
		
	}
//Content Generators
	function makeFile($file){
		$imageFormat = $this->settings['theme']['image'];

		$search = array(
			'{{ThumbSize}}',
			'{{ImageTitle}}',
			'{{Link}}',
			'{{ThumbURL}}',
			'{{Description}}'
		);
		
		$replace = array(
			$this->settings['general']['thumb_size'] . 'px',
			$file['name'],
			$file['path'],
			$this->generateThumbURL($file['path']),
			"Placeholder Description"//$file['description']
		);

		return str_replace($search, $replace, $imageFormat);
	}
	function makeFolder($directory){
		$folderFormat = $this->settings['theme']['folder']; 
		$fullPathToDir = $directory['path'];
			if (is_dir($fullPathToDir)){
				//Grab the directory info
				$dir_data = $this->getDirData($fullPathToDir);
				
				if ($this->settings['general']['thumb_folder_shuffle'] == true) { shuffle($dir_data['file']); }
				
				//Grab the first one as a thumb						
				if (isset($dir_data['file'][0])) {
					$thumb_url = $this->generateThumbURL($dir_data['file'][0]['path']);
				}
				else {
					$thumb_url = $this->getThemeURL() . 'images/no_images.png';
				}

			}
			else {
				$thumb_url = $this->getThemeURL() . 'images/no_images.png';
			}
				
			$search = array(
				'{{ThumbSize}}',
				'{{FolderTitle}}',
				'{{Link}}',
				'{{ThumbURL}}',
				'{{Description}}'
				);
			$replace = array(
				$this->settings['general']['thumb_size'] . 'px',
				$directory['name'],
				'?'.$directory['name'],
				$thumb_url,
				'Placeholder Folder Description'//$dir['description']
			);

		return str_replace($search, $replace, $folderFormat);
	}
	
	function getDirData($dir) {
		$current_cache_folder = $this->vars['dir']['cache_from_root'] . $dir . 'cache.xml';
		$dir = $this->normalizePath($dir);
		$full_dir = $this->normalizePath($this->vars['dir']['root_from_server_root']) . $dir;
		$output = array();
		$fd = array();
		$ff = array();
		
		if (is_dir($full_dir)) {
			if ( $this->settings['advanced']['use_gd_cache'] == true && is_file($current_cache_folder) ) {
				if (((time() - filemtime($current_cache_folder)) < $this->settings['advanced']['expire_file_cache'])) {
					$ff = $this->pullFilesFromCache($current_cache_folder);
					$fd = $this->pullFoldersFromCache($current_cache_folder);
				}
			}
			else {
				if ($dh = opendir($full_dir)) {
					while (($item = readdir($dh)) !== false) {
						if (filetype($full_dir . $item) == 'dir' && $this->checkList($item, $this->vars['folder_blacklist']) == false){
								$fd[] = array(
									'path'=>$dir . $item,
									'name'=>$item,
									'description'=> "Placeholder description"
								);
								
								sort($fd);
							}
						else if (
							filetype($full_dir . $item) == 'file'
							&& $this->checkList($item, $this->vars['file_blacklist']) == false
							&& $this->checkList(pathinfo($item)['extension'], $this->vars['file_types']) == true
							) {

								$ff[] = array(
									'path'=>$dir . $item,
									'name'=>$item,
									'data'=>getimagesize($full_dir . $item),
									'description'=> "Placeholder description" 
								);
								
								sort($ff);
							}
						}
						closedir($dh);
					} else {
						return false;
					}
				}
			
			
				$output['file'] = $ff;
				$output['dir'] = $fd;
				
				return $output;
			
		} else {
			return false;
		}
	}
	function pullFilesFromCache($cacheFile){
		$xml = new SimpleXMLElement(file_get_contents($cacheFile));
	
		$x = 0;
		$ff = array();
		
		if (isset($xml->files)){
			foreach($xml->files->file as $files){
				$ff[$x]['path'] = (string)$files->path;
				$ff[$x]['name'] = (string)$files->filename;
				$ff[$x]['data'][0] = (integer)$files->data->width;
				$ff[$x]['data'][1] = (integer)$files->data->height;
				$ff[$x]['data'][2] = (integer)$files->data->imagetype;
				$ff[$x]['data'][3] = (string)$files->data->sizetext;
				
				$x++;
			}
		}
		return $ff;
		
	}
	function pullFoldersFromCache($cacheFile){
	$xml = new SimpleXMLElement(file_get_contents($cacheFile));
	
		$x = 0;
		$fd = array();
		if (isset($xml->directories)){
			foreach($xml->directories->dir as $dirs){
				$fd[$x]['path'] = (string)$dirs->path;
				$fd[$x]['name'] = (string)$dirs->dirname;
				
				$x++;
			}
		}
		return $fd;
	}
	function cacheDirectory($dir) {
		$cacheFolderFromRoot = $this->vars['dir']['root_from_server_root'] . $this->vars['dir']['cache_from_root'];
		$cache_exists = false;
		
		if (count($this->vars['folder_list']) > 0 or count($this->vars['file_list']) > 0) {
			$cache_exists = $this->generateCacheDirectory($dir);
			
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
						$xml_dirs_data->addChild('path', $dirs['path']);
						$xml_dirs_data->addChild('dirname', $dirs['name']);
						if (isset($dirs['description'])) {$xml_dirs_data->addChild('description', $dirs['description']);}
					}
				}
				
				if (isset($this->vars['file_list']))
				{
					$xml_files = $xml->addChild('files');
					
					foreach($this->vars['file_list'] as $files)
					{
						$xml_files_data = $xml_files->addChild('file');
						$xml_files_data->addChild('path', $files['path']);
						$xml_files_data->addChild('filename', $files['name']);
						
						$xml_data = $xml_files_data->addChild('data');
						$xml_data->addChild('width', $files['data'][0]);
						$xml_data->addChild('height', $files['data'][1]);
						$xml_data->addChild('imagetype', $files['data'][2]);
						$xml_data->addChild('sizetext', $files['data'][3]);
						if (isset($files['description'])) { $xml_data->addChild('description', $files['description']); }
		
					}
				}
				
				$xml->asXML($cacheFolderFromRoot . $dir . 'cache.xml');
				return true;
				
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	function generateCacheDirectory($directory) {
		$cacheFolderFromServerRoot = $this->vars['dir']['root_from_server_root'] . $this->vars['dir']['cache_from_root'];
		if (!is_dir($cacheFolderFromServerRoot . $directory)){
			//Create cache folder/s if possible
			//Get an array of the layers of folders
			$temp_folders = explode('/', substr($directory, 0, -1));
			$prefix = '';
			//step through each layer adding the cache folders
			if (count($temp_folders) > 0) {
				foreach($temp_folders as $dir) {
					if (!is_dir($cacheFolderFromServerRoot . $prefix . $dir)) {
						if (mkdir($cacheFolderFromServerRoot . $prefix . $dir, 0775)){
							chmod($cacheFolderFromServerRoot . $prefix . $dir, 0775);
							$prefix .= $dir . '/';
							$cache_exists = true;
						} 
						else {
							$cache_exists = false;
							break;
						}
					}
					else {
						$prefix .= $dirs . '/';
						$cache_exists = true;
					}
				}
			}
		}
		else {
			$cache_exists = true;
		}	
		
		return $cache_exists;
	}
	
	function generateThumbURL($path) {
		$cache_folder = $this->vars['dir']['cache_from_root'];
		$use_cache = false;
		$thumb_width = 0;
		$thumb_height = 0;
		$thumb_size = array();
		
		$file_ext = pathinfo($path)['extension'];
		
		$dir = pathinfo($path)['dirname'];
		$fileName = pathinfo($path)['filename'];
		
		$cachedimagepath = $cache_folder . $dir . $fileName . '_' . $this->vars['general']['thumb_size'] .  '.' . $file_ext;
		$originalimagepath = $path;
		if ($this->settings['advanced']['use_gd'] == true){		

			$use_cache = false;
			
			if (!is_file($cachedimagepath))
			{
				//Cached image does not exist, create if possible
				$use_cache = false;
			}
			else {
				//Cached image exists, check if correct image size
				list($thumb_width, $thumb_height) = getimagesize($cachedimagepath);
				list($originalWidth, $originalHeight) = getimagesize($originalimagepath);
				$thumb_size = $this->resizedSize($originalWidth, $originalHeight);
				
				if ($thumb_size[0] != $thumb_width and $thumb_size[1] != $thumb_height){
					//Cached image does not match the current thumbnail size settings, create new thumbnail
					$use_cache = false;
				} else {
					//Cached image does not need updating, use cached thumbnail
					$use_cache = true;
				}
			}
			
			if ($use_cache == true){
				$img_url = $cache_folder . $dir . $fileName . '_' . $this->vars['general']['thumb_size'] .  '.' . $file_ext;
			} 
			else {
				$img_url = '?thumb=' . $dir . $fileName . '.' . $file_ext . '&size=' . $this->vars['general']['thumb_size'];
			}
		} 
		else {
			$img_url = '?thumb=' . $dir . $fileName . '.' . $file_ext . '&size=' . $this->vars['general']['thumb_size'];
		}

		//Overide for custom thumbs. Place a custom thumb of the same name as the original file in the correct thumb folder and it will be loaded
		if(file_exists($this->normalizePath($this->settings['advanced']['thumbs_folder']) . $dir . $fileName . '.' . $this->settings['general']['thumb_file_ext'])){
				$img_url = $this->normalizePath($this->settings['advanced']['thumbs_folder']) . $dir . $fileName . '.' . $this->settings['general']['thumb_file_ext'];
				}
		return $img_url;
	}
	
	//Creates thumbnail, either dynamically or for cache depending on settings
	function generateThumbnail($path){
		
		$safepath = $this->normalizePath($this->escapeString($path, "strip"));
		$filename = pathinfo($path)['basename'];
		$directory = pathinfo($path)['dirname'];
		$fileextension = pathinfo($path)['extension'];

		$cache_folder = $this->vars['dir']['cache_from_root'] . $safepath;
		$create_cache_file = false;
		return false;
		if ($this->settings['advanced']['use_gd'] == true){
			$create_cache_file = $this->generateCacheDirectory($directory);
		}
			
		$format = $fileextension;
		switch ($fileextension){
			case 'jpg':
			case 'jpeg':
				$image = imagecreatefromjpeg($path);
				$format = "jpeg";
			break;
			case 'png':
				$image = imagecreatefrompng($path);
				break;
			case 'gif':
				$image = imagecreatefromgif($path);
				break;
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
			
			$targetFileName = $cache_folder . $filename . '_' . $this->vars['general']['thumb_size'] . '.' . $fileextension;
			switch ($format){
			case "jpeg":
				header('Content-type: image/jpeg');
				imagejpeg($new_image, $targetFileName, $this->settings['advanced']['jpeg_quality']);
				imagejpeg($new_image);
			break;
			case "png":
				header('Content-type: image/png');
				imagepng($new_image, $targetFileName);
				imagepng($new_image);
			break;
			case "gif":
				header('Content-type: image/gif');
				imagegif($new_image, $targetFileName);
				imagegif($new_image);
			break;
			}
		}
		
		imagedestroy($new_image);
		
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
	function resizedSize($width, $height, $return = 2){
		//Returns width, height or an array of width and height for the thumbnail size of a full sized image		
		if ($width > $height)
		{
			$new_height = $this->vars['general']['thumb_size'];
			$new_width = $width * ($this->vars['general']['thumb_size'] / $height);
		} else if ($width < $height) {
			$new_height = $height * ($this->vars['general']['thumb_size'] / $width);
			$new_width = $this->vars['general']['thumb_size'];
		} else if ($width == $height) {
			$new_width = $this->vars['general']['thumb_size'];
			$new_height = $this->vars['general']['thumb_size'];
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
	function getThemeURL(){
		return 'neuegal/themes/' . $this->settings['general']['theme'] . '/';
	}
	function getUpFolderURL(){
		return '?' . pathinfo($_GET['file'])['dirname'];
	}
	//String Helpers
	
	function checkList($item, $list) {		
		foreach($list as $list_item){
			if (strtolower($list_item) == strtolower($item)){
				return true;
			}
		}
		return false;
	}
	//Cleanup and Formatting Helpers
	
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
	//Must end with a slash unless it's empty
	function normalizePath($path) {
		if ($path == '') {
			return '';
		} else if (substr($path, -1) !== '/') {
			return $path . '/';
		} else {
			return $path;
		}
	}
// Plumbing and Debug
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
	function showError() {
		echo $this->vars['error'];
	}
}
//This is a convenience polyfill until PHP6
function issetor(&$var, $default = false) {
	return isset($var) ? $var : $default;
}
function echoIfExists($path, $string){
	if( file_exists($path)){
		echo $string; 
	}
}