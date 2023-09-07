<?php


namespace Dat\Utils;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../src/TestUtil.php');

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

    /**
     * @param string $class
     * @param string $function
     * @param $input
     * @param $expectedResult
     */
    public static function assertSameTest(string $class, string $function, $input, $expectedResult)
    {
        $r = call_user_func_array("$class::" . self::getTargetMethod($function), $input);
        self::assertSame($expectedResult, $r);
    }

    /**
     * @param string $class
     * @param string $function
     * @param $input
     * @param $expectedResult
     */
    public static function assertEqualsTest(string $class, string $function, $input, $expectedResult)
    {
        $r = call_user_func_array("$class::" . self::getTargetMethod($function), $input);
        self::assertEquals($expectedResult, $r);
    }
}