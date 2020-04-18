<?php
require_once(__DIR__ . '/../src/BaseUtil.php');
require_once(__DIR__ . '/../src/BashUtil.php');
require_once(__DIR__ . '/../src/CmnUtil.php');
require_once(__DIR__ . '/../src/TestUtil.php');

use Dat\Utils\BaseUtil;
use Dat\Utils\BashUtil;
use Dat\Utils\CmnUtil;

$test = 'test';
CmnUtil::debug($test, 'Test');