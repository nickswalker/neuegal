<?php

namespace Nickswalker\NeueGal;

class NeueGal{
	var $settings;
	var $vars;
	var $fileSystemHelper;
	
	var $publicFromRoot;
	var $themePathFromRoot;
	var $packagePathFromRoot; //Location of NeueGal.php
	var $photosPathFromRoot; //The folder where your images are stored. Must be public
	
	public function __construct($themePathFromRoot, $photosPathFromRoot) {
		//var_dump($_SERVER);
		$this->publicPathFromRoot = normalizePath($_SERVER['DOCUMENT_ROOT']);
		$this->themePathFromRoot = normalizePath($themePathFromRoot);
		$this->packagePathFromRoot = dirname(__FILE__) . '/';
		$this->photosPathFromRoot = normalizePath($photosPathFromRoot);
		
		$this->vars['version'] = '1.1';
		$this->startTimer();
		$this->fileSystemHelper  = new FileSystemHelper($this->publicPathFromRoot, $themePathFromRoot, $photosPathFromRoot);
		$this->loadSettings();
			
		//Debug Mode		
		if ($this->settings['advanced']['debug'])
		{
			error_reporting(E_ALL);
			ini_set('display_errors', '1');
		}

		//Load Variables
		$this->loadVars();
    	
	}

	function loadSettings() {

		$this->settings = include( $this->packagePathFromRoot .'settings.php');
		$themeSettingsPath = $this->themePathFromRoot . 'settings.php';
		if ( is_file($themeSettingsPath) ){
			$themeSettings = include( $themeSettingsPath );
			$this->settings = array_replace_recursive($this->settings, $themeSettings);
		}
	}
		//Setup some important variables that will be available to the entire object
	function loadVars() {
		//Directories
		$dir = array();
		
		$this->vars['current_directory'] = '';
		//We listen to query strings to deduce the folder the user is going for
		// forinstance: nickwalker.us/photos?hello  would mean they want to look into the folder /photos/hello
		$this->vars['current_directory'] = normalizePath($_SERVER['QUERY_STRING']);
							
		$this->vars['gallery_url'] = dirname($_SERVER['REQUEST_URI']); 
		$this->vars['current_folder_name'] = $this->getDirectoryName($this->vars['current_directory']);
		$this->vars['description'] = FileSystemHelper::getFolderDescription($this->photosPathFromRoot . $this->vars['current_directory']);

		// Populates $this->vars['file_list'] and $this->vars['folder_list']
		$this->loadDirectoryInformation($this->photosPathFromRoot . $this->vars['current_directory']);
		
		//var_dump($this->vars);
		
	}
	function loadDirectoryInformation($path) {

		$pathFromGallery = $this->getGalleryRelativeURLFromPath($path);

		$currentCacheFileFromRoot = $this->photosPathFromRoot . 'cache/' . $pathFromGallery . 'cache.xml';
		if (is_file($currentCacheFileFromRoot)){
			$directoryData = $this->fileSystemHelper->getDirectoryDataFromCache($currentCacheFileFromRoot);
		}	
		else {
			$directoryData = $this->fileSystemHelper->getDirectoryData( $path);
			$this->fileSystemHelper->cacheDirectory($this->getGalleryRelativeURLFromPath($path), $directoryData['file'],$directoryData['dir']);
		}
		
		if (count($directoryData['file']) > 0) { $this->vars['file_list'] = $directoryData['file']; }
		if (count($directoryData['dir']) > 0) { $this->vars['folder_list'] = $directoryData['dir']; }
	}
	function display(){
		$settings = $this->settings;
		$vars = $this->vars;
		require $this->themePathFromRoot . 'template.php';
		
		if ($this->settings['advanced']['debug'] == true)
		{
			echo "DEBUG - Page Variables: <br><br>";
			echo "<pre>";
			print_r($this->vars);
			echo "</pre>";
		}
	}
	function showGallery(){
		$return_string = '';
		if (isset($this->vars['folder_list'])){
			foreach ($this->vars['folder_list'] as $directory){
				$return_string .= $this->makeFolder($directory);
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
		$imageFormat = $this->settings['theme']['imageFormat'];
		$search = array(
			'{{ThumbSize}}',
			'{{Title}}',
			'{{Path}}',
			'{{ThumbPath}}',
			'{{Description}}'
		);
		
		$replace = array(
			$this->settings['thumbnail_size'] . 'px',
			pathinfoFilename($image['name']),
			$this->getURLFromPath($image['path']),
			$this->getThumbnailURL($image['path']),
			$image['description']
		);

		return str_replace($search, $replace, $imageFormat);
	}
	function makeFolder($folder){
		$folderFormat = $this->settings['theme']['folderFormat']; 
		//Grab the directory info
		$directoryData = $this->fileSystemHelper->getDirectoryData($folder['path']);
		
		if ($this->settings['random_folder_thumbnail'] == true) { shuffle($directoryData['file']); }
		
		//Grab the first one as a thumb						
		if (isset($directoryData['file'][0])) {
			$thumb_url = $this->getThumbnailURL($directoryData['file'][0]['path']);
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
			$this->settings['thumbnail_size'] . 'px',
			$folder['name'],
			'?'.$this->getGalleryRelativeURLFromPath($folder['path']),
			$thumb_url,
			$folder['description']
		);

		return str_replace($search, $replace, $folderFormat);
	}
	
	//Checks to see if a thumbnail already exists, if not it creates one and returns the path to it in the cache
	function getThumbnailURL($path) {
		$directory = dirname($this->getGalleryRelativeURLFromPath($path));
		
		//A singal dot signifies the current directory, however, that's inconvenient for our purpose
		if ($directory == "."){
			$directory = "";
		}
		$directory = normalizePath("$directory");
		
		$fileName = pathinfoFilename($path);
		$fileExtension = pathinfoExtension($path);
		$possibleCachedFilePathFromRoot =  $this->photosPathFromRoot . 'cache/' . $directory . $fileName . '_' . $this->settings['thumbnail_size'] . '.' . $fileExtension;
		$possibleCachedFileURL = $this->getURLFromPath($possibleCachedFilePathFromRoot);

		$possibleCustomThumbPath = $this->photosPathFromRoot. 'custom thumbnails/' . $directory . $fileName . '.' . $fileExtension;
	
		$originalimagepath = $path;
		
		if ( is_file($possibleCustomThumbPath) ){
			return $this->getURLFromPath($possibleCustomThumbPath);
		}
		else if ( is_file($possibleCachedFilePathFromRoot) ){
			return $possibleCachedFileURL;
		}
		else {
			//This is where you could insert a setting to disable the use of custom thumbnails
			$this->generateThumbnail($originalimagepath);
			return $possibleCachedFileURL;
		}

	}
	//Takes a path to an original file, creates thumbnail and caches it
	function generateThumbnail($originalImagePathFromRoot){	
		$imageGalleryRelative = $this->getGalleryRelativeURLFromPath($originalImagePathFromRoot);
		
		$filename = pathinfoFilename($imageGalleryRelative);
		$fileExtension = pathinfoExtension($imageGalleryRelative);
		$directory = pathinfoDirname($imageGalleryRelative);
		
		if($directory === "."){
			$directory = "";
		}
		$directory = normalizePath($directory);
		$targetCachedFileName =  $this->photosPathFromRoot . 'cache/' . $directory . $filename . '_' . $this->settings['thumbnail_size'] . '.' . $fileExtension;
		$createCachedFile = $this->fileSystemHelper->generateCacheDirectory($directory);

		//Load the original image
		switch ($fileExtension){
			case 'jpg':
			case 'jpeg':
				$image = imagecreatefromjpeg($originalImagePathFromRoot);
				$format = "jpeg";
				break;
			case 'png':
				$image = imagecreatefrompng($originalImagePathFromRoot);
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
			$new_height = $this->settings['thumbnail_size'];
			$new_width = $width * ($this->settings['thumbnail_size'] / $height);
		} else if ($width < $height) {
			$new_height = $height * ($this->settings['thumbnail_size'] / $width);
			$new_width = $this->settings['thumbnail_size'];
		} else if ($width == $height) {
			$new_width = $this->settings['thumbnail_size'];
			$new_height = $this->settings['thumbnail_size'];
		}
		return array(floor($new_width), floor($new_height));
		
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

	function getThemeURL(){
		//We stipulate that the theme path must be public.
		//Thus, we remove the installation path from the theme path and we are left
		//with a relative path to the theme.

		// publicPathFromRoot  home/www/public_html/
		// themePathFromRoot   home/www/public_html/resume/themes/default
		// result			   					    resume/themes/default

		//Just add a slash to the front and you're set!
		return '/'. str_replace($this->publicPathFromRoot, '', $this->themePathFromRoot);
	}
	function getGalleryRelativeURLFromPath($path){
		// galleryPathFromRoot  /home/www/public_html/photos/
		// path					/home/www/public_html/photos/samples
		// result 											 samples
		return str_replace($this->photosPathFromRoot, '', $path);
	}
	function getURLFromPath($path){
		return '/' . str_replace($this->publicPathFromRoot, '', $path);
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
	//This is a convenience polyfill until PHP6
	function issetor(&$var, $default = false) {
		return isset($var) ? $var : $default;
	}
	function echoIfExists($path, $string){
		if( file_exists($path)){
			echo $string; 
		}
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
//Polyfill until PHP5.4 and anonymous array subscripting
function pathinfoExtension($path){
	$pathinfo = pathinfo($path);
	return $pathinfo['extension'];
}
function pathinfoFilename($path){
	$pathinfo = pathinfo($path);
	return $pathinfo['filename'];
}
function pathinfoDirname($path){
	$pathinfo = pathinfo($path);
	return $pathinfo['dirname'];
}
