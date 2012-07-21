<?php
/* General */

$this->settings['general']['site_name'] = 'NeueGal';

$this->settings['general']['theme'] = 'default'; //Folder name of theme to use.


$this->settings['general']['thumb_file_ext'] = 'jpg'; //File extention of non GD thumbnails.
$this->settings['general']['thumb_folder_show_thumbs'] = true; //Show images from within the folder as the folder's thumbnail (Can drastically decrease performance if there are a lot of folders (or images within folders) and file cache is turned off).
$this->settings['general']['thumb_folder_shuffle'] = false; //Shuffle thumbnails for folder (Requires ['general']['thumb_folder_show_thumbs'] to be set to true).
$this->settings['general']['thumb_folder_use_cache_only'] = false; //Force cache data only, if no cache exists then no thumbnails are shown for the folder (Can drastically improve performance on large folders) (Ignores cache expire setting).


/* Advanced */

$this->settings['advanced']['debug_mode'] = false; //Enable if having issues with NeueGal so you can report the exact error you are getting.
$this->settings['advanced']['debug_show_all'] = true; //Shows all information regarding the current page.

$this->settings['advanced']['cyrillic_support'] = true; //Enable support for cyrillic characters in folder names. Also helps with certain symbols.

$this->settings['advanced']['allow_theme_settings'] = true; //Allow theme settings to override your own.

$this->settings['advanced']['use_gzip_compression'] = 'on'; //Enable gzip compression of html where possible ('on' or 'off')
$this->settings['advanced']['gzip_compression_level'] = 1; //0 to 9 (9 being most compression).

$this->settings['advanced']['use_gd'] = true; //Enable GD thumbnail creation (dynamic thumbnails).
$this->settings['advanced']['use_gd_cache'] = true; //Cache thumbnails so they aren't recreated on every page load.
$this->settings['advanced']['jpeg_quality'] = 75; //Jpeg thumbnail quality (0 to 100)
$this->settings['advanced']['gd_cache_expire'] = 172800; //Seconds till expire (Default: 2 days)
$this->settings['advanced']['expire_file_cache'] = 86400; //Seconds till expire (Default: 1 day)
$this->settings['advanced']['cache_folder'] = 'neuegal/cache'; //Where you want to store your cached xml and thumbnail files. Relative to NeueGal install folder. Web server user must have write permissions.
$this->settings['advanced']['thumbs_folder'] = 'neuegal/thumbs'; //Where you want to store your non GD thumbnails. Relative to NeueGal install folder.


?>