<?php

/**
 * Main MFCS object
 */
class mfcs {
    /**
     * Instance of self
     * @var self
     */
    private static $instance;

    /**
     * @var EngineAPI
     */
    public static $engine;

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

    private static $user = array();

    /**
     * Class constructor
     *
     * @author David Gersting
     * @param string $configFile The config file to load
     */
    private function __construct($configFile){
        self::$engine   = EngineAPI::singleton();
        self::$mfcsRoot = realpath(__DIR__.'/..');

        // Load config file
        if(is_null($configFile)) $configFile = self::$mfcsRoot.'/config.ini';
        if(is_readable($configFile)){
            self::$config = parse_ini_file($configFile);
        }

        // Process the logged in user
        $username  = sessionGet('username');
        $sqlSelect = sprintf("SELECT * FROM users WHERE username='%s' LIMIT 1", self::$engine->openDB->escape($username));
        $sqlResult = self::$engine->openDB->query($sqlSelect);
        if(!$sqlResult['result']){
            errorHandle::newError(__METHOD__."() - Failed to lookup user ({$sqlResult['error']})", errorHandle::HIGH);
        }else{
            if(!$sqlResult['numRows']){
                // No user found, add them!
                $sqlInsert = sprintf("INSERT INTO users (username) VALUES('%s')", self::$engine->openDB->escape($username));
                $sqlResult = self::$engine->openDB->query($sqlInsert);
                if(!$sqlResult['result']){
                    errorHandle::newError(__METHOD__."() - Failed to insert new user ({$sqlResult['error']})", errorHandle::DEBUG);
                }else{
                    $sqlResult = self::$engine->openDB->query($sqlSelect);
                    self::$user = mysql_fetch_assoc($sqlResult['result']);
                }
            }else{
                self::$user = mysql_fetch_assoc($sqlResult['result']);
            }

        }
    }

    /**
     * Returns an instance of mfcs (singleton pattern)
     *
     * @author David Gersting
     * @param string $configFile The config file to load (Default: config.ini)
     * @return mfcs
     */
    public static function singleton($configFile=NULL){
        if(!isset(self::$instance)) self::$instance = new self($configFile);
        return self::$instance;
    }

    /**
     * Get a config item
     *
     * @author David Gersting
     * @param string $name The name of the config item
     * @param mixed $default If no config item found, return this
     * @return mixed
     */
    public static function config($name,$default=NULL){
        return isset(self::$config[$name])
            ? self::$config[$name]
            : $default;
    }

    /**
     * Get a user field
     *
     * @author David Gersting
     * @param string $name The name of the user field
     * @param mixed $default If no field value found, return this
     * @return mixed
     */
    public static function user($name,$default=NULL){
        return (isset(self::$user[$name]) and !empty(self::$user[$name]))
            ? self::$user[$name]
            : $default;
    }

    /**
     * Get an array of available projects
     *
     * This method returns an array of all available projects from the database
     *
     * @author David Gersting
     * @param string|array $fields An array or CSV of fields to include
     * @param string $orderBy
     * @return array
     */
    public static function getProjects($fields='ID,projectName',$orderBy='projectName ASC'){
        // Clean and process $fields
        $fields = is_string($fields) ? explode(',', $fields) : $fields;
        foreach($fields as $k => $field){
            $fields[$k] = '`'.self::$engine->openDB->escape($field).'`';
        }

        // Clean and process $orderBy
        $orderBy = !is_empty($orderBy) ? "ORDER BY ".self::$engine->openDB->escape($orderBy) : '';

        // Build SQL
        $sql = sprintf('SELECT %s FROM `projects` %s',
            implode(',', $fields),
            $orderBy);
        $sqlResult = self::$engine->openDB->query($sql);
        if(!$sqlResult['result']){
            errorHandle::newError(__METHOD__."() - MySQL Error ".$sqlResult['error'], errorHandle::DEBUG);
            return array();
        }

        $results = array();
        while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)){
            $results[] = $row;
        }
        return $results;
    }

}