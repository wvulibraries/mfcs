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
                $statFiles[] = array('file' => $file, 'name' => str_replace("_"," ",basename($file,".php")));
            }   
        }
        closedir($dir);

  
	}


	public function showStatFiles() {
		print "<pre>";
		var_dump($this->statFiles);
		print "</pre>";
	}
}
?>