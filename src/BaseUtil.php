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

class BaseUtil
{

    public static function base32Encode(string $str, bool $inputBinary = false)
    {
        if (!$inputBinary) $str = self::hex2Bin($str);
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
        $bytes = unpack('C*', $str);
        $r = '';
        $head = reset($bytes); // head here is to make sure ending is not always frequently appeared letters 10000,11000,00000
        $bl = 0; // buffer length
        $b = 0; // buffer
        while (count($bytes)) {
            $b <<= 8;
            $b += array_shift($bytes);
            $bl += 8;
            while ($bl >= 5) {
                $bl -= 5;
                $r .= $chars[$b >> $bl];
                $b -= $b >> $bl << $bl;
            }
        }
        if ($bl != 0) {
            $b <<= 5 - $bl;
            $b += $head >> (3 + $bl);
            $r .= $chars[$b];
        }
        return $r;
    }

    public static function bin2Hex(string $bin): string
    {
        $len = strlen($bin);
        return str_pad(bin2hex($bin), $len * 2, "0", STR_PAD_LEFT);
    }

    public static function hex2Bin(string $hex): string
    {
        $len = strlen($hex);
        return str_pad(hex2bin($hex), $len / 2, "\x0", STR_PAD_LEFT);
    }

    public static function base32Decode(string $str, bool $outputBinary = false)
    {
        if (!$str) return "";
        $str = self::base32StringCheck($str);
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
        for ($i = 0; $i < 32; $i++)
            $charmap[$chars[$i]] = $i;
        $strarr = str_split($str);
        $buffer = $charmap[array_shift($strarr)];
        $bl = 5;  // buffer length
        $r = '';
        while (!empty($strarr)) {
            while ($bl < 8) {
                $buffer <<= 5;
                if (empty($strarr))
                        throw new \Exception(__CLASS__ . '::' . __FUNCTION__ . ' Invalid base32 string');
                $buffer += $charmap[array_shift($strarr)];
                $bl += 5;
            }
            $bl -= 8;
            $r .= pack('C', $buffer >> $bl);
            $buffer -= $buffer >> $bl << $bl;
        }
        return $outputBinary ? $r : self::bin2Hex($r);
    }

    private static function base32StringCheck(&$str)
    {
        $str = strtoupper($str);
        $search = ['1', '0', '8', '9'];
        $replace = ['L', 'O', 'B', 'G'];
        return str_replace($search, $replace, $str);
    }
}
