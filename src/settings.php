<?php
$imageFormat = '
<li>
	<a title="{{Title}} {{Description}}" class="thumb-container" href="{{Path}}" >
		<img src="{{ThumbPath}}" alt="{{Title}}" />
	</a>
</li>
';
$folderFormat = '
<li class="folder">
	<a href="{{Path}}">
		<img src="{{ThumbPath}}" alt="{{Title}}"/>
	</a>
</li>';

return array(
/* General */
	'site_name' => 'NeueGal',
	'thumbnail_size' => 300, //In pixels. Should usually be set by the theme
	'folder_thumbnails' => true,
	'random_folder_thumbnail' => false,


/* Theme*/

	'theme' => array(
		'imageFormat' => $imageFormat,
		'folderFormat' => $folderFormat
		),

/* Advanced */
	'advanced' => array(
		'debug' => false,
		'jpeg_quality' => 90,
		'cache_expire' => 86400,
		'custom_thumbnails_path' => 'thumbs'
	)

);