<?php
require_once(__DIR__ . '/../src/BaseUtil.php');
require_once(__DIR__ . '/../src/BashUtil.php');
require_once(__DIR__ . '/../src/CmnUtil.php');
require_once(__DIR__ . '/../src/TestUtil.php');

use Dat\Utils\BaseUtil;
use Dat\Utils\BashUtil;
use Dat\Utils\CmnUtil;

$test = 'test';
$test = [];
$tmp = [
    "test1" => 0,
    "test2" => 1,
];
$i = 0;
while (true) {
    $tmp['test1']++;
    $tmp['test2'] += 2;
    $tmp["test$i"] = $i++;
    $test [] = $tmp;
    CmnUtil::liveDebug(CmnUtil::arrayToTable($test, 1, 2, 0, 1), 0.5);
}