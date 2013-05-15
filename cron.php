#!/usr/bin/php
<?php
$config = parse_ini_file(__DIR__.DIRECTORY_SEPARATOR."config.ini");

# Delete old, abandoned, upload directories
####################################################################
$uploadPath = $config['uploadPath'];
$uploadDirs = scandir($uploadPath);
foreach($uploadDirs as $uploadDir){
	// Skip hidden stuff
	if($uploadDir{0} == '.') continue;

	$uploadDir = $uploadPath.DIRECTORY_SEPARATOR.$uploadDir;
	$mTime     = filemtime($uploadDir);
	if(($mTime+$config['uploadPath']) <= time()){
		shell_exec("sudo rm -rf $uploadDir");
	}
}
?>
