<?php
/* General */

$this->settings['general']['page_title_format'] = '{{P}} | {{S}}'; //Title format ([S] = Site name, [P] = Page title).

$this->settings['general']['thumb_size']= 175 ; //Thumbnail will not exceed this value in either width or height (pixels).


$this->settings['general']['thumb_folder_show_thumbs'] = true; //Show images from within the folder as the folder's thumbnail (Can drastically decrease performance if there are a lot of folders (or images within folders) and file cache is turned off).
$this->settings['general']['thumb_folder_shuffle'] = false; //Shuffle thumbnails for folder (Requires ['general']['thumb_folder_show_thumbs'] to be set to true).
$this->settings['general']['thumb_folder_use_cache_only'] = false; //Force cache data only, if no cache exists then no thumbnails are shown for the folder (Can drastically improve performance on large folders) (Ignores cache expire setting).


/* Advanced */



/* Specific Theme Settings */




?>