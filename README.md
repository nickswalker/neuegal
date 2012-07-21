NeueGal
=======

A simple, no-database PHP image gallery. Neuegal is a fork of the fantastic PHPPI which aims to make it easy to create
simple galleries for photography portfolios or other purposes. Think of it as a bare bones Apache index for photos, except
it looks great out of the box and has simple to use theming support so you can integrate it with your existing designs.\

More information and examples: http://nickswalker.github.com/neuegal

How To Install
------

Just unzip the download into any directory on a PHP 5.2 or above enabled server and you'll have a working gallery that will
display the included directory of sample images. Simply add images or folders to the directory and your gallery will update
to display them just like you'd imagine. Note however that directory information is cached and you may have to delete the
cache.xml to get the server to regenerate the directory information.

Setup
------

Within the neuegal directory of your chosen gallery directory, you'll find settings.php here, you can configure a handful
of settings, which are listed below.

Theming
------

NeueGal features a dead simple theming system that should make 95% of what you need to do very simple. Several functions
exist that accept a string as a parameter and return 


Issues
------

Have a bug? Please create an issue on GitHub at https://github.com/nickswalker/neuegal/issues