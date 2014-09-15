<?php

/**
 * Class CliWriter
 * @author Aleksey Borisov (mclaren_f1@mail.ru)
 */
Class CliWriter
{
    private $_startTime = null;
    private $_prevTime = null;

    private $_progress_last_render;
    private $_progress_template;
    private $_progress_active = false;

    const PROGRESS_STYLE_PERCENT = 1;
    const PROGRESS_STYLE_OF = 2;
    const PROGRESS_STYLE_OF_PERCENT = 3;

    protected static $_instance;

    private function __construct() {}

    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self;

            ini_set('output_buffering', 'off');
            self::$_instance->_initTime();
        }
        return self::$_instance;
    }

    private function _initTime(){
        $this->_startTime = microtime(true);
    }

    static function sendLine($str){
        self::getInstance()->_sendLine($str);
    }

    static function sendMessage($str){
        self::getInstance()->_sendMessage($str);
    }

    static function sendError($str){
        self::getInstance()->_sendMessage("\e[0;91m".$str."\e[0m");
    }

    static function sendProgress($content, $total = null, $style = null){
        self::getInstance()->_sendProgress($content, $total, $style);
    }

    static function startProgress(){
        self::getInstance()->_startProgress();
    }

    static function endProgress(){
        self::getInstance()->_endProgress();
    }

    private function _sendLine($str){

        $parts = array();
        $parts[] = '';
        $parts[] = '';
        $parts[] = '';
        $parts[] = '';

        $content = $this->_join($parts, '-', '+').' '.$this->_prepareString($str);

        $this->_push($content);
    }

    private function _sendProgress($content, $total = null, $style = null){

        if ($total && $style){
            switch ($style){
                case self::PROGRESS_STYLE_PERCENT:
                    $variable = round($content*100/$total,1).'%';
                    break;
                case self::PROGRESS_STYLE_OF:
                    $variable = $content.' of '.$total;
                    break;
                case self::PROGRESS_STYLE_OF_PERCENT:
                    $variable = $content.' of '.$total.' ('.round($content*100/$total,1).'%)';
                    break;
                default:
                    return false;
            }
        } else {
            $variable = $content;
        }

        $this->_progress_last_render = str_replace('%progress%', $variable, $this->_progress_template);

        echo "\r".$this->_progress_last_render;
        flush();
        if (ob_get_length()){
            ob_end_flush();
        }
    }

    private function _push($str){

        if ($this->_progress_active){
            if (!$this->_progress_template && strpos($str, '%progress%') !== false){
                $this->_progress_template = $str;
                $str = str_replace('%progress%', '', $str);
            }
        }

        if ($this->_progress_last_render){
            $str  = "\r".str_pad($str, strlen($this->_progress_last_render), ' ').PHP_EOL;
            $str .= "\r".$this->_progress_last_render;
            echo $str;
        } else {
            echo $str."\n";
        }

        flush();
        if (ob_get_length()){
            ob_end_flush();
            ob_start();
        }
    }

    private function _startProgress(){
        $this->_progress_active = true;
    }

    private function _endProgress(){
        $this->_progress_active = false;
        $this->_progress_template = null;
        $this->_progress_last_render = null;
        $this->_push($this->_progress_last_render);
    }

    private function _sendMessage($str)
    {
        $currentTime = microtime(true);

        $fullInterval = $currentTime - $this->_startTime;
        $lastInterval = $currentTime - $this->_prevTime;

        $parts = array();
        $parts[] = $this->_getFormatTime($fullInterval);
        $parts[] = $this->_prevTime ? '+'.$this->_getFormatTime($lastInterval) : ' ';
        $parts[] = $this->_getMemoryUsage();

        $this->_prevTime = $currentTime;

        $content = $this->_join($parts, ' ', '|').'|'.$this->_prepareString($str);

        $this->_push($content);
    }

    private function _prepareString($string){

        $str = print_r($string, true);
        if (false && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
            if (($strTmp = @iconv('utf-8', 'cp866//TRANSLIT', print_r($str, true))) !== false){
                $str = $strTmp;
            }
        }

        // вывод с учетом табов

        $strParts = explode("\t", $str);

        $strNew = '';
        foreach ($strParts as $strPart){
            $strNew .= $strPart;
            $length = strlen($strNew);
            $strNew = str_pad($strNew, $length + ($length % 4 ? $length % 4 : 4), ' ');
        }
        return $strNew ? $strNew : $str;
    }

    private function _join($parts, $spaceChar, $delimiterChar){

        foreach ($parts as $key => $part){
            switch ($key){
                case 0:
                    $length = 8;
                    break;
                case 1:
                    $length = 8;
                    break;
                case 2:
                    $length = 8;
                    break;
                case 3:
                    $length = 10;
                    break;
            }
            $part = str_pad($part, $length, $spaceChar);
            $parts[$key] = $part;
        }

        return implode($delimiterChar, $parts);
    }

    private function _getFormatTime($time) {

        if ($time < 10){
            $result = round($time, 3).'s';
        } elseif ($time < 60){
            $result = round($time, 2).'s';
        } elseif ($time < 60*60){
            $result = intval($time/60).'m.'.intval($time - intval($time/60)*60).'s';
        } else {
            $result = intval($time/3600).'h.'.intval(($time - intval($time/3600)*3600)/60).'m';
        }
        return $result;
    }

    private function _getMemoryUsage() {
        $mem_usage = memory_get_usage(true);

        if ($mem_usage < 1024){
            $result = $mem_usage." b";
        }elseif ($mem_usage < 1048576){
            $result = round($mem_usage/1024,2)." kb";
        } elseif ($mem_usage < 1048576*50){
            $result = round($mem_usage/1048576,2)." mb";
        }else{
            $result = ceil($mem_usage/1048576)." mb";
        }
        return $result;
    }


    function _setColor(){
        // example echo "\e[42m\e[1;31mOK\e[43m\e[1;31mOK\e[0m";
        /**
         # Reset
         Color_Off='\e[0m'       # Text Reset

         # Regular Colors
         Black='\e[0;30m'        # Black
         Red='\e[0;31m'          # Red
         Green='\e[0;32m'        # Green
         Yellow='\e[0;33m'       # Yellow
         Blue='\e[0;34m'         # Blue
         Purple='\e[0;35m'       # Purple
         Cyan='\e[0;36m'         # Cyan
         White='\e[0;37m'        # White

         # Bold
         BBlack='\e[1;30m'       # Black
         BRed='\e[1;31m'         # Red
         BGreen='\e[1;32m'       # Green
         BYellow='\e[1;33m'      # Yellow
         BBlue='\e[1;34m'        # Blue
         BPurple='\e[1;35m'      # Purple
         BCyan='\e[1;36m'        # Cyan
         BWhite='\e[1;37m'       # White

         # Underline
         UBlack='\e[4;30m'       # Black
         URed='\e[4;31m'         # Red
         UGreen='\e[4;32m'       # Green
         UYellow='\e[4;33m'      # Yellow
         UBlue='\e[4;34m'        # Blue
         UPurple='\e[4;35m'      # Purple
         UCyan='\e[4;36m'        # Cyan
         UWhite='\e[4;37m'       # White

         # Background
         On_Black='\e[40m'       # Black
         On_Red='\e[41m'         # Red
         On_Green='\e[42m'       # Green
         On_Yellow='\e[43m'      # Yellow
         On_Blue='\e[44m'        # Blue
         On_Purple='\e[45m'      # Purple
         On_Cyan='\e[46m'        # Cyan
         On_White='\e[47m'       # White

         # High Intensity
         IBlack='\e[0;90m'       # Black
         IRed='\e[0;91m'         # Red
         IGreen='\e[0;92m'       # Green
         IYellow='\e[0;93m'      # Yellow
         IBlue='\e[0;94m'        # Blue
         IPurple='\e[0;95m'      # Purple
         ICyan='\e[0;96m'        # Cyan
         IWhite='\e[0;97m'       # White

         # Bold High Intensity
         BIBlack='\e[1;90m'      # Black
         BIRed='\e[1;91m'        # Red
         BIGreen='\e[1;92m'      # Green
         BIYellow='\e[1;93m'     # Yellow
         BIBlue='\e[1;94m'       # Blue
         BIPurple='\e[1;95m'     # Purple
         BICyan='\e[1;96m'       # Cyan
         BIWhite='\e[1;97m'      # White

         # High Intensity backgrounds
         On_IBlack='\e[0;100m'   # Black
         On_IRed='\e[0;101m'     # Red
         On_IGreen='\e[0;102m'   # Green
         On_IYellow='\e[0;103m'  # Yellow
         On_IBlue='\e[0;104m'    # Blue
         On_IPurple='\e[0;105m'  # Purple
         On_ICyan='\e[0;106m'    # Cyan
         On_IWhite='\e[0;107m'   # White
         */
    }
}