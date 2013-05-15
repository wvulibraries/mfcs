#!/usr/bin/php
<?php
$config = parse_ini_file(__DIR__.DIRECTORY_SEPARATOR."config.ini");

# Delete old, abandoned, upload directories
####################################################################
$uploadPath = $config['uploadPath'];
$uploadDirs = scandir($uploadPath);
foreach($uploadDirs as $uploadDir){
	// Skip hidden stuff
	if($uploadDir[0] == '.') continue;

	$uploadDir = implode(DIRECTORY_SEPARATOR,array($uploadPath,$uploadDir);
	$mTime     = filemtime($uploadDir);
	if(($mTime+$config['uploadPath']) <= time()){
		// @TODO apache cannot use 'sudo' for security reasons
		// @TODO we do not use rm -rf in an automated script, o the chance that something goes wrong. 
		//  These files will need to be unlinked using php functions
		//  There should be a sanity check to make sure we are not deleting starting at / or in the archives directory by accident. 
		// shell_exec("sudo rm -rf $uploadDir");
	}
}
?>
