<?php

namespace Nickswalker\NeueGal;

//Handles all caching, all file system polling and saving
class FileSystemHelper{

	var $publicFromRoot;
	var $themePathFromRoot;
	var $packagePathFromRoot; //Location of NeueGal.php
	var $galleryPathFromRoot;

	public function __construct($publicFromRoot, $themePathFromRoot, $galleryPathFromRoot) {
    	$this->publicPathFromRoot = normalizePath($publicFromRoot);
		$this->themePathFromRoot = normalizePath($themePathFromRoot);
		$this->packagePathFromRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR;
		$this->galleryPathFromRoot = normalizePath($galleryPathFromRoot);

	}
	// RETRIEVAL
	//Takes an installation relative path and returns an array of all folders and images with additional information
	function getDirectoryData($pathFromRoot) {

		$fileBlacklist = file_get_contents($this->packagePathFromRoot . 'file_blacklist.txt');
		$fileBlacklist = explode(",", $fileBlacklist);

		$folderBlacklist = file_get_contents($this->packagePathFromRoot . 'folder_blacklist.txt');
		$folderBlacklist = explode(",", $folderBlacklist);

		$fileTypes = file_get_contents($this->packagePathFromRoot . 'file_types.txt');
		$fileTypes = explode(",", $fileTypes);

		$output = array();
		$directories = array();
		$files = array();
		if ($dh = opendir($pathFromRoot)) {
			while (($item = readdir($dh)) !== false) {
				if (is_dir($pathFromRoot . $item) && !FileSystemHelper::isInList($item, $folderBlacklist) ){
						$directories[] = array(
							'path'=>normalizePath($pathFromRoot . $item),
							'name'=>$item,
							'description'=> FileSystemHelper::getFolderDescription(normalizePath($pathFromRoot.$item))
						);

						sort($directories);
					}
				else if (
					is_file($pathFromRoot . $item)
					&& !FileSystemHelper::isInList($item, $fileBlacklist)
					&& FileSystemHelper::isInList(pathinfoExtension($item), $fileTypes)
					) {

						$files[] = array(
							'path'=>$pathFromRoot . $item,
							'name'=>$item,
							'data'=>array(
								'width' => getimagesizeWidth($pathFromRoot . $item),
								'height' => getimagesizeHeight($pathFromRoot . $item)
							),
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
    	$directories = array();
		$files = array();

		if ( is_file($cacheFilePath) ) {
				$xml = new \SimpleXMLElement(file_get_contents($cacheFilePath));

				$files = FileSystemHelper::pullImagesFromXML($xml);
				$directories = FileSystemHelper::pullFoldersFromXML($xml);
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
				$images[$i]['data']['width'] = (integer)$image->data->width;
				$images[$i]['data']['height'] = (integer)$image->data->height;
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

		$imageName = pathinfoFilename($path);
		$imageDirectory = normalizePath(pathinfoDirname($path));
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

	// CACHERS

	function cacheDirectory($directory, $files, $directories) {
		$cacheFolderFromRoot = $this->galleryPathFromRoot . 'cache'. DIRECTORY_SEPARATOR;

		if ( isset($directories) || isset($files) ) {
			$cacheDirectoryExists = $this->generateCacheDirectory($directory);

			if ($cacheDirectoryExists){
				$xmlstr = "<?xml version='1.0' ?>\n<cache></cache>";
				$xml = new \SimpleXMLElement($xmlstr);

				if (isset($directories) ){
					$xml_dir = $xml->addChild('directories');

					foreach($directories as $dir){
						$xml_dirs_data = $xml_dir->addChild('dir');
						$xml_dirs_data->addChild('path', $dir['path']);
						$xml_dirs_data->addChild('name', $dir['name']);
						$xml_dirs_data->addChild('description', $dir['description']);
					}
				}

				if (isset($files)){
					$xml_files = $xml->addChild('files');

					foreach($files as $file){
						$xml_files_data = $xml_files->addChild('file');
						$xml_files_data->addChild('path', $file['path']);
						$xml_files_data->addChild('name', $file['name']);
						$xml_files_data->addChild('description', $file['description']);

						$xml_data = $xml_files_data->addChild('data');
						$xml_data->addChild('width', $file['data']['width']);
						$xml_data->addChild('height', $file['data']['height']);


					}
				}

				$xml->asXML($cacheFolderFromRoot . $directory . 'cache.xml');
				return true;

			}
		}
		return false;
	}

	function generateCacheDirectory($directory) {

		$desiredCachePath = $this->galleryPathFromRoot ."cache". DIRECTORY_SEPARATOR . $directory;
		if (!file_exists($desiredCachePath)) {
				return mkdir($desiredCachePath, 0777, true);
			}
		return true;
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
function getimagesizeWidth($path){
	$imagesize = getimagesize($path);
	return $imagesize[0];
}
function getimagesizeHeight($path){
	$imagesize = getimagesize($path);
	return $imagesize[1];
}