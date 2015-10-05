<?php
// David Mongeau-Petitpas
// dmongeau
// https://github.com/dmongeau
class FFMPEG {

    protected static $_config = array(
            'bin'         => 'ffmpeg',
            'optionsWord' => array(
            'bitrate'     => 'b',
            'duration'    => 't',
            'start'       => 'ss',
            'size'        => 's',
            'overwrite'   => 'y'
        )
    );

    protected $_inputFile;
    protected $_options = array('main'=>array(), 'in'=>array(), 'out'=>array());
    protected $_lastCommand;
    protected $_lastOutput;
    protected $_lastReturn;


    public function __construct($inputFile = null, $options = array()) {

        if(isset($inputFile)) $this->setInputFile($inputFile);

        if(sizeof($options)) $this->setOptions($options);

    }

    public function isValid() {
        $metadata = $this->getMetadata();
        if(sizeof($metadata) && isset($metadata['format']) && (int)$metadata['duration'] > 0) return true;
        return false;
    }

    public function isVideo() {
        $metadata = $this->getMetadata();
        if(sizeof($metadata) && isset($metadata['media']) && $metadata['media'] == 'video') return true;
        return false;
    }

    public function isAudio() {
        $metadata = $this->getMetadata();
        if(sizeof($metadata) && isset($metadata['media']) && $metadata['media'] == 'audio') return true;
        return false;
    }

    public function getMetadata() {

        $output = $this->_exec('-i '.escapeshellarg($this->getInputFile()));

        $metadata = array();
        $metaHeader = false;
        foreach($output as $line) {
            if(preg_match('/^\s{2}Metadata\:/',$line)) {
                $metaHeader = true;
            } else if($metaHeader && preg_match('/^\s{2}([A-Za-z0-9]+)\:/',$line)) {
                $metaHeader = false;
            } else if($metaHeader) {
                if(preg_match('/([A-Za-z0-9]+)\s+\: (.*)/',$line,$matches)) {
                    $key = strtolower($matches[1]);
                    if(!in_array($key,array('duration','starttime','totalduration'))) {
                        if($key == 'bytelength') $key = 'size';
                        $metadata[$key] = $matches[2];
                    }
                }
            }

            if(!$metaHeader) {
                if(preg_match('/^\s{2}Duration\: ([0-9\:\.]{11})/',$line,$matches)) {
                    $duration = explode(':',$matches[1]);
                    $metadata['duration'] = ((int)$duration[0]*3600)+((int)$duration[1]*60)+((float)$duration[2]);
                } else if(preg_match('/^Input #0, ([A-Za-z0-9\,]+) from/',$line,$matches)) {
                    $metadata['format'] = explode(',',trim(strtolower($matches[1]),','));
                    if(sizeof($metadata['format']) == 1) $metadata['format'] = $metadata['format'][0];
                } else if(preg_match('/^\s{4}Stream #0.0[^\:]*: (Video|Audio)\: (.*)/',$line,$matches)) {
                    $metadata['media'] = strtolower($matches[1]);
                    $parts = explode(',',$matches[2]);
                    if($metadata['media'] == 'video') {
                        $metadata['codec'] = strtolower(trim($parts[0]));
                        $metadata['color'] = strtolower(trim($parts[1]));
                        for($i = 2; $i < sizeof($parts); $i++) {
                            if(preg_match('/([0-9]+)x([0-9]+)/',$parts[$i],$matches)) {
                                $metadata['width'] = (int)$matches[1];
                                $metadata['height'] = (int)$matches[2];
                            } else if(preg_match('/([0-9]+) kb\/s/',$parts[$i],$matches)) {
                                $metadata['videorate'] = (int)$matches[1];
                            }
                        }
                    } else if($metadata['media'] == 'audio') {
                        $metadata['audio'] = strtolower(trim($parts[0]));
                        for($i = 1; $i < sizeof($parts); $i++) {
                            if(preg_match('/([0-9]+) Hz/',$parts[$i],$matches)) {
                                $metadata['frequency'] = (int)$matches[1];
                            } else if(preg_match('/(stereo|mono)/',$parts[$i],$matches)) {
                                $metadata['stereo'] = $matches[1] == 'stereo' ? true:false;
                            } else if(preg_match('/([0-9]+) kb\/s/',$parts[$i],$matches)) {
                                $metadata['audiorate'] = (int)$matches[1];
                            }
                        }
                    }
                }
            }
        }

        return $metadata;

    }

    public function getDuration() {

        $metadata = $this->getMetadata();

        return isset($metadata['duration']) ? $metadata['duration']:0;

    }

    public function convert($output, $optionsIn = array() , $optionsOut = array()) {

        if(sizeof($optionsIn)) $this->setOptions($optionsIn,'in');
        if(sizeof($optionsOut)) $this->setOptions($optionsOut,'out');

        $this->_execute($output);

    }

    public function getImages($path, $name = 'image%d.jpg', $optionsIn = array() , $optionsOut = array()) {

        if(sizeof($optionsIn)) $this->setOptions($optionsIn,'in');
        if(sizeof($optionsOut)) $this->setOptions($optionsOut,'out');

        $this->_execute(rtrim($path,'/').'/'.ltrim($name,'/'));

    }

    public function getThumbnail($path, $start = 'half') {

        if($start == 'half') {
            $metadata = $this->getMetadata();
            $duration = $metadata['duration'];
            $start = floor($duration/2);
        }
        else if(!is_numeric($start)) $start = 30;

        $file = basename($path);
        $path = dirname($path);

        $this->getImages($path.'/', $file, array(), array('start'=>$start));

    }


    /**
     *
     * Options
     *
     */
    public function setOptions($options,$category = 'main') {
        $this->_options[$category] = $options;
    }

    public function addOption($option, $value = null,$category = 'main') {
        $this->_options[$category][$option] = $value;
    }

    public function getOptions($category = 'main') {
        return $this->_options[$category];
    }

    public function hasOptions($category = 'main') {
        return isset($this->_options[$category]) && sizeof($this->_options[$category]) ? true:false;
    }

    protected function _prepareOptions($category = 'main') {

        $words = self::getConfig('optionsWord');
        $options = $this->getOptions($category);
        $str = array();
        $index = 0;
        foreach($options as $option => $value) {

            print "<pre>";
            var_dump($option." - ".$value);
            print "</pre>";

            if(is_numeric($option) && $option === $index) {
                $option = $value;
                $value = null;
                $index++;
            }
            $option = isset($words[$option]) ? '-'.$words[$option]:$option;
            $str[] = $option.(isset($value) ? ' '.escapeshellcmd($value):'');
        }

        return implode(' ',$str);
    }

    /**
     *
     * Last output
     *
     */
    public function getLastOutput() {
        return $this->_lastOutput;
    }

    /**
     *
     * Last command
     *
     */
    public function getLastCommand() {
        return $this->_lastCommand;
    }

    /**
     *
     * Last return
     *
     */
    public function getLastReturn() {
        return $this->_lastReturn;
    }

    /**
     *
     * Input file
     *
     */
    public function setInputFile($file) {
        $this->_inputFile = $file;
    }

    public function getInputFile() {
        return $this->_inputFile;
    }

    /**
     *
     * Execute the command line
     *
     */

    protected function _execute($output = null) {

        $parts = array();

        if($this->hasOptions('main')) $parts[] = $this->_prepareOptions('main');
        if($this->hasOptions('in')) $parts[] = $this->_prepareOptions('in');
        $parts[] = '-i '.escapeshellarg($this->getInputFile());

        if($this->hasOptions('out')) $parts[] = $this->_prepareOptions('out');
        if(isset($output)) $parts[] = escapeshellarg($output);

        $command = implode(' ',$parts);
        $this->_lastOutput = $this->_exec($command);

        return $this->_lastOutput;

    }

    protected function _exec($command) {

        $command = self::getConfig('bin').' '.trim($command).' 2>&1';

        $this->_lastCommand = $command;

        $descriptorspec = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w"),
           2 => array("file", "/tmp/error-output.txt", "a")
        );

        $cwd = '/tmp';

        $process = proc_open($command, $descriptorspec, $pipes, $cwd);

        if (is_resource($process)) {

            $output = explode("\n",stream_get_contents($pipes[1]));

            fclose($pipes[0]);
            fclose($pipes[1]);

            $this->_lastReturn = proc_close($process);

            return $output;
        }

    }

    /**
     *
     * Handle command return code
     *
     */
    protected function _catchError($code) {

        switch((int)$code) {
            case 0:
                return true;
            break;
            case 2:
                throw new Exception('Misuse of shell builtins, according to Bash documentation',$code);
            break;
            case 126:
                throw new Exception('Cannot execute command',$code);
            break;
            case 127:
                throw new Exception('Command not found',$code);
            break;
            case 128:
                throw new Exception('Invalid argument',$code);
            break;
            case 130:
                throw new Exception('Script terminated by Control-C',$code);
            break;
            default:
                throw new Exception('An error occured',$code);
            break;
        }

    }



    /**
     *
     * Global configuration
     *
     */
    public static function setConfig($name, $value = null) {

        if(!isset($value) && is_array($name)) self::$_config = $name;
        else if(isset($value) && !is_array($name)) self::$_config[$name] = $value;

    }

    public static function getConfig($name = null) {

        if(isset($name)) return isset(self::$_config[$name]) ? self::$_config[$name]:null;
        else return self::$_config;

    }


}