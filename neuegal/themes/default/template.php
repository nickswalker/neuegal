<!DOCTYPE html>
<html>
<head>

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="stylesheet" href="neuegal/scripts/fancybox/jquery.fancybox.css">

<?php
	echo "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js\"></script>\n";
	echo "<link rel=\"stylesheet\" href=\"" . $this->showThemeURL(1) . "gallery.css\">\n";
?>
<script src="neuegal/scripts/fancybox/jquery.fancybox.js"></script>
	

<script type="text/javascript">
	$(document).ready(function() {
		$('.fancybox').fancybox({
			padding : 0,
			openEffect: 'fade',
			closeEffect: 'fade',
			prevEffect: 'fade',
			nextEffect: 'fade',
			loop: false,
			closeBtn: false,
			helpers : {
            	title : null            
            }      
		});
	});
</script>
<link rel="stylesheet" href="/style.css" />
<title><?php if($this->vars['dir_req']!=''){ 
				echo $this->vars['page_title'] . ' | '. $this->settings['general']['site_name'];
				} 
			else{ 
				echo $this->settings['general']['site_name'];
				} ?></title>
</head>
<body>
<div id="content">
<?php $this->showPage(); ?>
</div>
</body>
</html>