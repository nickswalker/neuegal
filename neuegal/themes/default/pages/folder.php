<div class="page-bar">
	<?php 
	if( $this->vars['dir_req_current'] != ''){
		echo '<a href="?'. $this->vars['dir_parent'] .'" class="up">^</a>';
	}
	?>
	<h2><?php if($this->vars['dir_req']!=''){ 
				echo $this->vars['page_title'];
				} 
			else{ 
				echo $this->settings['general']['site_name'];
				} ?>
	</h2>
	<?php if(isset($this->vars['description'])){
		echo('<span class="description">'. $this->vars['description'] .'</span>');

	} ?>
</div>
<?php $image = '
<figure class="gallery-entry">
	<a title="{{ImageTitle}} {{Description}}" class="thumb-container fancybox" data-fancybox-group="gallery" href="{{Link}}" style="width:   {{ThumbSize}}; height:   {{ThumbSize}};">
		<img src="{{ThumbURL}}" alt="{{ImageTitle}}" />
	</a>
</figure>
';
$folder = '
<figure class="gallery-entry folder">
	<a href="{{Link}}" style="width:   {{ThumbSize}}; height:   {{ThumbSize}};">
		<img src="{{ThumbURL}}" alt="{{FolderTitle}}"/>
	</a>
</figure>';
echo $dir['description'];
$this->showGallery($folder,$image); ?>
