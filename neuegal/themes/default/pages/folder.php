<div class="page-bar">
	<?php 
	if( $this->vars['dir_req_current'] != ''){
		echo '<a href="?'. $this->vars['dir_parent'] .'" class="up">^</a>';
	}
	?>
	<h2><?php $this->showPage($this->settings['general']['page_title_format']); ?></h2>
</div>
<?php $image = '
<figure class="gallery-entry">
	<a class="thumb-container fancybox" data-fancybox-group="gallery" href="{{Link}}" style="width:   {{Thumb-size}}; height:   {{Thumb-size}};">
		<img src="{{Thumb-URL}}" alt="{{Image-Title}}" />
	</a>
</figure>
';
$folder = '
<figure class="gallery-entry folder">
	<a href="{{Link}}" style="width:   {{Thumb-size}}; height:   {{Thumb-size}};">
		<img src="{{Thumb-URL}}" alt="{{Folder-Title}}"/>
	</a>
	<span>{{Folder-Title}}</span>
</figure>';
if(isset($this->$dir['description'])) {
	echo $this->$dir['description'];
	}
$this->showGallery($folder,$image); ?>
