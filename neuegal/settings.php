<?php
/* General */
$this->settings['general']['site_name'] = 'NeueGal';
$this->settings['general']['theme'] = 'default';

$this->settings['general']['thumb_size'] = 175;

$this->settings['general']['thumb_file_ext'] = 'jpg';
$this->settings['general']['thumb_folder_show_thumbs'] = true;
$this->settings['general']['thumb_folder_shuffle'] = false;
$this->settings['general']['thumb_folder_use_cache_only'] = false;


/* Advanced */

$this->settings['advanced']['debug_mode'] = false;
$this->settings['advanced']['debug_show_all'] = false;

$this->settings['advanced']['cyrillic_support'] = true;

$this->settings['advanced']['use_gzip_compression'] = 'on';
$this->settings['advanced']['gzip_compression_level'] = 1;

$this->settings['advanced']['use_gd'] = true;
$this->settings['advanced']['use_gd_cache'] = true;
$this->settings['advanced']['jpeg_quality'] = 75;
$this->settings['advanced']['gd_cache_expire'] = 172800;
$this->settings['advanced']['expire_file_cache'] = 86400; 
$this->settings['advanced']['cache_folder'] = 'neuegal/cache';
$this->settings['advanced']['thumbs_folder'] = 'neuegal/thumbs';


?>