<?php

/**
 * Main MFCS object
 */
class mfcs {
    const AUTH_NONE  = 0;
    const AUTH_VIEW  = 1;
    const AUTH_ENTRY = 2;
    const AUTH_ADMIN = 3; // Bitwise of all auth's

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

    

    /**
     * Multi dimensional array to house various Caches that we will be using.
     * @var array
     */
    private $cache         = array();

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
        users::processUser();

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

    // if $increment is true it returns the NEXT number. if it is false it returns the current
    public static function getIDNO($formID,$increment=TRUE) {

        $engine         = self::$engine;
        $idno           = forms::getFormIDInfo($formID);

        $sqlResult = $engine->openDB->query(
            sprintf("SELECT `count` FROM `forms` WHERE `ID`='%s'",
                $engine->openDB->escape($formID)
                )
            );

        if (!$sqlResult['result']) {
            return FALSE;
        }

        $idno                 = $idno['idnoFormat'];
        $len                  = strrpos($idno,"#") - strpos($idno,"#") + 1;
        $sqlResult['numrows'] = str_pad($sqlResult['numrows'],$len,"0",STR_PAD_LEFT);
        $idno                 = preg_replace("/#+/", $sqlResult['result']['count'], $idno);

        return $idno;
    }

    /**
     * Object cache manager
     *
     * This function identifies cache by the class by default. So each class gets 1 cache. If you
     * need more than 1 cache per class that should be handled internal to the calling class using
     * the $cachID to distinguish.
     *
     *
     * @param string $action create, update, delete, or get
     * @param $cacheID
     *        How the calling method/fucntion identifies the cache.<br>
     *        If the calling function or class will be using multiple<br>
     *        caches it should add cache name information to this as well.
     * @param mixed $value
     *        The value to be stored. (required for everything except "get")
     * @return bool
     */
    public function cache($action,$cacheID,$value=NULL) {

        // for security we have to determine the function ID ourselves.
        // otherwise a malicious module/object author could overwrite the permissions cache
        $trace  =debug_backtrace();
        $caller = $trace[1];

        $functionID = (isset($caller['class']))?$caller['class']:$caller['function'];

        if ($action == "create") {

            if (isnull($value)) {
                errorHandle::newError(__METHOD__."() - value not provided.", errorHandle::DEBUG);
                return(FALSE);
            }

            if (isset($this->cache[$functionID][$cacheID])) {
                errorHandle::newError(__METHOD__."() - cachID found. use update", errorHandle::DEBUG);
                return(FALSE);
            }

            $this->cache[$functionID][$cacheID] = $value;

        }
        else if ($action == "update") {

            if (isnull($value)) {
                errorHandle::newError(__METHOD__."() - value not provided.", errorHandle::DEBUG);
                return(FALSE);
            }

            if (!isset($this->cache[$functionID][$cacheID])) {
                errorHandle::newError(__METHOD__."() - cachID not found. use create", errorHandle::DEBUG);
                return(FALSE);
            }

            $this->cache[$functionID][$cacheID] = $value;

        }
        else if ($action == "delete") {

            if (isnull($value)) {
                errorHandle::newError(__METHOD__."() - value not provided.", errorHandle::DEBUG);
                return(FALSE);
            }

            if (!isset($this->cache[$functionID][$cacheID])) {
                errorHandle::newError(__METHOD__."() - cachID not found. use create", errorHandle::DEBUG);
                return(FALSE);
            }

            unset($this->cache[$functionID][$cacheID]);

        }
        else if ($action == "get") {

            if (isset($this->cache[$functionID][$cacheID])) {
                return($this->cache[$functionID][$cacheID]);
            }

            return(NULL);

        }
        else {
            errorHandle::newError(__METHOD__."() - Action '".$action."' not allowed.", errorHandle::DEBUG);
            return(FALSE);
        }

        return(TRUE);

    }

}