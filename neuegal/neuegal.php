<?php
require("FileSystemHelper.php");
class NeueGal{
	var $settings;
	var $vars;
	var $fileSystemHelper;
	
	public function __construct() {
		$this->fileSystemHelper  = new FileSystemHelper(normalizePath(dirname($_SERVER['SCRIPT_FILENAME'])));
    	
	}

	function loadSettings() {
		if (is_file('neuegal/settings.php')){
			require('neuegal/settings.php');
		}

		if (is_file('neuegal/themes/' . $this->settings['general']['theme'] . '/settings.php')){
			require('neuegal/themes/' . $this->settings['general']['theme'] . '/settings.php');
			
			return true;
		}

    }
	//Setup some important variables that will be available to the entire object
	function loadVars() {
		//Directories
		$dir = array();
		$dir['root'] = pathinfo($_SERVER['SCRIPT_NAME'])['dirname']; // The folder where index.php is
		$dir['cache_from_root'] = $dir['root'] . $this->settings['advanced']['cache_path'];
		
		$dir['current'] = '';
		//We listen to query strings to deduce the folder the user is going for
		// forinstance: nickwalker.us/photos?hello  would mean they want to look into the folder /photos/hello
		if($_SERVER['QUERY_STRING']){
			$dir['current'] .= $_SERVER['QUERY_STRING'];
			
		}						
		
		$dir['current_from_root'] = $dir['root'] . $dir['current']; 	
		$dir['current_parent_from_root'] = dirname($dir['current_from_root']);
		$dir['root_from_server_root'] = dirname($_SERVER['SCRIPT_FILENAME']);
		
		foreach( $dir as $name=>$value){
			$dir[$name] = normalizePath($dir[$name]);
		}
		$this->vars['dir'] = $dir;
		$this->vars['general']['current_folder_name'] = $this->getDirectoryName($dir['current']);
		$this->vars['general']['description'] = 

		
		
		// Populates $this->vars['file_list'] and $this->vars['folder_list']
		$this->loadDirectoryInformation($this->vars['dir']['current']);
		
	}
	function loadDirectoryInformation($dir) {		
		if ($directoryData = $this->fileSystemHelper->getDirectoryData( $dir)) {
			if (count($directoryData['file']) > 0) { $this->vars['file_list'] = $directoryData['file']; }
			if (count($directoryData['dir']) > 0) { $this->vars['folder_list'] = $directoryData['dir']; }
	
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

		//Load Variables
		$this->loadVars();
		
		//Display Content
		if (isset($_GET['thumb'])) {
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
				$return_string .= $this->makeImage($file);
		
			}
		}
		
		echo $return_string;
		
	}
//Content Generators
	function makeImage($image){
		$imageFormat = $this->settings['theme']['image'];
		$search = array(
			'{{ThumbSize}}',
			'{{Title}}',
			'{{Path}}',
			'{{ThumbPath}}',
			'{{Description}}'
		);
		
		$replace = array(
			$this->settings['general']['thumbnail_size'] . 'px',
			pathinfo($image['name'])['filename'],
			$image['path'],
			$this->getThumbnailPath($image['path']),
			"Placeholder Description"//$file['description']
		);

		return str_replace($search, $replace, $imageFormat);
	}
	function makeFolder($directory){
		$folderFormat = $this->settings['theme']['folder']; 
		$fullPathToDir = $this->vars['dir']['root_from_server_root'] . $directory['path'];
		//Grab the directory info
		$directoryData = $this->fileSystemHelper->getDirectoryData($directory['path']);
		
		if ($this->settings['general']['random_folder_thumbnail'] == true) { shuffle($directoryData['file']); }
		
		//Grab the first one as a thumb						
		if (isset($directoryData['file'][0])) {
			$thumb_url = $this->getThumbnailPath($directoryData['file'][0]['path']);
		}
		else {
			$thumb_url = $this->getThemeURL() . 'images/no_images.png';
		}

		$search = array(
			'{{ThumbSize}}',
			'{{Title}}',
			'{{Path}}',
			'{{ThumbPath}}',
			'{{Description}}'
			);
		$replace = array(
			$this->settings['general']['thumbnail_size'] . 'px',
			$directory['name'],
			'?'.$directory['name'],
			$thumb_url,
			'Placeholder Folder Description'//$dir['description']
		);

		return str_replace($search, $replace, $folderFormat);
	}
	

	function cacheDirectory($dir) {
		$cacheFolderFromRoot = $this->vars['dir']['root_from_server_root'] . $this->vars['dir']['cache_from_root'];
		$cache_exists = false;
		
		if ( isset($this->vars['folder_list']) or isset($this->vars['file_list']) ) {
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
	
	//Checks to see if a thumbnail already exists, if not it creates one and returns the path to it in the cache
	function getThumbnailPath($path) {
		
		$directory = pathinfo($path)['dirname'];
		
		//A singal dot signifies the current directory, however, that's inconvenient for our purpose
		if ($directory == "."){
			$directory = "";
		}
		$directory = normalizePath("$directory");
		$fileName = pathinfo($path)['filename'];
		$fileExtension = pathinfo($path)['extension'];
		$possibleCachedFilePath = $this->vars['dir']['cache_from_root'] . $directory . $fileName . '_' . $this->settings['general']['thumbnail_size'] . '.' . $fileExtension;
		$possibleCachedFilePathFromRoot =  substr($this->vars['dir']['root_from_server_root'], 0, -1) . $possibleCachedFilePath;
		
		$getThumbnailPath = '?thumb=' . $directory . $fileName . '.' . $fileExtension . '&size=' . $this->settings['general']['thumbnail_size'];
		$possibleCustomThumbPath = $this->settings['advanced']['custom_thumbnails_path'] . $directory . $fileName . '.' . $fileExtension;
	
		$originalimagepath = $path;
		if ( is_file($possibleCustomThumbPath) ){
			return $possibleCustomThumbPath;
		}
		else if ( is_file($possibleCachedFilePathFromRoot) ){
			return $possibleCachedFilePath;
		}
		else {
			//This is where you could insert a setting to disable the use of custom thumbnails
			$this->generateThumbnail($originalimagepath);
			return $possibleCachedFilePath;
		}

	}
	//Takes a path to an original file, creates thumbnail and caches it
	function generateThumbnail($originalImagePath){
		$filename = pathinfo($originalImagePath)['filename'];
		$fileExtension = pathinfo($originalImagePath)['extension'];
		$directory = pathinfo($originalImagePath)['dirname'];
		
		if($directory === "."){
			$directory = "";
		}
		$directory = normalizePath($directory);
		
		$cacheFolder = $this->vars['dir']['cache_from_root'] . $directory;
		$targetCachedFileName =  substr($this->vars['dir']['root_from_server_root'], 0, -1) . $this->vars['dir']['cache_from_root'] . $directory . $filename . '_' . $this->settings['general']['thumbnail_size'] . '.' . $fileExtension;
		
	
		$createCachedFile = $this->generateCacheDirectory($directory);

		//Load the original image
		switch ($fileExtension){
			case 'jpg':
			case 'jpeg':
				$image = imagecreatefromjpeg($originalImagePath);
				$format = "jpeg";
				break;
			case 'png':
				$image = imagecreatefrompng($originalImagePath);
				break;
		}
		
		//Resize it
		$width = imagesx($image);
		$height = imagesy($image);
		
		$new_size = $this->resizedSize($width, $height);
		
		$newImage = ImageCreateTrueColor($new_size[0], $new_size[1]);
		
		imagecopyresampled($newImage, $image, 0, 0, 0, 0, $new_size[0], $new_size[1], $width, $height);
		$this->cacheImage($newImage, $targetCachedFileName, $format);
		return true;
		
	}
	function displayImage($image, $format){
		
		header('Pragma: public');
		header('Cache-Control: maxage=' . $this->settings['advanced']['thumbnail_expire']);
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $this->settings['advanced']['thumbnail_expire']) . ' GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		
		switch ($format){
			case 'jpeg':
			case 'jpg':
				header('Content-type: image/jpeg');
				imagejpeg($image, null, $this->settings['advanced']['jpeg_quality']);
				break;
			case 'png':
				header('Content-type: image/png');
				imagepng($image);
				break;
		}	
		imagedestroy($image);
	}
	
	function cacheImage($image, $path, $format){
		switch ($format){
			case 'jpeg':
				imagejpeg($image, $path, $this->settings['advanced']['jpeg_quality']);
				break;
			case 'png':
				header('Content-type: image/png');
				imagepng($image, $path);
				break;
		}	
	}


	//!Thumbnails
	function resizedSize($width, $height){
		//Returns width, height or an array of width and height for the thumbnail size of a full sized image		
		if ($width > $height){
			$new_height = $this->settings['general']['thumbnail_size'];
			$new_width = $width * ($this->settings['general']['thumbnail_size'] / $height);
		} else if ($width < $height) {
			$new_height = $height * ($this->settings['general']['thumbnail_size'] / $width);
			$new_width = $this->settings['general']['thumbnail_size'];
		} else if ($width == $height) {
			$new_width = $this->settings['general']['thumbnail_size'];
			$new_height = $this->settings['general']['thumbnail_size'];
		}
		return array(floor($new_width), floor($new_height));
		
	}
	function getThemeURL(){
		return 'neuegal/themes/' . $this->settings['general']['theme'] . '/';
	}
	function getUpFolderURL(){
		return '?' . pathinfo($_GET['file'])['dirname'];
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

	function getDirectoryName($path){
		$directoriesFromPath = explode('/', $path);
		if (isset($directoriesFromPath[count($directoriesFromPath)-2])){
			return $directoriesFromPath[count($directoriesFromPath)-2];
		}
		return null;
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
function normalizePath($path) {
	$path = str_replace("%20", " ", $path);
	if ($path == '') {
		return '';
	} else if (substr($path, -1) !== '/') {
		return $path . '/';
	} else {
		return $path;
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