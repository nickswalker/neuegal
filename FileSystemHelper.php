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
				if (filetype($pathFromRoot . $item) == 'dir' && !FileSystemHelper::isInList($item, $folderBlacklist) ){
						$directories[] = array(
							'path'=>normalizePath($path . $item),
							'name'=>$item,
							'description'=> "Placeholder"
						);
						
						sort($directories);
					}
				else if (
					filetype($pathFromRoot . $item) == 'file'
					&& !FileSystemHelper::isInList($item, $fileBlacklist)
					&& FileSystemHelper::isInList(pathinfo($item)['extension'], $fileTypes)
					) {

						$files[] = array(
							'path'=>normalizePath($path . $item),
							'name'=>$item,
							'data'=>getimagesize($pathFromRoot . $item),
							'description'=> "Placeholder"
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
	function getDirectoryDataFromCache($path, $cacheFilePath){
		//If the folder is real, check the caches
		//$currentCacheFile = $this->vars['dir']['cache_from_root'] . $path . 'cache.xml';
		if (is_dir($path)) {
			
			if ( is_file($cacheFilePath) ) {
				if (  ( time() - filemtime($cacheFilePath) ) < 100000 ) {
					$xml = new SimpleXMLElement(file_get_contents($cacheFilePath));
					$files = FileSystemHelper::pullFilesFromCache($xml);
					$directories = FileSystemHelper::pullFoldersFromCache($xml);
				}
			}
		}
		$output['file'] = $files;
		$output['dir'] = $directories;
		
		return $output;
	}
	static function pullFilesFromCache($xml){
		$i = 0;
		$files = array();
		
		if (isset($xml->files)){
			foreach($xml->files->file as $files){
				$files[$i]['path'] = (string)$files->path;
				$files[$i]['name'] = (string)$files->filename;
				$files[$i]['data'][0] = (integer)$files->data->width;
				$files[$i]['data'][1] = (integer)$files->data->height;
				$files[$i]['data'][2] = (integer)$files->data->imagetype;
				$files[$i]['data'][3] = (string)$files->data->sizetext;
				
				$i++;
			}
		}
		return $files;
		
	}
	static function pullFoldersFromCache($xml){
		$i = 0;
		$directories = array();
		if (isset($xml->directories)){
			foreach($xml->directories->dir as $dirs){
				$directories[$i]['path'] = (string)$dirs->path;
				$directories[$i]['name'] = (string)$dirs->dirname;
				
				$i++;
			}
		}
		return $directories;
	}
	static function getImageDescription($path) {
	
		$imageName = pathinfo($path)['filename'];
		$imageDirectory = pathinfo($path)['dirname'];
		$possibleDescriptionPath =  $imageDirectory . $imageName . '.txt';
		if( is_file($possibleDescriptionPath) ){
			return (string)file_get_contents($possibleDescriptionPath);
		}
		return $output;
	}
	static function getDirectoryDescription($path){
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