#!/usr/bin/php
<?php
$config = parse_ini_file(__DIR__.DIRECTORY_SEPARATOR."config.ini");

# Functions
####################################################################
/**
 * Recursively delete a directory
 * @see http://www.php.net/manual/en/function.rmdir.php#108113
 * @param string $dir
 */
function rrmdir($dir) {
	foreach(glob($dir.DIRECTORY_SEPARATOR.'*') as $file) {
		// Never follow links, as we don't know where they will end up
		if(is_link($file)) continue;
		if(is_dir($file)) rrmdir($file);
		else unlink($file);
	}
	// Try and delete the directory. (We suppress errors as there might be a symlink, and this will fail)
	@rmdir($dir);
}

# Delete old, abandoned, upload directories
####################################################################
$uploadPath  = $config['uploadPath'];
$scanPattern = $config['uploadPath'].DIRECTORY_SEPARATOR.'*';

// Sanity check to make sure it's safe to proceed
if(!empty($uploadPath) and strpos($scanPattern,'/home/') === 0){
	// We're cleared to launch!
	foreach(glob($scanPattern) as $uploadDir){
		// Skip hidden stuff
		if($uploadDir[0] == '.') continue;
		// If the modified time is older then uploadMaxAge DELETE it
		$mTime = filemtime($uploadDir);
		if(($mTime+$config['uploadMaxAge']) <= time()) rrmdir($uploadDir);
	}
}else{
	// Woah, something's not right!
	trigger_error("Fetal Error - We were about to delete the world! (scan pattern: $scanPattern)", E_USER_ERRORS);
}
?>
