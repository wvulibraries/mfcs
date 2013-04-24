<?php

class mfcsStats {
	
	private $statFiles = array();

	public function __construct($dir) {

		if (!is_readable($dir)) {
			return FALSE;
		}

		$dir = opendir($dir); // open the cwd..also do an err check.
		while(false != ($file = readdir($dir))) {
			if(($file != ".") && ($file != "..") && ($file != "index.php")) {
                $this->statFiles[] = array('file' => $file, 'name' => str_replace("_"," ",basename($file,".php")));
            }   
        }
        closedir($dir);

  
	}


	public function showStatFiles() {

		$output = "<ul>";
		foreach ($this->statFiles as $file) {
			$output .= sprintf('<li><a href="'.$file['file'].'">'.$file['name'].'</a></li>');
		}
		$output .= "</ul>";

		return $output;
	}
}
?>