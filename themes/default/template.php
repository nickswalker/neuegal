<!DOCTYPE html>
<html>
<head>
	<meta charset= "UTF-8">
	<meta name=viewport content="width=device-width, initial-scale=1">
	<title><?php if($vars['current_folder_name']!=''){
				echo $vars['current_folder_name'] . ' | '. $settings['site_name'];
				}
			else{
				echo $settings['site_name'];
				} ?></title>
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:300,300italic,700' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.css" />
	<?php if( is_file($this->photosPathFromRoot.'custom-style.css') ){
		echo '<link rel="stylesheet"  href="'.  $this->getPhotosURL() . 'custom-style.css" media="all" />';
	}?>

	<link rel="stylesheet"  href="<?php echo $this->getThemeURL();?>style.css" media="all" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.js"></script>


	<script type="text/javascript">
		$(document).ready(function() {
			$('a').fancybox({
				afterLoad: function() {
					var description = $(this.element).attr('data-description');
					if (description != ''){
						this.title = this.title + " – " + $(this.element).attr('data-description');
					}
				},
				padding : 0,
				openEffect: 'fade',
				closeEffect: 'fade',
				prevEffect: 'fade',
				nextEffect: 'fade',
				loop: false,
				closeBtn: false,

			});
		});
	</script>

</head>
<body>
	<div id="content">
		<header>
			<h1><a href="<?php echo $vars['gallery_url'];?>"><?php echo $settings['site_name']; ?></a></h1>
			<h2 class="folder-title"><?php if( isset( $vars['current_folder_name']) ){
				echo ("| " . $vars['current_folder_name']);
			} ?></h2>
			<span class="description"><?php echo $vars['description'];?></span>
		</header>
		<ul class="gallery">
			<?php  $this->showGallery(); ?>
		</ul>
	</div>
</body>
</html>