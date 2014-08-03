<?php

require 'vendor/autoload.php'; //Don't try to make these paths absolute (begin with a /) unless you know what you're doing.

$themePathFromRoot = realpath('themes/default'); //Where is your theme?
												 //Note that the theme MUST be in a publicly accesible directory!
												 //Otherwise your CSS won't load :(
												 
$photosPathFromRoot = realpath('Sample Gallery'); //Where are the photos you want to use?


$neuegal = new \Nickswalker\NeueGal\NeueGal( $themePathFromRoot, $photosPathFromRoot);

$neuegal->display();