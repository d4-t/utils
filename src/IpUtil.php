<?php

namespace Dat\Utils;


class IpUtil
{
    public static function getIpInfo($ip)
    {
        if (class_exists("\Dat\Ip\Ip")) {
            return \Dat\Ip\Ip::getInfoStr($ip);
        }
        $ipCmd = __DIR__ . '/../../ipg/ip';
        if (!file_exists($ipCmd)) {
            throw new \Exception("Package ipg does not exist");
        }
        $r = trim(shell_exec($ipCmd . " $ip  2>&1"));
        if (strpos($r, 'ip2location_db5') !== false) {
            throw new \Exception("Ip Database table does not exist");
        }
        return $r;
    }
}
