<?php

//Handles all caching, all file system polling and saving
class FileSystemHelper{
	
	public function __construct($installationRoot) {
    	$this->installationRoot = normalizePath($installationRoot);
    	
	}
	
	//Takes an installation relative path and returns an array of all folders and images with additional information
	function getDirectoryData($path) {
		$pathFromRoot = $this->installationRoot . $path;
	
		$fileBlacklist = file_get_contents('neuegal/file_blacklist.txt');
		$fileBlacklist = explode(",", $fileBlacklist);
		
		$folderBlacklist = file_get_contents('neuegal/folder_blacklist.txt');
		$folderBlacklist = explode(",", $folderBlacklist);
		
		$fileTypes = file_get_contents('neuegal/file_types.txt');
		$fileTypes = explode(",", $fileTypes);
		
		$output = array();
		$directories = array();
		$files = array();
		if ($dh = opendir($pathFromRoot)) {
			while (($item = readdir($dh)) !== false) {
				if (is_dir($pathFromRoot . $item) && !FileSystemHelper::isInList($item, $folderBlacklist) ){
						$directories[] = array(
							'path'=>normalizePath($path . $item),
							'name'=>$item,
							'description'=> FileSystemHelper::getFolderDescription(normalizePath($pathFromRoot.$item))
						);
						
						sort($directories);
					}
				else if (
					is_file($pathFromRoot . $item)
					&& !FileSystemHelper::isInList($item, $fileBlacklist)
					&& FileSystemHelper::isInList(pathinfo($item)['extension'], $fileTypes)
					) {

						$files[] = array(
							'path'=>$path . $item,
							'name'=>$item,
							'data'=>getimagesize($pathFromRoot . $item),
							'description'=> FileSystemHelper::getImageDescription($pathFromRoot.$item)
						);
						
						sort($files);
					}
				}
				closedir($dh);
			} 
					
		
			$output['file'] = $files;
			$output['dir'] = $directories;
			
			return $output;
			
	}
	function getDirectoryDataFromCache($cacheFilePath){
			
		if ( is_file($cacheFilePath) ) {
			if (  ( time() - filemtime($cacheFilePath) ) < 100000 ) {
				$xml = new SimpleXMLElement(file_get_contents($cacheFilePath));
				
				$files = FileSystemHelper::pullImagesFromXML($xml);
				$directories = FileSystemHelper::pullFoldersFromXML($xml);
			}
		}
		$output['file'] = $files;
		$output['dir'] = $directories;
		
		return $output;
	}
	private static function pullImagesFromXML($xml){
		$i = 0;
		$images = array();
		
		if (isset($xml->files)){
			foreach($xml->files->file as $image){
				$images[$i]['path'] = (string)$image->path;
				$images[$i]['name'] = (string)$image->name;
				$images[$i]['data'][0] = (integer)$image->data->width;
				$images[$i]['data'][1] = (integer)$image->data->height;
				$images[$i]['data'][2] = (integer)$image->data->imagetype;
				$images[$i]['data'][3] = (string)$image->data->sizetext;
				$images[$i]['description'] = (string)$image->description;
				
				$i++;
			}
		}
		return $images;
		
	}
	private static function pullFoldersFromXML($xml){
		$i = 0;
		$directories = array();
		if (isset($xml->directories)){
			foreach($xml->directories->dir as $dir){
				$directories[$i]['path'] = (string)$dir->path;
				$directories[$i]['name'] = (string)$dir->name;
				$directories[$i]['description'] = (string)$dir->description;
				
				$i++;
			}
		}
		return $directories;
	}
	static function getImageDescription($path) {
		
		$imageName = pathinfo($path)['filename'];
		$imageDirectory = normalizePath(pathinfo($path)['dirname']);
		$possibleDescriptionPath =  $imageDirectory . $imageName . '.txt';
		if( is_file($possibleDescriptionPath) ){
			return file_get_contents($possibleDescriptionPath);
		}
		return null;
	}
	static function getFolderDescription($path){
		$possibleDescriptionPath = $path.'description.txt';
		if( is_file($possibleDescriptionPath) ){
			return (string)file_get_contents($possibleDescriptionPath);
		}
		return null;
	}
	//String Helpers
	
	static function isInList($item, $list) {		
		foreach($list as $list_item){
			if (strtolower($list_item) == strtolower($item)){
				return true;
			}
		}
		return false;
	}
}