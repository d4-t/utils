<?php


namespace Dat\Utils;

use PHPUnit\Framework\TestCase;
require_once('../src/TestUtil.php');

class AbstractTest extends TestCase
{
    public static function getTargetMethod($function)
    {
        return TestUtil::getTargetMethod($function);
    }

    public static function runWithValueArray($function, $class, $testArr)
    {
        TestUtil::runWithValueArray($function, $class, $testArr);
    }

    public static function testTest()
    {
        self::assertEquals(1, 1);
    }


}