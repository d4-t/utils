<?php

/* * ********************************
 * *     ____      _____    _______  *
 * **   /\  __`\  /| |\ \  /\__  __\  *
 *  **  \ \ \_\ \ || |_\ \ \/_/\ \_/   *
 *   **  \ \____| ||_|\ \_\   \ \ \     *
 *    **  \/___/  /_/  \/_/    \/_/      *
 *     **       Copyright 2014-2019 Dat   *
 *      *********************************** */

namespace Dat\Utils;

class CmnUtil
{
    const LOGTYPE_NOTICE = 0;
    const LOGTYPE_WARNING = 1;
    const LOGTYPE_DEBUG = 2;
    const LOGTYPE_ERROR = 3;


    /**
     * show a status bar in the console
     *
     * <code>
     * for($x=1;$x<=100;$x++){
     *
     *     showStatus($x, 100);
     *
     *     usleep(100000);
     *
     * }
     * </code>
     *
     * @param int $done how many items are completed
     * @param int $total how many items are to be done total
     * @param bool $reset_time to reset time counting if set true
     * @param int $size optional size of the status bar
     * @return  void
     *
     */
    public static function showStatus($done, $total, $reset_time = false, $size = 30)
    {

        static $start_time;
        if ($reset_time) {
            $start_time = time();
            return;
        }
        // if we go over our bound, just ignore it
        if ($done > $total)
            return;
        if (empty($start_time))
            $start_time = time();
        $now = time();
        $perc = (double)($done / $total);
        $bar = floor($perc * $size);
        $status_bar = "\r[";
        $status_bar .= str_repeat("=", $bar);
        if ($bar < $size) {
            $status_bar .= ">";
            $status_bar .= str_repeat(" ", $size - $bar);
        } else {
            $status_bar .= "=";
        }
        $disp = number_format($perc * 100, 0);
        $status_bar .= "] $disp%  $done/$total";
        if ($done == 0)
            $rate = 0;
        else
            $rate = ($now - $start_time) / $done;

        $left = $total - $done;
        $eta = round($rate * $left, 2);
        $elapsed = $now - $start_time;
        $speed = round($elapsed ? $done / $elapsed : 0, 2);
        $etaStr = $eta < 7200 ? ($eta . " sec") : ($eta < 86400 ? (round($eta / 3600) . " hr") : (floor($eta / 86400) . " days " . round($eta % 86400 / 3600) . " hr"));
        $elapsedStr = $elapsed < 7200 ? ($elapsed . " sec") : ($elapsed < 86400 ? (round($elapsed / 3600) . " hr") : (floor($elapsed / 86400) . " days " . round($elapsed % 86400 / 3600) . " hr"));
        $status_bar .= " remaining: " . $etaStr . "  elapsed: " . $elapsedStr . " speed: " . $speed;
        echo "$status_bar  ";
        flush();
        // when done, send a newline
        if ($done == $total)
            echo PHP_EOL;
    }

//    function progress_bar($done, $total, $info="", $width=50) {
//        $perc = round(($done * 100) / $total);
//        $bar = round(($width * $perc) / 100);
//        return sprintf("%s%%[%s>%s]%s\r", $perc, str_repeat("=", $bar), str_repeat(" ", $width-$bar), $info);
//    }
    /**
     *
     * @param string $str Input string. Note:this string will be modified
     * @param int $n number of bytes to cut
     * @param bool $isHex whether string is in Hex or binary format
     * @return string the string cut out
     */
    public static function leftCut(&$str, $n, $io = 'hh')
    {
        $ioArr = self::decodeIo($io);
        switch ($ioArr['input']) {
            case 'h':
            case 'n':
                $result = substr($str, 0, $n * 2);
                $str = substr($str, $n * 2);
                break;
            case 'b':
                $result = CryptoCurrency::bin2Hex(substr($str, 0, $n));
                $str = substr($str, $n);
                break;
            default :
                throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . '() Invalid io option');
        }
        switch ($ioArr['output']) {
            case 'h':
            case 'n':
                break;
            case 'b':
                $result = CryptoCurrency::hex2Bin($result);
                break;
            default :
                throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . '() Invalid io option');
        }
        return $result;
    }

    /**
     * Left n byte of string, move pointer p too
     *
     * @param string $str Input string. Note:this string will be modified
     * @param int $n number of bytes to cut
     * @param int $p pointer / offset
     * @param string $io
     * @return string the result string
     */
    public static function left(&$str, $n, &$p, $io = 'hh')
    {
        $ioArr = self::decodeIo($io);
        switch ($ioArr['input']) {
            case 'h':
            case 'n':
                $result = substr($str, $p * 2, $n * 2);
                break;
            case 'b':
                $result = CryptoCurrency::bin2Hex(substr($str, $p, $n));
                $str = substr($str, $n);
                break;
            default :
                throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . '() Invalid io option');
        }
        $p += $n;
        switch ($ioArr['output']) {
            case 'h':
            case 'n':
                break;
            case 'b':
                $result = CryptoCurrency::hex2Bin($result);
                break;
            default :
                throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . '() Invalid io option');
        }
        return $result;
    }

    protected static function getDateTimeStr(\DateTimeZone $tz = null)
    {
        $datetime = new \DateTime(); // current time = server time
        if ($tz) {
            $datetime->setTimezone($tz);
        }
        return $datetime->format('Y-m-d H:i:s');
    }

    public static function getInfoStr(string $typeStr, $color, $var = null, bool $hasVar = false, string $name = "", string $text = "", \DateTimeZone $tz = null, $isCli = null, $verbose = true)
    {
        $dateStr = self::getDateTimeStr($tz);
        if ($isCli === null) {
            $api = php_sapi_name();
        } else {
            $api = $isCli ? "cli" : "html";
        }
        $r = "";
        if ($api == 'cli') {
            $r .= self::getColoredString("[$typeStr] $dateStr $text", $color) . ($name ? "$name : " : "");

        } else {
            $r .= "[$typeStr] $dateStr $name : ";
        }
        if ($hasVar) {
            $r .= self::dumpVar($var, ($api === 'cli'), $verbose);
        }
        return $r;
    }

    /**
     * Print the variable on screen
     * @param mixed $var variable to debug
     * @param string $name name of variable, default: var
     * @param bool $exit
     * @param \DateTimeZone|null $tz
     * @throws \Exception
     * ex: [debug] var: var_content_here
     */
    public static function debug($var, $name = 'var', $exit = false, \DateTimeZone $tz = null)
    {
        echo self::getInfoStr('Debug', 'light_cyan', $var, true, $name, "", $tz);
        $exit and exit;
    }

    /**
     * Print the variable to /tmp/debug.log
     * @param mixed $var variable to debug
     * @param string $name name of variable, default: var
     * @param bool $verbose
     * ex: [debug] var: var_content_here
     */
    public static function logDebug($var, $name = 'var', $verbose = false, \DateTimeZone $tz = null)
    {
        $data = self::getInfoStr('Debug', 'light_cyan', $var, true, $name, "", $tz, true, $verbose);
        $fp = fopen("/tmp/debug.log", "a");
        fwrite($fp, $data);
        fclose($fp);
    }

    /**
     * Print notice on screen, optionally print $var too
     * @param string $text
     * @param string/array/object $var
     * @param boolean $exit
     * @param \DateTimeZone|null $tz
     * ex: [notice] YY-mm-dd HH:ii:ss notice_content_here var_content_here
     */
    public static function notice(string $text = "", $var = '', bool $exit = false, \DateTimeZone $tz = null)
    {
        echo self::getInfoStr('Notice', "", $var, true, "", $text, $tz);
        $exit and exit;
    }

    /**
     * Print warning on screen, optionally print $var too
     * @param string $text
     * @param string/array/object $var
     * @param boolean $exit
     * @param \DateTimeZone|null $tz
     * ex: [warning] YY-mm-dd HH:ii:ss warning_content_here var_content_here
     */
    public static function warning(string $text, $var = '', bool $exit = false, \DateTimeZone $tz = null)
    {
        echo self::getInfoStr('Warning', "yellow", $var, true, "", $text, $tz);
        $exit and exit;
    }

    /**
     * Print Error on screen, optionally print $var too
     * @param string $text
     * @param string/array/object $var
     * @param boolean $exit
     * @param \DateTimeZone|null $tz
     * ex: [Error] YY-mm-dd HH:ii:ss error_content_here var_content_here
     */
    public static function error($text, $var = '', $exit = false, \DateTimeZone $tz = null)
    {
        echo self::getInfoStr('Error', "light_red", $var, true, "", $text, $tz);
        $exit and exit;
    }

    /**
     * Dump $var to string
     * @param mixed $var
     * @param bool $cli whether is command line
     * @param mixed $verbose if verbose for object
     * @return string
     */
    public static function dumpVar($var, $cli = true, $verbose = 1)
    {
        $r = "";
        if ($cli) {
            if (is_array($var)) {
                $r .= PHP_EOL . print_r($var, true);
            } elseif (is_object($var)) {
//                $r .= PHP_EOL;
                $r .= self::getColoredString("  Class: ", 'yellow') . get_class($var) . PHP_EOL;
                if ($verbose === true || $verbose === 1) {
                    ob_start();
                    var_dump($var);
                    $r .= ob_get_clean();
                } elseif ($verbose === 2) {
                    $r .= "  Class methods: ";
                    $r .= print_r(get_class_methods($var), true);
                    $r .= "  Class vars: ";
                    $r .= print_r(get_object_vars($var), true);
                }
            } else if (is_bool($var)) {
                $r .= ($var ? "True" : "False") . " (Boolean)" . PHP_EOL;
            } else {
                $r .= $var . PHP_EOL;
            }
        } else {
            $htmlEol = "<br />";
            if (is_array($var)) {
                $r .= $htmlEol . PHP_EOL . print_r($var, true) . $htmlEol . PHP_EOL;
            } elseif (is_object($var) || is_bool($var)) {
                $r .= $htmlEol . PHP_EOL;
                ob_start();
                var_dump($var);
                $r .= ob_get_clean();
                $r .= $htmlEol . PHP_EOL;
            } else {
                $r .= $var . $htmlEol . PHP_EOL;
            }
        }
        return $r;
    }

    /**
     * return a colored string on screen
     * @param string $string content string
     * @param string $fgColor foreground color
     * @param string $bgColor background color
     * @return string with color
     *
     * fgchoices: black, dark_gray, blue, light_blue, green, light_green, cyan, light_cyan, red, light_red, purple, light_purple, brown, yellow, light_gray, white ___|___
     * bgchoices: black, red, green, yellow, blue, magenta, cyan, light_gray
     */
    public static function getColoredString($string, $fgColor = null, $bgColor = null)
    {
        $fgColors = array('black' => '0;30', 'dark_gray' => '1;30', 'blue' => '0;34', 'light_blue' => '1;34', 'green' => '0;32', 'light_green' => '1;32', 'cyan' => '0;36', 'light_cyan' => '1;36', 'red' => '0;31', 'light_red' => '1;31', 'purple' => '0;35', 'light_purple' => '1;35', 'brown' => '0;33', 'yellow' => '1;33', 'light_gray' => '0;37', 'white' => '1;37');
        $bgColors = array('black' => '40', 'red' => '41', 'green' => '42', 'yellow' => '43', 'blue' => '44', 'magenta' => '45', 'cyan' => '46', 'light_gray' => '47',);
        $cString = "";
        if (isset($fgColors[$fgColor]))
            $cString .= "\033[" . $fgColors[$fgColor] . "m";
        if (isset($bgColors[$bgColor]))
            $cString .= "\033[" . $bgColors[$bgColor] . "m";
        $cString .= $string . "\033[0m";
        return $cString;
    }

    public static function red($str)
    {
        return self::getColoredString($str, 'red');
    }

    public static function green($str)
    {
        return self::getColoredString($str, 'green');
    }

    public static function brown($str)
    {
        return self::getColoredString($str, 'brown');
    }

    public static function yellow($str)
    {
        return self::getColoredString($str, 'yellow');
    }

    public static function blue($str)
    {
        return self::getColoredString($str, 'blue');
    }

    public static function cyan($str)
    {
        return self::getColoredString($str, 'cyan');
    }

    public static function lcyan($str)
    {
        return self::getColoredString($str, 'light_cyan');
    }

    /**
     * Return Scientific format of given float number
     * @param float $float a float number
     * @param integer $s significant digits [optional]
     * @return string
     */
    public static function formatScientific($float, $s = 0)
    {
        if ($float == 0) {
            return 0;
        }
        $power = floor(log10($float));
        if ($power)
            $pStr = "e" . $power;
        else
            $pStr = '';
        $num = $float / pow(10, $power);
        if ($s > 0) {
            $num = preg_match("/\./", $num) ? substr((string)$num, 0, $s + 1) : $num;
        }
        return $num . $pStr;
    }

    /**
     * find the first missing element from array
     * in the form of Array([0] => Array([head] => 1,[tail] => 10),[1] => Array([head] => 11,[tail] => 20))
     *
     * @param array $groupArr
     * @return first missing member in array or false if no one missing
     * @throws \Exception if input is not array
     */
    public static function findMissingInGroupArray($groupArr)
    {
        if (!is_array($groupArr))
            throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . "() input must be array");
        $all = array();
        foreach ($groupArr as $arr) {
            $tmp = range($arr['head'], $arr['tail']);
            $all = array_unique(array_merge($all, $tmp));
        }
        $full = range(min($all), max($all));
        $missing = array_diff($full, $all);
        if ($missing)
            return min($missing);
        else
            return false;
    }

    /**
     * Get max in group array
     *
     * @param array $groupArr
     * @return max in the group array
     * @throws \Exception
     */
    public static function maxInGroupArray($groupArr)
    {
        if (!is_array($groupArr))
            throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . "() input must be array");
        $all = array();
        foreach ($groupArr as $arr) {
            $tmp = range($arr['head'], $arr['tail']);
            $all = array_merge($all, $tmp);
        }
        return max($all);
    }

    /**
     * Decode $io string to be array
     * $io available letters: h(hex string), d(dec string), b(bin string),i(dec integer), s(string), f(float), n(not providated/not available/default)
     *
     * @param string $io 1-2 letter string representing input and output type
     * @return array in form of array('input'=>$input,'output'=>$output)
     * @throws \Exception if $io is invalid
     */
    public static function decodeIo($io)
    {
        if (!is_string($io))
            throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . " meet invalid input. io: $io");
        if (strlen($io) > 2 || strlen($io) == 0)
            throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . " meet invalid input. io: $io");
        $io = strtolower($io);
        $input = substr($io, 0, 1);
        $output = substr($io, -1);
        $ioList = array('h', 'd', 'b', 'i', 'f', 's', 'n');
        if (!in_array($input, $ioList) || !in_array($output, $ioList))
            throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . "() meet invalid input. Input: $input, Output: $output");
        $r['input'] = $input;
        $r['output'] = $output;
        return $r;
    }

    /**
     * Encode $ioArr array to 2 letters string
     *
     * @param array $ioArr require 1 letter 'input' and 1 letter 'output'
     * @return string 2 letter $io
     * @throws \Exception when input is illegal
     */
    public static function encodeIo($ioArr)
    {
        if (!isset($ioArr['input']) || !isset($ioArr['output']))
            throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . " meet invalid input.");
        if (strlen($ioArr['input']) !== 1 || strlen($ioArr['output']) !== 1)
            throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . " meet invalid input.");
        return $ioArr['input'] . $ioArr['output'];
    }

    /**
     * Extend implode function to accept non array
     * @param string $glue
     * @param array $pieces , or if
     * @return string
     */
    public static function implode($glue, $pieces)
    {
        if (is_array($pieces))
            return implode($glue, $pieces);
        elseif (is_object($pieces))
            throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . " invalid arguemnt");
        else
            return $pieces;
    }

    public static function curl_post($url = '', $postdata = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function curl_get($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function object2Array($d)
    {
        if (is_object($d))
            $d = get_object_vars($d);
        return is_array($d) ? array_map(__METHOD__, $d) : $d;
    }

    public static function array2Object($d)
    {
        return is_array($d) ? (object)array_map(__METHOD__, $d) : $d;
    }

    /**
     * Get string according to $offset and $nBytes, automatically increase $offset
     * @param hexstring $str
     * @param int $offset
     * @param int $nBytes
     */
    public static function getData($str, &$offset, $nBytes, $io = 'hn')
    {
        $ioArr = self::decodeIo($io);
        $r = $ioArr['input'] == 'h' ? substr($str, $offset * 2, $nBytes * 2) : substr($str, $offset, $nBytes);
        $offset += $nBytes;
        return $r;
    }

    /**
     * log $msg to $file
     *
     * @param string $msg message
     * @param string $file file with full path
     * @param int|string $type default: notice, other choice: warning, debug, error
     * @param \DateTimeZone|null $tz
     */
    public static function log($msg, $file = '/tmp/debug.log', $type = self::LOGTYPE_NOTICE, \DateTimeZone $tz = null)
    {
        if (is_string($type)) {
            $type = strtolower($type);
        }
        if ($type === self::LOGTYPE_ERROR || $type === 'error') {
            $typeStr = 'Error';
            $color = 'light_red';
        } elseif ($type === self::LOGTYPE_WARNING || $type === 'warning') {
            $typeStr = 'Debug';
            $color = 'light_cyan';
        } elseif ($type === self::LOGTYPE_DEBUG || $type === 'debug') {
            $typeStr = 'Warning';
            $color = 'yellow';
        } else {
            $typeStr = 'Notice';
            $color = null;
        }
        $fmsg = self::getInfoStr($typeStr, $color, null, false, "", $msg, $tz, true, false) . PHP_EOL;
        file_put_contents($file, $fmsg, FILE_APPEND | LOCK_EX);
    }

    /**
     * get file name with full path
     *
     * @param string $filename filename
     * @param path $path default /tmp/ Note: the last slash is needed
     * @return string
     */
    public static function logFileName($filename, $path = '/tmp/')
    {
        return $path . $filename;
    }

    /**
     * Get input from command line
     * @param string $text text to show on command line
     * @param string $dataType datatype. supported: string, bool, int, real, multi_int, multi_str
     * @return type
     */
    public static function getInput($text = "Please input your data:", $dataType = "string", $default = NULL)
    {
        echo $text . PHP_EOL;
        $line = trim(fgets(STDIN));
        if (empty($line))
            return $default;
        switch ($dataType) {
            case "bool":
            case "boolean":
                return self::getBoolFrStr($line);
            case "int":
            case "integer":
                $r = (string)(int)$line === $line;
                if ($r)
                    return (int)$line;
                else
                    return self::getInput("An integer is required, please try again:", $dataType, $default);
            case "multi_int":
            case "multiInt":
                $arr = array_map('trim', explode(',', $line));
                foreach ($arr as $num) {
                    if ((string)(int)$num !== $num)
                        return self::getInput("Integers are required, please try again:", $dataType, $default);
                }
                return $arr;
            case "multi_str":
            case "multiStr":
                $arr = array_map('trim', explode(',', $line));
                foreach ($arr as &$str) {
                    $str = self::convertNull($str);
                }
                return $arr;
            case "real":
            case "float":
            case "double":
                $r = floatval($line);
                if ($r)
                    return (double)$line;
                else
                    return self::getInput("An real number is required, please try again:", $dataType, $default);
            case "str":
            case "string":
            default:
                return self::convertNull($line);
        }
    }

    public static function getHiddenInput($prompt = "Enter Password:")
    {

        if (preg_match('/^win/i', PHP_OS)) {
            $vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
            file_put_contents(
                $vbscript, 'wscript.echo(InputBox("'
                . addslashes($prompt)
                . '", "", "password here"))');
            $command = "cscript //nologo " . escapeshellarg($vbscript);
            $password = rtrim(shell_exec($command));
            unlink($vbscript);
            return $password;
        } else {
            $command = "/usr/bin/env bash -c 'echo OK'";
            if (rtrim(shell_exec($command)) !== 'OK') {
                trigger_error("Can't invoke bash");
                return;
            }
            $command = "/usr/bin/env bash -c 'read -s -p \""
                . addslashes($prompt)
                . "\" mypassword && echo \$mypassword'";
            $password = rtrim(shell_exec($command));
            echo "\n";
            return $password;
        }
    }

    public static function convertNull($str)
    {
        return ($str == 'null' || $str == 'Null' || $str == 'NULL') ? NULL : $str;
    }

    /**
     * Return a boolean result from input string
     *
     * @param string $string a boolean string such as 'y','n','yes','false'
     * @return boolean
     * @throws \Exception if input string is not recogniazble
     */
    public static function getBoolFrStr($string)
    {
        $trueArray = ['T', 't', 'Y', 'y', 'True', 'TRUE', 'true', 'Yes', 'YES', 'yes', 'ok', 'yep', 'yeah'];
        $falseArray = ['F', 'f', 'N', 'n', 'False', 'FALSE', 'false', 'No', 'NO', 'no', 'nope', 'na', 'nada'];
        $test = trim($string);
        if (in_array($test, $trueArray))
            return true;
        elseif (in_array($test, $falseArray))
            return false;
        else
            throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . '() invalid input boolean string');
    }

    /**
     * Tell weather input string is unicode printable
     *
     * @param string $str
     * @return int 0 for false, 1 for true
     */
    public static function ctype_print_unicode($str)
    {
        return preg_match("~^[\pL\pN\s\"\~" . preg_quote("!#$%&'()*+,-./:;<=>?@[\]^_`{|}´") . "]+$~u", $str);
    }

    /**
     * Get heights bit of variable. 0xf0 will get 8
     *
     * @param various type $value
     * @return int
     */
    public static function getHeightBit($v)
    {
        $c = 0;
        while ($v) {
            $c++;
            $v = $v >> 1;
        }
        return $c;
    }

    public static function getSessionValue($key)
    {
        $value = $_SESSION[$key];
        return $value;
    }

    public static function getGet($key = null, $default = null)
    {
        if ($key == null) {
            return $_GET;
        }
        return self::getFormParameter($_GET, $key, $default);
    }

    public static function getPost($key = null, $default = null)
    {
        if ($key == null) {
            return $_POST;
        }
        return self::getFormParameter($_POST, $key, $default);
    }

    public static function setPost($key, $value)
    {
        $_POST[$key] = $value;
    }

    public static function getSession($key, $default = null)
    {
        return self::getFormParameter($_SESSION, $key, $default);
    }

    private static function getFormParameter($array, $key, $default = null)
    {
        if ($key != null && array_key_exists($key, $array)) {
            if ($array[$key] != null) {
                $obj = $array[$key];
                if (is_string($obj)) {
                    $tmp = htmlentities($array[$key], ENT_COMPAT, "UTF-8");
                    return str_replace("'", "''", $tmp);
                } else {
                    return $obj;
                }
            }
        }
        return $default;
    }

    /**
     * Recursivly copy a folder and all files
     *
     * @param string $src source path
     * @param string $dst destination path
     */
    public static function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Get the file size of remote url
     * @param string $url
     * @param float $timeout
     * @return int
     */
    public static function getRemoteFileSize($url, $timeout = 5)
    {
        // Assume failure.
        $result = -1;

        $curl = curl_init($url);

        // Issue a HEAD request and follow any redirects.
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        if ($timeout > 0)
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

        $data = curl_exec($curl);
        curl_close($curl);

        if ($data) {
            $content_length = "unknown";
            $status = "unknown";

            if (preg_match("/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches)) {
                $status = (int)$matches[1];
            }

            if (preg_match("/Content-Length: (\d+)/", $data, $matches)) {
                $content_length = (int)$matches[1];
            }

            // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
            if ($status == 200 || ($status > 300 && $status <= 308)) {
                $result = $content_length;
            }
        }

        return $result;
    }

    /**
     * Download file through curl
     *
     * @param string $url
     * @param string $localfile
     * @param int $timeout
     * @param bool $fReturnStatus
     * @param bool $fShowStatus
     * @return boolean
     */
    public static function getRemoteFile($url, $localfile = '/tmp/tmp', $timeout = -1, $fReturnStatus = true, $fShowStatus = false)
    {

        ob_start();
        ob_flush();
        flush();


        if ($timeout < 0)
            $timeout = ini_get('default_socket_timeout');

        set_time_limit(0);
        if ($fReturnStatus) {
            $fs = self::getRemoteFileSize($url, $timeout);
            if ($fs < 0)
                return false;
        }
        $fp = fopen($localfile, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if ($fShowStatus) {
            echo "Loading...";
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, ['DAT\Lib\Dat\CmnUtil', 'curlDownloadProgress']);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        }

        curl_exec($ch);
        curl_close($ch);
        fclose($fp);


        ob_flush();
        flush();
        ob_end_clean();
        if ($fReturnStatus)
            return $fs == filesize($localfile) || $fs == "unknown";
    }

    /**
     * Download file by curl (Deprecated, use getRemoteFile instead)
     * @param string $url
     * @param string $dst path + filename
     * @param boolean $fShowStatus
     */
    public static function curlGetFile($url, $dst, $fShowStatus = false)
    {

        ob_start();
        ob_flush();
        flush();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($fShowStatus) {
            echo "Loading...";
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, ['DAT\Lib\Dat\CmnUtil', 'curlDownloadProgress']);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $r = curl_exec($ch);
        curl_close($ch);
        file_put_contents($dst, $r);

        ob_flush();
        flush();
    }

    public static function curlDownloadProgress($resource, $download_size, $downloaded, $upload_size, $uploaded)
    {
        if ($download_size > 0)
            self::showStatus($downloaded, $download_size);
        ob_flush();
        flush();
    }

    /**
     * Convert integer to human readable file size
     * @param int $size
     * @param string $unit "GB","MB" or "KB"
     * @return string
     */
    public static function humanFileSize($size, $unit = "")
    {
        if ((!$unit && $size >= 1 << 30) || $unit == "GB")
            return number_format($size / (1 << 30), 2) . "GB";
        if ((!$unit && $size >= 1 << 20) || $unit == "MB")
            return number_format($size / (1 << 20), 2) . "MB";
        if ((!$unit && $size >= 1 << 10) || $unit == "KB")
            return number_format($size / (1 << 10), 2) . "KB";
        return number_format($size) . " bytes";
    }

    /**
     * Simply array (numeral key) to string
     * @param array $array
     * @param string $separator
     * @return string
     */
    public static function arrayToString($array, $separator = ' ', $quote = '"')
    {
        $r = "";
        foreach ($array as $i => $v) {
            $r .= $i ? $separator . $quote . $v . $quote : $quote . $v . $quote;
        }
        return $r;
    }

    /**
     * Get filesize, if not exist, return -1
     *
     * @param string $file
     * @return int
     */
    public static function filesize($file)
    {
        if (!file_exists($file))
            return -1;
        else
            return filesize($file);
    }

    /**
     * map csv row to head, return false if number of column doesnot match
     *
     * @param array $head
     * @param array $row
     * @return boolean | array
     */
    public static function mapCsv($head, $row)
    {
        if (count($head) != count($row))
            return false;
        $r = [];
        foreach ($head as $k => $key) {
            if ($key)
                $r[$key] = $row[$k];
        }
        return $r;
    }

    /**
     * Get line count of the given file
     *
     * @param string $file
     * @return int
     */
    public static function getFileLineCount($file)
    {
        $linecount = 0;
        $handle = fopen($file, "r");
        while (!feof($handle)) {
            $line = fgets($handle);
            $linecount++;
        }

        fclose($handle);

        return $linecount;
    }

    /**
     * Make dir auto detect whether dir exists
     *
     * @param string $path
     * @param bool $fHasFile whether path contain file name
     * @return boolean
     */
    public static function mkdir($path, $fHasFile = false)
    {
        try {
            if ($fHasFile) {
                $path = dirname($path);
            }
            if (file_exists($path))
                return true;
            else
                return mkdir($path, 0777, true);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get value from param array
     *
     * @param string $key
     * @param array $param
     * @param mixed $default
     * @return mixed
     */
    public static function getParam($key, $param, $default = false)
    {
        if (!$param)
            return $default;
        if (is_string($key)) {
            if (is_array($param)) {
                if (key_exists($key, $param))
                    return $param[$key];
            } elseif (is_object($param)) {
                if (!isset($param->$key))
                    return $default;
                return $param->$key;
            }
        } elseif (is_array($key)) {
            $tmp = $param;
            foreach ($key as $k) {
                $tmp = self::getParam($k, $tmp);
            }
            return $tmp;
        }
        return $default;
    }

    public static function setParam(&$param, string $key, $value)
    {
        if (is_array($param)) {
            $param[$key] = $value;
        } elseif (is_object($param) || is_null($param)) {
            $param->$key = $value;
        } else {
            throw new \Exception("Error: " . self::class . " " . __FUNCTION__ . " Illegal format param " . gettype($param));
        }
        return $param;
    }

    public static function incrementParam(&$param, string $key, $increment = 1)
    {
        $value = self::getParam($key, $param);
        if (is_numeric($value)) {
            self::setParam($param, $key, $value + $increment);
        } elseif (is_string($value)) {
            self::setParam($param, $key, (string)($value + $increment));
        } elseif (!$value) {
            self::setParam($param, $key, $increment);
        } else {
            throw new \Exception("Error: " . self::class . " " . __FUNCTION__ . " Illegal format of $key " . gettype($value));
        }
    }

    /**
     * Get value from options array
     *
     * @param string $key
     * @param array $param
     * @param mixed $default
     * @return mixed
     */
    public static function getOption($key, $options, $default = false)
    {
        return self::getParam($key, $options, $default);
    }

    /**
     * Output array to screen by given format
     *
     * @param array $a
     * @param string $format default 'pipe', Options: csv, pipe
     */
    public static function outputArray($a, $format = 'pipe')
    {
        $fKey = false;
        echo '| ';
        foreach ($a as $key => $value) {
            if (!is_numeric($key)) {
                $fKey = true;
                echo $key . ' | ';
            }
        }
        if ($fKey)
            echo PHP_EOL . '| ';
        foreach ($a as $key => $value) {
            if (!is_array($value))
                echo $value . ' | ';
            else {
                foreach ($value as $v) {
                    echo $v . ', ';
                }
                echo ' | ';
            }
        }
        echo PHP_EOL;
    }

    /**
     * Output help text
     *
     * @param array $helpArr
     *
     * Sample input
     * $helpArr = [
     * ['main command instruction','prarm0 instruction' ...],
     * 'command name' => ['command instruction', 'param0 instruction' ...]
     * ];
     */
    public static function help($helpArr)
    {
        if (!is_array($helpArr))
            self::error("Help text must be array!", '', 1);
        $api = php_sapi_name();
        foreach ($helpArr as $k => $v) {
            $title = is_numeric($k) ? "" : "[$k] : ";
            $text = self::helpStr($v);
            echo ($api == 'cli') ? self::getColoredString("$title", 'green') . "$text" : ("$title$text");
            echo PHP_EOL;
        }
    }

    private static function helpStr($input)
    {
        if (is_string($input))
            return $input;
        if (!is_array($input))
            self::error("Help text must be string or array!", '', 1);
        $r = '';
        foreach ($input as $k => $v) {
            if ($k === 0)
                $r .= $v;
            elseif (is_numeric($k))
                $r .= PHP_EOL . "    -param " . ($k - 1) . " : " . $v;
            else
                $r .= PHP_EOL . "    -$k : " . $v;
        }
        return $r;
    }

    /**
     * Get a random file name
     * @param int $len
     * @return string
     */
    public static function getRandomFilename(int $len = 20): string
    {
        $a = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $r = "";
        for ($i = 0; $i < $len; $i++) $r .= $a[rand(0, 61)];
        return $r;
    }

    /**
     * Trim string from left with pattern
     * @param $str
     * @param $pattern
     * @return bool|string
     */
    public static function leftTrim($str, $pattern)
    {
        $strpos = strpos($str, $pattern);
        $posToCut = $strpos !== false ? $strpos + strlen($pattern) : 0;
        return substr($str, $posToCut);
    }

    /**
     * Trim string from right with pattern
     * @param $str
     * @param $pattern
     * @return bool|string
     */
    public static function rightTrim($str, $pattern)
    {
        $strpos = strpos($str, $pattern);
        $posToCut = $strpos !== false ? $strpos : strlen($str);
        return substr($str, 0, $posToCut);
    }

    /**
     * Get file name from full path
     * @param $path
     * @return mixed
     */
    public static function getFileNameFromPath(string $path): string
    {
        $pp = pathinfo($path);
        return $pp['basename'];
    }

    /**
     * Get file name without extension from full path
     * @param $path
     * @return mixed
     */
    public static function getFileBaseNameFromPath(string $path): string
    {
        $pp = pathinfo($path);
        return $pp['filename'];
    }

    public static function getFileExtFromPath(string $path): string
    {
        $fn = self::getFileNameFromPath($path);
        if (strpos($fn, '.') === false) {
            return '';
        }
        $tmp = explode('.', $fn);
        return end($tmp);
    }

    /**
     * Is array an associative array
     * @param array $arr
     * @return bool
     */
    public static function isArrayAssoc(array $arr): bool
    {
        if (self::isArrayMulti($arr)) return true;
        return ([] === $arr) ? false : array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Is array a multi-dimension array
     * @param array $a
     * @return bool
     */
    public static function isArrayMulti(array $a): bool
    {
        foreach ($a as $v) {
            if (is_array($v)) return true;
        }
        return false;
    }

    /**
     * Get Depth of array
     * @param array $array
     * @return int
     */
    public static function getArrayDepth(array $array)
    {
        $d = 1;
        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = self::getArrayDepth($value) + 1;
                if ($depth > $d) $d = $depth;
            }
        }
        return $d;
    }

    /**
     * Create human readable table format of array
     * @param array $a
     * @param int $markSet
     * @param int $dimension
     * @return string
     * @throws \Exception
     */
    public static function arrayToTable(array $a, int $markSet = 0, int $dimension = 1): string
    {

        $r = "";
        $set = [
            ["+", "+", "+", "+", "+", "+", "+", "+", "+", "-", "|", " "],
            ["┌", "┐", "└", "┘", "┬", "├", "┤", "┴", "┼", '─', "│", " "],
            [" ", " ", " ", " ", " ", " ", " ", " ", " ", ' ', " ", " "],
        ];
        $markSet = ($markSet >= 0 && $markSet < count($set)) ? $markSet : 0;
        $s = $set[$markSet];
        $d = $dimension > 0 ? $dimension : 2;
        if (count($a) == 0) return $r;
        $isAssoc = self::isArrayAssoc($a);
        $isMulti = self::isArrayMulti($a);
        $h = self::getTableHeader($a, $isAssoc, $isMulti, $d);
        $r .= self::createTableHeader($h, $s, $d);
        foreach ($a as $k => $e) {
            $r .= PHP_EOL . self::tableLine($h, $isAssoc ? ($isMulti ? ['key' => $k, 'line' => self::convertLine($e, $d)] : [$k, $e]) : $e, $s);
        }
        $r .= PHP_EOL . self::createTableFooter($h, $s);
        return $r;
    }

    /**
     * Align tables on the same page
     * @param array $tables
     * @param array $headers
     * @param string $separator
     * @param int $dist
     * @return string
     */
    public static function alignTables(array $tables, array $headers = [], string $separator = " ", int $dist = 3)
    {
        $tArrs = [];
        $size = 0;
        foreach ($tables as $k => $table) {
            $tArrs[$k] = explode(PHP_EOL, $table);
            $size = max($size, count($tArrs[$k]));
        }
        $r = '';
        if ($headers) {
            foreach ($headers as $key => $header) {
                if ($key < count($tArrs)) {
                    $r .= self::strPad($header, mb_strlen($tArrs[$key][0]) + $dist, " ");
                }
            }
            $r .= PHP_EOL;
        }
        for ($i = 0; $i < $size; $i++) {
            foreach ($tArrs as $tArr) {
                if ($i < count($tArr)) {
                    $r .= $tArr[$i];
                } else {
                    $r .= self::strPad("", mb_strlen(end($tArr)));
                }
                $r .= self::strPad("", $dist, $separator);
            }
            $r .= PHP_EOL;
        }
        return $r;
    }

    public static function getCurrentCommit(string $branch = 'master')
    {
        $hash = trim(file_get_contents(__DIR__ . "/../.git/refs/heads/$branch"));
        return $hash ? $hash : false;
    }

    public static function base32Encode($str, $inputBinary = false)
    {
        return BaseUtil::base32Encode($str, $inputBinary);
    }

    public static function base32Decode(string $str, bool $outputBinary = false)
    {
        return BaseUtil::base32Decode($str, $outputBinary);
    }

    public static function bin2Hex(string $bin): string
    {
        return BaseUtil::bin2Hex($bin);
    }

    public static function hex2Bin(string $hex): string
    {
        return BaseUtil::hex2Bin($hex);
    }

    public static function strPad($str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT)
    {
        $str_len = mb_strlen($str);
        $pad_str_len = mb_strlen($pad_str);
        if (!$str_len && ($dir == STR_PAD_RIGHT || $dir == STR_PAD_LEFT)) {
            $str_len = 1; // @debug
        }
        if (!$pad_len || !$pad_str_len || $pad_len <= $str_len) {
            return $str;
        }
        $result = null;
        if ($dir == STR_PAD_BOTH) {
            $length = ($pad_len - $str_len) / 2;
            $repeat = ceil($length / $pad_str_len);
            $result = mb_substr(str_repeat($pad_str, $repeat), 0, floor($length)) . $str . mb_substr(str_repeat($pad_str, $repeat), 0, ceil($length));
        } else {
            $repeat = ceil($str_len - $pad_str_len + $pad_len);
            if ($dir == STR_PAD_RIGHT) {
                $result = $str . str_repeat($pad_str, $repeat);
                $result = mb_substr($result, 0, $pad_len);
            } else if ($dir == STR_PAD_LEFT) {
                $result = str_repeat($pad_str, $repeat);
                $result = mb_substr($result, 0, $pad_len - (($str_len - $pad_str_len) + $pad_str_len)) . $str;
            }
        }
        return $result;
    }

    public static function getConstantNameByValue(string $class, string $prefix, $value, bool $isFullName = true, bool $isLog = true): string
    {
        try {
            $rClass = new \ReflectionClass($class);
            $constants = $rClass->getConstants();
            foreach ($constants as $name => $val) {
                if (substr($name, 0, strlen($prefix)) !== $prefix) {
                    unset($constants[$name]);
                } else if ($value === $val) {
                    return $isFullName ? $name : trim(substr($name, strlen($prefix)), "_");
                }
            }
        } catch (\ReflectionException $e) {
            $isLog and CmnUtil::logDebug($e->getMessage(), __FUNCTION__ . " Error");
        }
        return "";
    }

    public static function getConstantValueByName(string $class, string $name, bool $isLog = true)
    {
        try {
            $rClass = new \ReflectionClass($class);
            $constants = $rClass->getConstants();
            foreach ($constants as $cname => $val) {
                if ($cname === $name) return $val;
            }
        } catch (\ReflectionException $e) {
            $isLog and CmnUtil::logDebug($e->getMessage(), __FUNCTION__ . " Error");
        }
        return null;
    }

    /**
     * Get float number from mixed string
     * @param string $str
     * @return float
     */
    public static function getFloatFrStr(string $str): float
    {
        return (float)filter_var($str, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public static function encodeUrlOnlySpecial(string $url): string
    {
        $r = preg_replace_callback('/[^\x20-\x7f]/', function ($match) {
            return urlencode($match[0]);
        }, $url);
        return str_replace(' ', '%20', $r);
    }

    protected static function convertLine($e, $d, &$r = null, $key = null)
    {
        $r = $r ?? [];
        if ($d == 0 || !is_array($e)) {
            $key = $key ? $key : 'value';
            $r[$key] = is_array($e) ? json_encode($e) : $e;
        } else {
            $d--;
            if (!$e && $key) $r[$key] = json_encode($e);
            foreach ($e as $k => $v) {
                $newKey = $key ? $key . '.' . $k : $k;
                self::convertLine($v, $d, $r, $newKey);
            }
        }
        return $r;
    }

    protected static function getTableWidth($h)
    {
        if (is_array($h)) {
            $r = 0;
            foreach ($h as $subH) {
                $r += self::getTableWidth($subH);
            }
            return $r;
        } else {
            return $h;
        }
    }

    protected static function getTableHeader($a, $isAssoc = null, $isMulti = null, $d = 2)
    {
        $isMulti = $isMulti ?? self::isArrayMulti($a);
        $isAssoc = $isAssoc ?? self::isArrayAssoc($a);

        if ($isMulti) {
            $r = [];
            $r['key'] = 3;
            $r['value'] = 5;
            $hasValue = false;
            foreach ($a as $k => $v) {
                $r['key'] = max($r['key'], self::getTableLen($k));
                if (is_array($v)) {
                    foreach ($v as $key => $value) {
                        $r[$key] = isset($r[$key]) ? self::getTableHeader1($value, $d, $r[$key]) : self::getTableHeader1($value, $d);
                    }
                } else {
                    $r['value'] = max($r['value'], self::getTableLen($v));
                    $hasValue = true;
                }
            }
            if (!$hasValue) unset($r['value']);
            $r = self::getTableHeader2($r, $d);
            return $r;
        } else {
            $wk = 0;
            $w = 0;
            foreach ($a as $k => $e) {
                $wk = $isAssoc ? max($wk, mb_strlen((string)$k)) : 0;
                $w = max($w, self::getTableLen($e));
            }
            return $isAssoc ? ["key" => $wk, "value" => max(5, $w)] : $w;
        }
    }

    protected static function getTableHeader1($a, $d, &$h = null)
    {
        $r = $h ? $h : [];
        if ($d <= 1 || !is_array($a)) {
            $r = $h ? max($h, self::getTableLen($a)) : self::getTableLen($a);
        } else {
            $d--;
            foreach ($a as $k => $v) {
                $r[$k] = (is_array($v) && $d >= 1) ? $r[$k] = self::getTableHeader1($v, $d, $subH) : self::getTableLen($v);
            }
        }
        return $r;
    }

    protected static function getTableHeader2($h, $d, &$r = null, $key = null)
    {
        $r = $r ?? [];
        if ($d == 0 || !is_array($h)) {
            $r[$key] = max(self::getTableLen($key), $h);
        } else {
            if (!$h && $key) {
                $r[$key] = max(self::getTableLen($key), 2); // 2 is the length of []
            }
            $d--;
            foreach ($h as $k => $v) {
                $newKey = $key ? $key . '.' . $k : $k;
                self::getTableHeader2($v, $d, $r, $newKey);
            }
        }
        return $r;
    }

    protected static function getTableLen($e)
    {
        if (is_string($e) || is_numeric($e)) {
            $w = mb_strlen((string)$e);
        } elseif (is_bool($e)) {
            $w = $e ? 4 : 5;
        } elseif (is_null($e)) {
            $w = 4;
        } elseif (is_array($e)) {
            $w = mb_strlen(json_encode($e));
        } elseif (is_object($e)) {
            throw new \Exception("Error: " . self::class . " " . __FUNCTION__ . " Illegal input array");
        } else {
            throw new \Exception("Error: " . self::class . " " . __FUNCTION__ . " Unknown");
        }
        return $w;
    }

    protected static function createTableHeader($h, $s, $d)
    {
        if (is_array($h)) {
            $r = $s[0];
            $i = 0;
            foreach ($h as $header => $subH) {
                if ($i++ !== 0) $r .= $s[4];
                $w = self::getTableWidth($subH);
                $r .= self::strPad("", $w + 2, $s[9]);
            }
            $r .= $s[1]; // First line finish here
            $depth = min(self::getArrayDepth($h), $d);
            $r .= self::createTableHeader1($h, $s, $depth);
//            }
        } else {
            $r = $s[0] . self::strPad("", $h + 2, $s[9]) . $s[1];
        }
        return $r;
    }

    protected static function createTableHeader1($h, $s, $l = 0)
    {
        $r = PHP_EOL;
        foreach ($h as $header => $subH) {
            $w = self::getTableWidth($subH);
            $r .= $s[10] . $s[11] . self::strPad($header, $w) . $s[11];
        }
        $r .= $s[10] . PHP_EOL . $s[5];
        $i = 0;
        foreach ($h as $header => $subH) {
            if ($i++ !== 0) $r .= $s[8];
            $w = self::getTableWidth($subH);
            $r .= self::strPad("", $w + 2, $s[9]);
        }
        $r .= $s[6];
        return $r;
    }


    protected static function createTableFooter($h, $s)
    {
        if (is_array($h)) {
            $r = $s[2];
            $i = 0;
            foreach ($h as $header => $len) {
                if ($i++ !== 0) $r .= $s[7];
                $r .= self::strPad("", $len + 2, $s[9]);
            }
            $r .= $s[3];
        } else {
            $r = $s[2] . self::strPad("", $h + 2, $s[9]) . $s[3];
        }
        return $r;
    }

    protected static function tableLine($w, $e, $s)
    {
        $isAssoc = is_array($e) ? self::isArrayAssoc($e) : false;
        $r = "";
        if (is_int($w)) {
            $r = $s[10] . $s[11] . self::tableCell($w, $e) . $s[11];
        } else if ($isAssoc) {
            foreach ($w as $k => $wd) {
                if ($k == 'key') {
                    $r .= $s[10] . $s[11] . self::tableCell($wd, $e['key']) . $s[11];
                } else {
                    $line = $e['line'];
                    $filled = false;
                    if (is_array($line)) {
                        foreach ($line as $key => $v) {
                            if ($key == $k) {
                                $r .= $s[10] . $s[11] . self::tableCell($w[$k], $v) . $s[11];
                                $filled = true;
                            }
                        }
                    } else if ($k == 'value') {
                        $r .= $s[10] . $s[11] . self::tableCell($w['value'], $line) . $s[11];
                        $filled = true;
                    }
                    if (!$filled) {
                        $r .= $s[10] . $s[11] . self::tableCell($w[$k], "") . $s[11];
                    }
                }
            }
        } elseif (count($w) == count($e)) {
            $i = 0;
            foreach ($w as $k => $wd) {
                $r .= $s[10] . $s[11] . self::tableCell($wd, $e[$i++]) . $s[11];
            }
        } else {
            throw new \Exception("Error: " . self::class . " " . __FUNCTION__ . " input error");
        }
        return $r . $s[10];
    }

    protected static function tableCell($w, $e)
    {
        $true = "TRUE";
        $false = "FALSE";
        $null = "NULL";
        if (is_string($e)) {
            return self::strPad($e, $w, " ");
        } elseif (is_numeric($e)) {
            return self::strPad((string)$e, $w, " ", STR_PAD_LEFT);
        } elseif (is_bool($e)) {
            return self::strPad($e ? $true : $false, $w, " ", STR_PAD_LEFT);
        } elseif (is_null($e)) {
            return self::strPad($null, $w, " ", STR_PAD_LEFT);
        } elseif (is_array($e)) {
            return self::strPad(json_encode($e), $w);
        }
    }
}
