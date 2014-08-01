<?php
/* General */
$this->settings['general']['site_name'] = 'NeueGal';
$this->settings['general']['theme'] = 'default';

$this->settings['general']['thumbnail_size'] = 300; //In pixels. Should usually be set by the theme

$this->settings['general']['folder_thumbnails'] = true;
$this->settings['general']['random_folder_thumbnail'] = false;


/* Theme*/

$this->settings['theme']['image'] = '
<li>
	<a title="{{Title}} {{Description}}" class="thumb-container" href="{{Path}}" style="width:{{ThumbSize}}; height:{{ThumbSize}};">
		<img src="{{ThumbPath}}" alt="{{Title}}" />
	</a>
</li>
';
$this->settings['theme']['folder'] = '
<li class="folder">
	<a href="{{Path}}" style="width: {{ThumbSize}}; height: {{ThumbSize}};">
		<img src="{{ThumbPath}}" alt="{{Title}}"/>
	</a>
</li>';

/* Advanced */

$this->settings['advanced']['debug_mode'] = false;
$this->settings['advanced']['debug_show_all'] = false;

$this->settings['advanced']['jpeg_quality'] = 75; //0-100
$this->settings['advanced']['thumbnail_expire'] = 172800; //Seconds
$this->settings['advanced']['cache_expire'] = 86400; //Seconds
$this->settings['advanced']['custom_thumbnails_path'] = 'neuegal/custom-thumbs'; //Relative from index.php
