<div class="page-bar">
<?php 
	var_dump($this->settings);
	if( $dir['req_current'] != ''){
		echo '<a href="?'. $dir['parent'] .'" class="up">^</a>';
	}
	?>
	<h2><?php if($dir['request']!=''){ 
				echo $vars['page_title'];
				} 
			else{ 
				echo $settings['general']['site_name'];
				} ?>
	</h2>
	<?php if(isset($vars['description'])){
		echo('<span class="description">'. $vars['description'] .'</span>');

	} ?>
	
	
	<?php if(isset($settings['theme']['info_link'])){
		echo('<a class="info-link" href="'. $settings['theme']['info_link'] .'">?</a>');
	} ?>
</div>
<?php 
echo issetor($dir['description']);
$this->showGallery(); ?>
