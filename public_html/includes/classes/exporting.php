<?php

class exporting {

	private $exportDirs = array();

	public function __construct($dir) {

		if (!is_readable($dir)) {
			return FALSE;
		}

		$dir = opendir($dir); // open the cwd..also do an err check.
		while(false != ($file = readdir($dir))) {
			if(($file != ".") && ($file != "..") && ($file != "index.php")) {
                $this->exportDirs[] = array('file' => $file, 'name' => str_replace("_"," ",basename($file,".php")));
            }   
        }
        closedir($dir);

  
	}

	public function showExportListing() {

		$output = "<ul>";
		foreach ($this->exportDirs as $dir) {
			$output .= sprintf('<li><a href="'.$dir['file'].'">'.$dir['name'].'</a></li>');
		}
		$output .= "</ul>";

		return $output;

	}

}

?>