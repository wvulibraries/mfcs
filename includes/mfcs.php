<?php

/**
 * Main MFCS object
 * @author David Gersting
 */
class mfcs {
    /**
     * Instance of self
     * @var self
     */
    private static $instance;

    /**
     * The root path of the mfcs system
     * @var string
     */
    private static $mfcsRoot;

    /**
     * MFCS config items
     * @var array
     */
    private static $config = array();

    /**
     * Class constructor
     * @param string $configFile The config file to load
     */
    private function __construct($configFile){
        self::$mfcsRoot = realpath(__DIR__.'/..');

        if(is_null($configFile)) $configFile = self::$mfcsRoot.'/config.ini';
        if(is_readable($configFile)){
            self::$config = parse_ini_file($configFile);
        }
    }

    /**
     * Returns an instance of mfcs (singleton pattern)
     * @param string $configFile The config file to load (Default: config.ini)
     * @return mfcs
     */
    public static function singleton($configFile=NULL){
        if(!isset(self::$instance)) self::$instance = new self($configFile);
        return self::$instance;
    }

    /**
     * Get a config item
     * @param string $name The name of the config item
     * @param mixed $default If no config item found, return this
     * @return mixed
     */
    public static function config($name,$default=NULL){
        return isset(self::$config[$name])
            ? self::$config[$name]
            : $default;
    }

}