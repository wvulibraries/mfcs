<?php

/**
 * queryHelper.php
 * @author Tracy A. McCormick
 * @created 2022-12-08
 * @version 1.0
 * @package envHelper
 * description: extract variables from /etc/.env file
 * and return the value of the variable requested
 */

class envHelper {

    private $envfile;

    public function __construct() {
        $this->envfile = "/etc/.env";
    }   

    public function getAppEnv($findKey) {
        $lines = file($this->envfile);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, "#") === 0) {
                continue;
            }
            $parts = explode("=", $line);
            $key = $parts[0];
            $value = $parts[1];
            if ($findKey == $key) {
                return $value;
            }
        }
        return NULL;
    }    
}

?>