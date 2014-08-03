<?php

$imageFormat = <<<EOD
<li>
	<a title="{{Title}}" data-description="{{Description}}" href="{{Path}}" style="background-image:url('{{ThumbPath}}');">
	</a>
</li>
EOD;
$folderFormat = <<<EOD
<li class="folder">
	<a href="{{Path}}" style="background-image:url('{{ThumbPath}}');">
	</a>
</li>
EOD;

return array(
/* General */
	'site_name' => 'My Gallery',
	'thumb_size'=> 300,
	'thumb_folder_show_thumbs' => true,
	'thumb_folder_shuffle' => false,

/* Specific Theme Settings */

	'theme' => array(
		'info_link' => 'http://nickswalker.github.com/neuegal/',
		'imageFormat' => $imageFormat,
		'folderFormat' => $folderFormat
		),
	'advanced' => array(
		'debug' => false,
		'jpeg_quality' => 90
	)

);
