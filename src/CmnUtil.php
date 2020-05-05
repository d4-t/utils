<?php

/* * ********************************
 * *     ____      _____    _______  *
 * **   /\  __`\  /| |\ \  /\__  __\  *
 *  **  \ \ \_\ \ || |_\ \ \/_/\ \_/   *
 *   **  \ \____| ||_|\ \_\   \ \ \     *
 *    **  \/___/  /_/  \/_/    \/_/      *
 *     **       Copyright 2014-2020 Dat   *
 *      *********************************** */

namespace Dat\Utils;

class CmnUtil
{
    const LOGTYPE_NOTICE = 0;
    const LOGTYPE_WARNING = 1;
    const LOGTYPE_DEBUG = 2;
    const LOGTYPE_ERROR = 3;
    const TEST = 0;
    const LANG_UNKNOWN = '--';

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
        if ($done > $total) return;
        if (empty($start_time)) $start_time = time();
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
        if ($done == 0) $rate = 0;
        else $rate = ($now - $start_time) / $done;

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
        if ($done == $total) echo PHP_EOL;
    }

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

    /**
     * Get substring by identifier and offset
     * @param string $str
     * @param string $startStr
     * @param string $endStr
     * @param int $startOffset
     * @param int $endOffset
     * @return false|string
     */
    public static function getSubstrByIdentifier(string $str, string $startStr = '', string $endStr = '', int $startOffset = 0, int $endOffset = 0)
    {
        $start = $startStr ? (strpos($str, $startStr) === false ? -1 : strpos($str, $startStr)) : 0;
        $end = $endStr ? (strpos($str, $endStr, $start) === false ? -1 : strpos($str, $endStr, $start)) : 0;
        if ($start === -1 || $end === -1) return false;
        $start += $startOffset + strlen($startStr);
        if ($start < 0) return false;
        if ($end === 0) {
            $len = $endOffset ? $endOffset : null;
        } else {
            $end += $endOffset;
            $len = $end < $start ? false : ($end - $start);
        }
        return $len === null ? substr($str, $start) : ($len === false ? false : substr($str, $start, $len));
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
        if ($power) $pStr = "e" . $power;
        else $pStr = '';
        $num = $float / pow(10, $power);
        if ($s > 0) {
            $num = preg_match("/\./", $num) ? substr((string)$num, 0, $s + 1) : $num;
        }
        return $num . $pStr;
    }

    /**
     * Return string format of number with given significant digits
     *
     * @param $number
     * @param int $digits
     * @param bool $isStrict
     * @return string
     */
    public static function formatSignificantDigits($number, int $digits = 2, bool $isStrict = true)
    {
        if (!$isStrict) $digits = (int)$number ? strlen((string)(int)$number) : self::getSignificantDigits($number);
        if ($number == 0) {
            $decimalPlaces = $digits - 1;
        } elseif ($number < 0) {
            $decimalPlaces = $digits - floor(log10($number * -1)) - 1;
        } else {
            $decimalPlaces = $digits - floor(log10($number)) - 1;
        }
        $answer = ($decimalPlaces > 0) ? number_format($number, $decimalPlaces) : round($number, $decimalPlaces);
        return (string)$answer;
    }

    /**
     * Return the significant digits of given number
     * Note: input (float)11.0 will return 2 instead of 3, input '11.0' will return 3
     * Note: illegal input will not throw exception. Example: 1.1.2 will return 4
     * @param $number
     * @return int
     */
    public static function getSignificantDigits($number, bool $isTailZeroCounted = false): int
    {
        $numberStr = ltrim((string)$number, '-');
        $hasDot = strpos($numberStr, '.') !== false;
        if ($hasDot) {
            if ((int)$numberStr) return strlen($numberStr) - 1;
            else return strlen(ltrim(substr($numberStr, strpos($numberStr, '.') + 1), '0'));
        } else {
            return $isTailZeroCounted ? strlen($numberStr) : strlen(rtrim($numberStr, '0'));
        }
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
        if ($missing) return min($missing);
        else return false;
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
        if (is_array($pieces)) return implode($glue, $pieces);
        elseif (is_object($pieces))
            throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . " invalid arguemnt");
        else return $pieces;
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
        if (is_object($d)) $d = get_object_vars($d);
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
            $typeStr = 'Warning';
            $color = 'yellow';
        } elseif ($type === self::LOGTYPE_DEBUG || $type === 'debug') {
            $typeStr = 'Debug';
            $color = 'light_cyan';
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
        if (empty($line)) return $default;
        switch ($dataType) {
            case "bool":
            case "boolean":
                return self::getBoolFrStr($line);
            case "int":
            case "integer":
                $r = (string)(int)$line === $line;
                if ($r) return (int)$line;
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
                if ($r) return (double)$line;
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
    public static function getBoolFrStr(string $string)
    {
        $trueArray = ['T', 't', 'Y', 'y', 'True', 'TRUE', 'true', 'Yes', 'YES', 'yes', 'ok', 'yep', 'yeah', '1'];
        $falseArray = ['F', 'f', 'N', 'n', 'False', 'FALSE', 'false', 'No', 'NO', 'no', 'nope', 'na', 'nada', '0'];
        $test = trim($string);
        if (in_array($test, $trueArray)) return true;
        elseif (in_array($test, $falseArray)) return false;
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
        if ($timeout > 0) curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

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


        if ($timeout < 0) $timeout = ini_get('default_socket_timeout');

        set_time_limit(0);
        if ($fReturnStatus) {
            $fs = self::getRemoteFileSize($url, $timeout);
            if ($fs < 0) return false;
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
        if ($download_size > 0) self::showStatus($downloaded, $download_size);
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
        if (!file_exists($file)) return -1;
        else return filesize($file);
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
        if (count($head) != count($row)) return false;
        $r = [];
        foreach ($head as $k => $key) {
            if ($key) $r[$key] = $row[$k];
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
            if (file_exists($path)) return true;
            else return mkdir($path, 0777, true);
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
        if (!$param) return $default;
        if (is_string($key)) {
            if (is_array($param)) {
                if (key_exists($key, $param)) return $param[$key];
            } elseif (is_object($param)) {
                if (!isset($param->$key)) return $default;
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
        if ($fKey) echo PHP_EOL . '| ';
        foreach ($a as $key => $value) {
            if (!is_array($value)) echo $value . ' | ';
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
        if (!is_array($helpArr)) self::error("Help text must be array!", '', 1);
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
        if (is_string($input)) return $input;
        if (!is_array($input))
            self::error("Help text must be string or array!", '', 1);
        $r = '';
        foreach ($input as $k => $v) {
            if ($k === 0) $r .= $v;
            elseif (is_numeric($k))
                $r .= PHP_EOL . "    -param " . ($k - 1) . " : " . $v;
            else $r .= PHP_EOL . "    -$k : " . $v;
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
        for ($i = 0; $i < $len; $i++)
            $r .= $a[rand(0, 61)];
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
     * @param array $a input array
     * @param int $markSet 0 (+-|), 1 (table symbols), 2 (space)
     * @param int $dimension
     * @param bool $isTrans transit table
     * @param bool $rowSeperator seperate the rows by lines
     * @return string
     */
    public static function arrayToTable(array $a, int $markSet = 0, int $dimension = 1, $isTrans = false, $rowSeperator = false): string
    {
        $d = $dimension >= 0 ? $dimension : 1;
        $tableArr = self::arrayToTableArray($a, $d);
        if ($isTrans) $tableArr = self::transTableArray($tableArr);
        return self::tableArrayToTable($tableArr, $markSet, (bool)$rowSeperator);
    }

    /**
     * Transform array to a two dimensions array (in order to print to table)
     * @param array $arr
     * @param int $d
     * @return array
     */
    public static function arrayToTableArray(array $arr, int $d = 2): array
    {
        $r = [];
        $hasValue = false;
        foreach ($arr as $k => $v) {
            $r['key'][] = $k;
            if (!is_array($v)) {
                $r['value'][$k] = $v;
                $hasValue = true;
            } elseif ($d <= 1) {
                $r['value'][$k] = json_encode($v);
                $hasValue = true;
            } else {
                self::arrayToTableArray1($v, $d - 1, $r, $k);
            }
        }
        return $r;
    }

    /**
     * Transit rows with columns on a table array (refer to arrayToTableArray)
     * @param array $arr
     * @return array
     */
    public static function transTableArray(array $arr): array
    {
        $r = [];
        foreach ($arr as $k => $v) {
            if ($k !== 'key') $r['key'][] = $k;
        }
        foreach ($arr['key'] as $key) {
            foreach ($arr as $k => $v) {
                $r[$key][$k] = array_key_exists($key, $v) ? $v[$key] : ($k === 'key' ? $key : "");
            }
        }
        return $r;
    }

    /**
     * Print table from table array (refer to arrayToTableArray)
     * @param array $arr
     * @param int $ms
     * @param bool $rs
     * @return string
     */
    public static function tableArrayToTable(array $arr, int $ms = 0, bool $rs = false): string
    {
        $set = [
//          ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", 10 , 11 ],
            ["+", "+", "+", "+", "+", "+", "+", "+", "+", "-", "|", " "],
            ["┌", "┐", "└", "┘", "┬", "├", "┤", "┴", "┼", '─', "│", " "],
            [" ", " ", " ", " ", " ", " ", " ", " ", " ", ' ', " ", " "],
        ];
        $s = in_array($ms, array_keys($set)) ? $set[$ms] : $set[1];
        $r = "";
        $h = self::getTbH($arr);
        $r .= self::genTbH($h, $s);
        $r .= self::genTbB($arr, $h, $s, $rs);
        $r .= self::genTbF($h, $s);
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

    /**
     * Refresh screen with update of $var
     * @param $var
     * @param float|int $interval
     * @throws \Exception
     */
    public static function liveDebug($var, float $interval = 2)
    {
        $scColCount = exec('tput cols');
        $scRowCount = exec('tput lines');
        system('clear');
        $timeStr = "[" . self::getDateTimeStr() . "]";
        $r = PHP_EOL . $var;
        $rows = explode(PHP_EOL, $r);
        $colCount = 0;
        foreach ($rows as &$row) {
            $colCount = max($colCount, mb_strlen($row));
            $row = mb_substr($row, 0, $scColCount);
        }
        $rowCount = count($rows);
        $rows = array_splice($rows, 0, $scRowCount);
        $scInfo = " Col: " . ($colCount > $scColCount ? "$scColCount/" . self::getColoredString($colCount, 'red') : $colCount) . " Rows: " . ($rowCount > $scRowCount ? "$scRowCount/" . self::getColoredString($rowCount, 'red') : $rowCount);
        $r = $timeStr . $scInfo . self::implode(PHP_EOL, $rows);
        echo $r;
        usleep($interval * 1000000);
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
        if (0 !== $str_len && !$str_len && ($dir == STR_PAD_RIGHT || $dir == STR_PAD_LEFT)) {
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
            if ($dir == STR_PAD_RIGHT) {
                $result = $str . str_repeat($pad_str, $pad_len);
                $result = mb_substr($result, 0, $pad_len);
            } else if ($dir == STR_PAD_LEFT) {
                $result = str_repeat($pad_str, $pad_len);
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

    public static function getConstantValueByName(string $class, string $name, bool $isLog = true, $default = null)
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
        return $default;
    }

    public static function getConstantValues(string $class, string $prefix = "", $isLog = true)
    {
        $r = [];
        try {
            $rClass = new \ReflectionClass($class);
            $constants = $rClass->getConstants();
            foreach ($constants as $name => $val) {
                if (substr($name, 0, strlen($prefix)) !== $prefix) {
                    unset($constants[$name]);
                } else {
                    array_push($r, $val);
                }
            }
        } catch (\ReflectionException $e) {
            $isLog and CmnUtil::logDebug($e->getMessage(), __FUNCTION__ . " Error");
        }
        return $r;
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

    /**
     * Get Mine type from url
     * @param string $url
     * @return type
     */
    public static function getMimeFrUrl(string $url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        return curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    }

    /**
     * Get file extension from MIME type
     * @param string $mime
     * @return type
     */
    public static function getExtentionFrMime(string $mime)
    {
        $mime_map = [
            'video/3gpp2' => '3g2',
            'video/3gp' => '3gp',
            'video/3gpp' => '3gp',
            'application/x-compressed' => '7zip',
            'audio/x-acc' => 'aac',
            'audio/ac3' => 'ac3',
            'application/postscript' => 'ai',
            'audio/x-aiff' => 'aif',
            'audio/aiff' => 'aif',
            'audio/x-au' => 'au',
            'video/x-msvideo' => 'avi',
            'video/msvideo' => 'avi',
            'video/avi' => 'avi',
            'application/x-troff-msvideo' => 'avi',
            'application/macbinary' => 'bin',
            'application/mac-binary' => 'bin',
            'application/x-binary' => 'bin',
            'application/x-macbinary' => 'bin',
            'image/bmp' => 'bmp',
            'image/x-bmp' => 'bmp',
            'image/x-bitmap' => 'bmp',
            'image/x-xbitmap' => 'bmp',
            'image/x-win-bitmap' => 'bmp',
            'image/x-windows-bmp' => 'bmp',
            'image/ms-bmp' => 'bmp',
            'image/x-ms-bmp' => 'bmp',
            'application/bmp' => 'bmp',
            'application/x-bmp' => 'bmp',
            'application/x-win-bitmap' => 'bmp',
            'application/cdr' => 'cdr',
            'application/coreldraw' => 'cdr',
            'application/x-cdr' => 'cdr',
            'application/x-coreldraw' => 'cdr',
            'image/cdr' => 'cdr',
            'image/x-cdr' => 'cdr',
            'zz-application/zz-winassoc-cdr' => 'cdr',
            'application/mac-compactpro' => 'cpt',
            'application/pkix-crl' => 'crl',
            'application/pkcs-crl' => 'crl',
            'application/x-x509-ca-cert' => 'crt',
            'application/pkix-cert' => 'crt',
            'text/css' => 'css',
            'text/x-comma-separated-values' => 'csv',
            'text/comma-separated-values' => 'csv',
            'application/vnd.msexcel' => 'csv',
            'application/x-director' => 'dcr',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/x-dvi' => 'dvi',
            'message/rfc822' => 'eml',
            'application/x-msdownload' => 'exe',
            'video/x-f4v' => 'f4v',
            'audio/x-flac' => 'flac',
            'video/x-flv' => 'flv',
            'image/gif' => 'gif',
            'application/gpg-keys' => 'gpg',
            'application/x-gtar' => 'gtar',
            'application/x-gzip' => 'gzip',
            'application/mac-binhex40' => 'hqx',
            'application/mac-binhex' => 'hqx',
            'application/x-binhex40' => 'hqx',
            'application/x-mac-binhex40' => 'hqx',
            'text/html' => 'html',
            'image/x-icon' => 'ico',
            'image/x-ico' => 'ico',
            'image/vnd.microsoft.icon' => 'ico',
            'text/calendar' => 'ics',
            'application/java-archive' => 'jar',
            'application/x-java-application' => 'jar',
            'application/x-jar' => 'jar',
            'image/jp2' => 'jp2',
            'video/mj2' => 'jp2',
            'image/jpx' => 'jp2',
            'image/jpm' => 'jp2',
            'image/jpeg' => 'jpeg',
            'image/pjpeg' => 'jpeg',
            'application/x-javascript' => 'js',
            'application/json' => 'json',
            'text/json' => 'json',
            'application/vnd.google-earth.kml+xml' => 'kml',
            'application/vnd.google-earth.kmz' => 'kmz',
            'text/x-log' => 'log',
            'audio/x-m4a' => 'm4a',
            'application/vnd.mpegurl' => 'm4u',
            'audio/midi' => 'mid',
            'application/vnd.mif' => 'mif',
            'video/quicktime' => 'mov',
            'video/x-sgi-movie' => 'movie',
            'audio/mpeg' => 'mp3',
            'audio/mpg' => 'mp3',
            'audio/mpeg3' => 'mp3',
            'audio/mp3' => 'mp3',
            'video/mp4' => 'mp4',
            'video/mpeg' => 'mpeg',
            'application/oda' => 'oda',
            'audio/ogg' => 'ogg',
            'video/ogg' => 'ogg',
            'application/ogg' => 'ogg',
            'application/x-pkcs10' => 'p10',
            'application/pkcs10' => 'p10',
            'application/x-pkcs12' => 'p12',
            'application/x-pkcs7-signature' => 'p7a',
            'application/pkcs7-mime' => 'p7c',
            'application/x-pkcs7-mime' => 'p7c',
            'application/x-pkcs7-certreqresp' => 'p7r',
            'application/pkcs7-signature' => 'p7s',
            'application/pdf' => 'pdf',
            'application/octet-stream' => 'pdf',
            'application/x-x509-user-cert' => 'pem',
            'application/x-pem-file' => 'pem',
            'application/pgp' => 'pgp',
            'application/x-httpd-php' => 'php',
            'application/php' => 'php',
            'application/x-php' => 'php',
            'text/php' => 'php',
            'text/x-php' => 'php',
            'application/x-httpd-php-source' => 'php',
            'image/png' => 'png',
            'image/x-png' => 'png',
            'application/powerpoint' => 'ppt',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.ms-office' => 'ppt',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop' => 'psd',
            'image/vnd.adobe.photoshop' => 'psd',
            'audio/x-realaudio' => 'ra',
            'audio/x-pn-realaudio' => 'ram',
            'application/x-rar' => 'rar',
            'application/rar' => 'rar',
            'application/x-rar-compressed' => 'rar',
            'audio/x-pn-realaudio-plugin' => 'rpm',
            'application/x-pkcs7' => 'rsa',
            'text/rtf' => 'rtf',
            'text/richtext' => 'rtx',
            'video/vnd.rn-realvideo' => 'rv',
            'application/x-stuffit' => 'sit',
            'application/smil' => 'smil',
            'text/srt' => 'srt',
            'image/svg+xml' => 'svg',
            'application/x-shockwave-flash' => 'swf',
            'application/x-tar' => 'tar',
            'application/x-gzip-compressed' => 'tgz',
            'image/tiff' => 'tiff',
            'text/plain' => 'txt',
            'text/x-vcard' => 'vcf',
            'application/videolan' => 'vlc',
            'text/vtt' => 'vtt',
            'audio/x-wav' => 'wav',
            'audio/wave' => 'wav',
            'audio/wav' => 'wav',
            'application/wbxml' => 'wbxml',
            'video/webm' => 'webm',
            'audio/x-ms-wma' => 'wma',
            'application/wmlc' => 'wmlc',
            'video/x-ms-wmv' => 'wmv',
            'video/x-ms-asf' => 'wmv',
            'application/xhtml+xml' => 'xhtml',
            'application/excel' => 'xl',
            'application/msexcel' => 'xls',
            'application/x-msexcel' => 'xls',
            'application/x-ms-excel' => 'xls',
            'application/x-excel' => 'xls',
            'application/x-dos_ms_excel' => 'xls',
            'application/xls' => 'xls',
            'application/x-xls' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-excel' => 'xlsx',
            'application/xml' => 'xml',
            'text/xml' => 'xml',
            'text/xsl' => 'xsl',
            'application/xspf+xml' => 'xspf',
            'application/x-compress' => 'z',
            'application/x-zip' => 'zip',
            'application/zip' => 'zip',
            'application/x-zip-compressed' => 'zip',
            'application/s-compressed' => 'zip',
            'multipart/x-zip' => 'zip',
            'text/x-scriptzsh' => 'zsh',
        ];

        return isset($mime_map[$mime]) === true ? $mime_map[$mime] : false;
    }

    public static function getGetParamFrUrl(string $url): array
    {
        $r = [];
        if (strpos($url, '?') === false) return $r;
        $trimmedUrl = substr($url, strpos($url, '?') + 1);
        $arr = explode('&', $trimmedUrl);
        foreach ($arr as $line) {
            $lineArr = explode('=', $line);
            if (count($lineArr) > 2) continue;
            $r[$lineArr[0]] = isset($lineArr[1]) ? rawurldecode($lineArr[1]) : true;
        }
        return $r;
    }

    /**
     *
     * @param DateInterval $dateDiff
     * @param string $precision Note: M for month, m for minute
     *      Suggested value Y,M,D,h,i,s,f,z z for round up value
     *      Allowed value: Y,M,D,H,I,S,F,y,m,d,h,i,s,z
     * @return string Example: 3Y2M1D 12h10m8.1000000s
     */
    public static function dateIntervalToString(\DateInterval $dateDiff, string $precision = 'z'): string
    {
        $last = ($precision == 'm' ? 'i' : strtolower($precision));
        if (!in_array($last, ['y', 'm', 'd', 'h', 'i', 's', 'f', 'z']))
            return '';
        $dateDiffArr = self::dateIntervalToArray($dateDiff);
        if ('z' == $last) {
            $rDateDiffArr = array_reverse($dateDiffArr);
            $last = 'f';
            foreach ($rDateDiffArr as $k => $v) {
                if ($v == 0) continue;
                else {
                    $last = $k;
                    break;
                }
            }
        }
        $s = 0;
        $r = $startFr = '';
        $sign = $dateDiff->format("%r") !== '-';
        foreach ($dateDiffArr as $k => $v) {
            if (!$startFr) {
                if (0 == $v) continue;
                else $startFr = $k;
            }
            if ('y' == $k) $r .= $v ? abs($v) . "Y" : "";
            elseif ('m' == $k) $r .= $v ? abs($v) . "M" : "";
            elseif ('d' == $k) $r .= $v ? abs($v) . "D " : "";
            elseif ('h' == $k) $r .= $v ? abs($v) . "h" : "";
            elseif ('i' == $k) $r .= $v ? abs($v) . "m" : "";
            elseif ('s' == $k) $s = abs($v);
            else $s += abs($v);
            if ($v < 0) $sign = !$sign;
            if ($k == strtolower($last)) break;
        }
        if ('f' === strtolower($precision) && 'f' === $last && 'f' === $startFr)
            return $dateDiff->format("%fmicroseconds");
        $sStr = ('f' === $last && 'z' !== $precision) ? number_format($s, 6) : rtrim(rtrim(number_format($s, 6), '0'), '.') . 's';

        if ('s' == $last || 'f' == $last) $r .= $sStr;
        return trim($sign ? $r : "-$r");
    }

    /**
     * Get array of possible languages
     * Available languages: zh (Chinese), jp (Japanese), kr (Korean), th (Thai), vn (Vietnamese), my (Burmese), lo (Lao), km (khmer), en (English), self::LANG_UNKNOWN
     * Note: "日本人" will return zh instead of jp, "nhanh" will return en instead of vn
     * @param bool $isResultArray
     * @return array | string
     */
    public static function getStrLangByUnicodeRange(string $str, bool $isResultArray = true)
    {
        $arr = self::getStrLangArr($str);
        if ($isResultArray) return $arr;
        $t = array_keys($arr, max($arr));
        $maxKey = array_shift($t);
        if (count($arr) < 3) {
            return $maxKey;
        } else {
            $sd = self::getStdDeviationFrArr($arr);
            if ($sd < 0.25) return self::LANG_UNKNOWN;
            $arrN = $arr;
            unset($arrN[$maxKey]);
            $t2 = array_keys($arrN, max($arrN));
            $secondMaxKey = array_shift($t2);
            if ($arr[$maxKey] - $arr[$secondMaxKey] < $sd)
                return self::LANG_UNKNOWN;
            return $maxKey;
        }
    }

    /**
     *
     * @param array $arr
     * @return float
     */
    public static function getStdDeviationFrArr(array $arr): float
    {
        $size = count($arr);
        if (0 === $size) return 0;
        $mu = array_sum($arr) / $size;
        $ans = 0;
        foreach ($arr as $elem) {
            $ans += pow(($elem - $mu), 2);
        }
        return sqrt($ans / $size);
    }

    /**
     * mb safe (utf-8) str_split
     * @param type $str
     * @param type $len
     * @return type
     */
    public static function strSplitUnicode($str, $len = 1)
    {
        $arr = [];
        $length = mb_strlen($str, 'UTF-8');
        for ($i = 0; $i < $length; $i += $len) {
            $arr[] = mb_substr($str, $i, $len, 'UTF-8');
        }
        return $arr;
    }

    /**
     * Get all country codes (alpha2) from php locales
     * @return array
     */
    public static function getAllCountryCodes(): array
    {
        $locales = \ResourceBundle::getLocales('');
        $r = [];
        foreach ($locales as $locale) {
            $c = \Locale::getRegion($locale);
            if (2 === strlen($c)) $r[] = $c;
        }
        return array_values(array_unique($r));
    }

    /**
     * Get all countries from php locales
     * @return array
     */
    public static function getAllCountries()
    {
        $locales = \ResourceBundle::getLocales('');
        $r = [];
        foreach ($locales as $locale) {
            $c = \Locale::getRegion($locale);
            if (2 === strlen($c)) {
                $r[$c] = \Locale::getDisplayRegion($locale);
            }
        }
        ksort($r);
        return $r;
    }

    /**
     *
     * @param string $alpha2
     * @return string empty if unknown
     */
    public static function getCountryNameByCode(string $alpha2): string
    {
        $r = \Locale::getDisplayRegion("-$alpha2");
        return $r === "Unknown Region" ? "" : $r;
    }

    /**
     *
     * @param string $name
     * @return string (alpha2)
     */
    public static function getCountryCodeByName(string $name): string
    {
        $countries = self::getAllCountries();
        foreach ($countries as $code => $country) {
            if ($country === $name) return $code;
        }
        return "";
    }

    /**
     * Get all possible locales by alpha 2 country code
     * @param string $alpha2
     * @return array
     */
    public static function getAllLocalesFrCountry(string $alpha2): array
    {
        if (2 !== strlen($alpha2)) return [];
        $locales = \ResourceBundle::getLocales('');
        $r = [];
        foreach ($locales as $locale) {
            if ($alpha2 === \Locale::getRegion($locale)) $r[] = $locale;
        }
        return $r;
    }

    /**
     * Encode get part of url with input arrray
     * @param array $params
     * @return string
     */
    public static function encodeUrlParam(array $params): string
    {
        $r = '';
        $i = 0;
        foreach ($params as $k => $v) {
            $r .= $i++ ? '&' : '?';
            $r .= self::encodeUrlOnlySpecial($k);
            $r .= true === $v ? '' : ('=' . self::encodeUrlOnlySpecial($v));
        }
        return $r;

    }

    /**
     * Return the location info of given ip address
     * @param $ip
     * @return string
     */
    public static function getIpInfo($ip)
    {
        return IpUtil::getIpInfo($ip);
    }

    protected static function getStrLangArr(string $str): array
    {
        if (0 === mb_strlen($str)) return [self::LANG_UNKNOWN => 1];
        $r = [];
        $langData = [
            'sym' => '/[-!$%^&*()_+|~=`{}\[\]:";\'<>?,.\/#@\s\t\n\r\\\]/',
            'en' => '/[a-zA-Z]/u',
            'th' => '/\p{Thai}/u',
            'zh' => '/\p{Han}/u',
            'my' => '/\p{Myanmar}/u',
            'lo' => '/\p{Lao}/u',
            'km' => '/\p{Khmer}/u',
            'jp' => '/[\p{Hiragana}\p{Katakana}]/u',
            'kr' => '/\p{Hangul}/u',
            'vn' => '/[àáãạảăắằẳẵặâấầẩẫậèéẹẻẽêềếểễệđìíĩỉịòóõọỏôốồổỗộơớờởỡợùúũụủưứừửữựỳỵỷỹýÀÁÃẠẢĂẮẰẲẴẶÂẤẦẨẪẬÈÉẸẺẼÊỀẾỂỄỆĐÌÍĨỈỊÒÓÕỌỎÔỐỒỔỖỘƠỚỜỞỠỢÙÚŨỤỦƯỨỪỬỮỰỲỴỶỸÝ]/u',
        ];

        $lm = [];
        $chars = self::strSplitUnicode($str);
        $len = count($chars);
        foreach ($chars as $c) {
            foreach ($langData as $lang => $ex) {
                $match = preg_match($ex, $c);
                if ($match) $lm[$lang] = isset($lm[$lang]) ? $lm[$lang] + 1 : 1;
            }
        }
        $cc = $len - ($lm['sym'] ?? 0);
        if (1 === count($lm)) {
            ('sym' === key($lm)) ? ($r[self::LANG_UNKNOWN] = 1) : ($r[key($lm)] = 1);
            return $r;
        } elseif (2 === count($lm) && isset($lm['sym'])) {
            $other = $len - $lm['sym'];
            unset($lm['sym']);
            $other -= $lm[key($lm)];
            $r = $other > $lm[key($lm)] ? [self::LANG_UNKNOWN => 1] : [key($lm) => 1];
            return $r;
        }
        foreach ($lm as $lang => $cnt) {
            $r[$lang] = min($cnt / $cc, 1);
        }
        if (isset($lm['jp']) && isset($lm['zh']) && $lm['zh'] > 0) {
            $r['jp'] = min($lm['jp'] * 20 / $cc, 1);
            if ($lm['zh'] / $cc > $r['jp'])
                $r['zh'] = min($lm['zh'] / $cc - $r['jp'], 1);
            else unset($r['zh']);
        }
        if (isset($lm['vn']) && isset($lm['en']) && $lm['en'] > 0) {
            $r['vn'] = min($lm['vn'] * 10 / $cc, 1);
            if ($lm['en'] / $cc > $r['vn'])
                $r['en'] = min($lm['en'] / $cc - $r['vn'], 1);
            else unset($r['en']);
        }
        unset($r['sym']);
        if (count($r) < 3) return $r;
        $sum = array_sum($r);
        foreach ($r as &$v) {
            $v = ($sum ? ($v / $sum) : $v);
        }
        return $r;
    }

    protected static function dateIntervalToArray(\DateInterval $dateDiff): array
    {
        return [
            'y' => $dateDiff->y,
            'm' => $dateDiff->m,
            'd' => $dateDiff->d,
            'h' => $dateDiff->h,
            'i' => $dateDiff->i,
            's' => $dateDiff->s,
            'f' => $dateDiff->f,
        ];
    }

    protected static function getTbC($w, $e)
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

    protected static function getPathValue($path, $arr, $i = 0)
    {
        if (isset($path[$i]))
            return self::getPathValue($path, $arr[$path[$i]] ?? "", $i + 1) ?? $arr;
        else return $arr;
    }


    protected static function arrayToTableArray1(array $arr, int $d, array &$r, string $rKey, string $key = '')
    {
        if (count($arr) === 0) $r[$key][$rKey] = [];
        else $key = $key ? $key . "." : "";
        foreach ($arr as $k => $v) {
            if ($d <= 1) {
                $r[$key . $k][$rKey] = is_array($v) ? json_encode($v) : $v;
            } else if (!is_array($v)) {
                $r[$key . $k][$rKey] = $v;
            } else {
                self::arrayToTableArray1($v, $d - 1, $r, $rKey, $key . $k);
            }
        }
    }

    protected static function genTbB(array $arr, array $h, array $s, bool $rs): string
    {
        $r = PHP_EOL . $s[10];
        $j = 0;
        foreach ($arr['key'] as $key) {
            foreach ($arr as $k => $v) {
                $tmp = array_key_exists($key, $v) ? $v[$key] : ($k === 'key' ? $key : "");
                $r .= $s[11] . self::getTbC($h[$k], $tmp) . $s[11] . $s[10];
            }
            $r .= PHP_EOL;
            $i = 0;
            if (++$j < count($arr['key'])) {
                if ($rs) {
                    $r .= $s[5];
                    foreach ($h as $w) {
                        $r .= self::strPad("", $w + 2, $s[9]);
                        if (++$i < count($h)) $r .= $s[8];
                    };
                    $r .= $s[6] . PHP_EOL;
                }
                $r .= $s[10];
            }

        }
        return $r;
    }

    protected static function genTbH(array $h, array $s): string
    {
        $r = $s[0];
        $i = 0;
        foreach ($h as $k => $w) {
            $r .= self::strPad("", $w + 2, $s[9]);
            if (++$i < count($h)) $r .= $s[4];
        }
        $r .= $s[1] . PHP_EOL . $s[10];
        foreach ($h as $k => $w) {
            $r .= $s[11] . self::strPad($k, $w, $s[11], STR_PAD_RIGHT) . $s[11] . $s[10];
        }
        $i = 0;
        $r .= PHP_EOL . $s[5];
        foreach ($h as $k => $w) {
            $r .= self::strPad("", $w + 2, $s[9]);
            if (++$i < count($h)) $r .= $s[8];
        }
        $r .= $s[6];
        return $r;
    }

    protected static function genTbF(array $h, array $s): string
    {
        $r = $s[2];
        $i = 0;
        foreach ($h as $k => $w) {
            $r .= self::strPad("", $w + 2, $s[9]);
            if (++$i < count($h)) $r .= $s[7];
        }
        $r .= $s[3];
        return $r;
    }

    protected static function getTbH(array $arr): array
    {
        $h = [];
        foreach ($arr as $k => $v) {
            $h[$k] = mb_strlen($k);
            foreach ($v as $key => $val) {
                $h[$k] = max($h[$k], self::getTbCW($val));
            }
        }
        return $h;
    }

    protected static function getTbCW($v): int
    {
        if (is_string($v)) return mb_strlen($v);
        elseif (is_numeric($v)) return mb_strlen((string)$v);
        elseif (is_bool($v)) return $v ? 4 : 5;
        elseif (is_null($v)) return 4;
        elseif (is_array($v)) return mb_strlen(json_encode($v));
        else throw new \Exception(__CLASS__ . "::" . __FUNCTION__ . " Error: unknown type of " . gettype($v));
    }

}