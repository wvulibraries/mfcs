<?php

class exporting {

	private $exportDirs = array();

	private $skipDirs   = array(".","..","index.php","lost+found",".DS_Store");

	public function __construct($dir) {

		if (!is_readable($dir)) {
			return FALSE;
		}

		$dir = opendir($dir); // open the cwd..also do an err check.
		while(false != ($file = readdir($dir))) {
			if(!in_array($file,$this->skipDirs)) {
                $this->exportDirs[] = array('file' => $file, 'name' => str_replace("_"," ",basename($file,".php")));
            }   
        }
        closedir($dir);

        sort($this->exportDirs);
  
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