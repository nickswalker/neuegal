<!DOCTYPE html>
<html>
<head>
	<meta charset= "UTF-8">
	<title><?php if($dir['current']!=''){ 
				echo $vars['general']['current_folder_name'] . ' | '. $settings['general']['site_name'];
				} 
			else{ 
				echo $settings['general']['site_name'];
				} ?></title>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.css" />
	<link rel="stylesheet"  href="<?php echo $this->getThemeURL();?>style.css" media="all" />
	<?php echoIfExists('custom-style.css','<link rel="stylesheet" href="custom-style.css">');?>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.js"></script>


	<script type="text/javascript">
		$(document).ready(function() {
			$('a').fancybox({
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
			<h1><?php echo $settings['general']['site_name']; ?></h1>
		</header>
		<ul class="gallery">
			<?php  $this->showGallery(); ?>
		</ul>
	</div>
</body>
</html>