NeueGal
=======

A simple, no-database PHP image gallery. Neuegal is a fork of the fantastic [PHPPI](http://code.google.com/p/phppi/) 
which aims to make it easy to create simple galleries for photography portfolios or other purposes. Think of it as a 
bare bones Apache index for photos, except it looks great out of the box and has simple to use theming support so you 
can integrate it with your existing designs. Comes with [Fancybox](http://fancyapps.com/fancybox/) built in.

More information and examples: http://nickswalker.github.com/neuegal/

Installation
------

Just unzip the download into any directory on a PHP 5.2 or above enabled server and you'll have a working gallery that will
display the included directory of sample images. 

Usage
------
####Adding Images and Files
Simply add images or folders to the directory and your gallery will update
to display them just like you'd imagine. Note however that directory information is cached and you may have to delete the
cache.xml to get the server to regenerate the directory information.

####Adding Image Descriptions
Image descriptions are dynamically retrieved from the JPEG Comment header in the file if it is set.

####Adding Folder Descriptions
Folder descriptions are dynamically retrieved from a `description.txt` file in a given directory .

####Overiding Dynamically Generated thumbnails
NeueGal will generate and cache thumbnails automatically. If you would like to provide a custom thumbnail simply upload it
with the same filename as it's full size companion into the `neuegal/thumbs/` directory,

Settings
------

Within the `neuegal` directory of your chosen gallery directory, you'll find `settings.php`. Here you can configure a handful
of settings, which are listed below. Any setting can be set in either the root `settings.php` or a settings file at the
root of a theme (though some are more useful in one place than the other). All of these functions 
are part of the NeueGal class under the settings object and can be accessed in theme files with 
`$this->settings` as a prefix.

###General
<table>
  <tr>
    <td>`site_name`</td><td>Name of site</td>
  </tr>
  <tr>
    <td>`theme`</td><td>Folder name of the theme to use</td>
  </tr>
  <tr>
    <td>`thumb_file_ext`</td><td>File extention of manually created and uploaded thumbnails</td>
  </tr>
  <tr>
    <td>`thumb_folder_show_thumbs`</td><td>Show a thumbnail for folders</td>
  </tr>
  <tr>
    <td>`thumb_folder_shuffle`</td><td>Boolean. Whether folder thumbnails should randomized or not</td>
  </tr>
  <tr>
    <td>`thumb_size`</td><td>Max width or height of generated thumbnail</td>
  </tr>
  <tr>
    <td>`thumb_folder_use_cache_only`</td><td>Force cache data only, if no cache exists then no thumbnails are shown for the folder (Can drastically improve performance on large folders)</td>
  </tr>
</table>

###Advanced
<table>
  <tr>
    <td>`debug_mode`</td><td>Boolean. Display errors and notices</td>
  </tr>
  <tr>
    <td>`debug_show_all`</td><td>Boolean. Shows all variables and information regarding current page</td>
  </tr>
  <tr>
    <td>`cyrillic_support`</td><td>Boolean. Enable support for cyrillic characters in folder names. Also helps with certain symbols</td>
  </tr>
  <tr>
    <td>`use_gzip_compression`</td><td>'on' or 'off'. Enable gzip compression of html where possible</td>
  </tr>
  <tr>
    <td>`gzip_compression_level`</td><td>0 to 9 (9 being most compression)</td>
  </tr>
  <tr>
    <td>`use_gd`</td><td>Boolean. Enable GD thumbnail creation (dynamic thumbnails)</td>
  </tr>
  <tr>
    <td>`use_gd_cache`</td><td>Boolean. Cache thumbnails so they aren't recreated on every page load</td>
  </tr>
  <tr>
    <td>`jpeg_quality`</td><td>0 to 100. JPEG thumbnail quality</td>
  </tr>
  <tr>
    <td>`gd_cache_expire`</td><td>Seconds till expire</td>
  </tr>
  <tr>
    <td>`expire_file_cache`</td><td>Seconds till expire</td>
  </tr>
  <tr>
    <td>`cache_folder`</td><td>Where you want to store your cached xml and thumbnail files. Relative to NeueGal install folder</td>
  </tr>
  <tr>
    <td>`thumbs_folder`</td><td>Where you want to store your non GD thumbnails. Relative to NeueGal install folder</td>
  </tr>
</table>

Theming
------

NeueGal features a dead simple theming system that should make 95% of what you need to do very simple. Several functions
exist that accept a string with placeholders as a parameter and return that string with the relevant content inserted.
All of these functions are part of the NeueGal class and should be accesed within the
theme files with `$this->` as a prefix.

###`showGallery($folderFormat, $imageFormat)`
####`$folderFormat`
<table>
  <tr>
    <td>{{ThumbSize}}</td><td>Size of thumbnail in pixels</td>
  </tr>
  <tr>
    <td>{{FolderTitle}}</td><td>Title of folder</td>
  </tr>
  <tr>
    <td>{{Link}}</td><td>URL to the directory</td>
  </tr>
  <tr>
    <td>{{ThumbURL}}</td><td>URL to thumbnail</td>
  </tr>
  <tr>
    <td>{{ThemePath}}</td><td>Path to current theme</td>
  </tr>
  <tr>
    <td>{{Description}}</td><td>Any text in a file name `description.txt` in the directory</td>
  </tr>
</table>
####`$imageFormat`
<table>
  <tr>
    <td>{{ThumbSize}}</td><td>Size of thumbnail in pixels</td>
  </tr>
  <tr>
    <td>{{ImageTitle}}</td><td>Text taken from image file name</td>
  </tr>
  <tr>
    <td>{{Link}}</td><td>URL to the directory</td>
  </tr>
  <tr>
    <td>{{ThumbURL}}</td><td>URL to thumbnail</td>
  </tr>
  <tr>
    <td>{{ThemePath}}</td><td>Path to current theme</td>
  </tr>
  <tr>
    <td>{{Description}}</td><td>Description included in the JPEG Comment header for image.</td>
  </tr>
</table>

###`showLoadInfo($loadInfoFormat)`
<table>
  <tr>
    <td>{{Version}}</td><td>Version of the NeueGal script running</td>
  </tr>
  <tr>
    <td>{{LoadTime}}</td><td>Duration of script execution in seconds</td>
  </tr>
</table>

###`showTitle($titleFormat)`
<table>
  <tr>
    <td>{{SiteName}}</td><td>Text specified in `settings.php`</td>
  </tr>
  <tr>
    <td>{{PageTitle}}</td><td>Name of current directory</td>
  </tr>
</table>
###`showError()`
Just outputs error string.
###`showError()`
Just outputs link to the parent directory.

Issues
------

Have a bug? Please create an issue on GitHub at https://github.com/nickswalker/neuegal/issues