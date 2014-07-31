<?php
/* General */
$this->settings['general']['site_name'] = 'NeueGal';
$this->settings['general']['theme'] = 'default';

$this->settings['general']['thumb_size'] = 175;

$this->settings['general']['thumb_file_ext'] = 'jpg';
$this->settings['general']['thumb_folder_show_thumbs'] = true;
$this->settings['general']['thumb_folder_shuffle'] = false;


/* Theme*/

$this->settings['theme']['image'] = '
<figure class="gallery-entry">
	<a title="{{Title}} {{Description}}" class="thumb-container" href="{{Path}}" style="width:{{ThumbSize}}; height:{{ThumbSize}};">
		<img src="{{ThumbPath}}" alt="{{Title}}" />
	</a>
</figure>
';
$this->settings['theme']['folder'] = '
<figure class="gallery-entry folder">
	<a href="{{Path}}" style="width: {{ThumbSize}}; height: {{ThumbSize}};">
		<img src="{{ThumbPath}}" alt="{{Title}}"/>
	</a>
</figure>';

/* Advanced */

$this->settings['advanced']['debug_mode'] = false;
$this->settings['advanced']['debug_show_all'] = false;
 

$this->settings['advanced']['use_gzip_compression'] = 'on';
$this->settings['advanced']['gzip_compression_level'] = 1;

$this->settings['advanced']['use_gd'] = true;
$this->settings['advanced']['use_gd_cache'] = true;
$this->settings['advanced']['jpeg_quality'] = 75;
$this->settings['advanced']['gd_cache_expire'] = 172800;
$this->settings['advanced']['expire_file_cache'] = 86400; 
$this->settings['advanced']['cache_folder'] = 'neuegal/cache'; //Relative from index.php
$this->settings['advanced']['thumbs_folder'] = 'neuegal/thumbs'; //Relative from index.php
