<?php

namespace Dat\Utils;


class IpUtil
{
    public static function getIpInfo($ip)
    {
        if (class_exists("\Dat\Ip\Ip")) {
            return \Dat\Ip\Ip::getInfo($ip);
        }
        $ipCmd = __DIR__ . '/../../ipg/ip';
        if (!file_exists($ipCmd)) {
            throw new \Exception("Package ipg does not exist");
        }
        return trim(shell_exec($ipCmd . " $ip"));
    }
}