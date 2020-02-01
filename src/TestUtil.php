<?php

namespace Dat\Utils;

use PHPUnit\Framework\TestCase;

class TestUtil
{

    public static function getTargetMethod($function)
    {
        return lcfirst(substr($function, 4));
    }

    /**
     *
     * Only work with all static functions
     * @param $function
     * @param $class
     * @param $testArr
     * @throws \ReflectionException
     */
    public static function runWithValueArray($function, $class, $testArr)
    {
        $methodName = self::getTargetMethod($function);
        $fullMethodName = $class . '::' . $methodName;
        $targetMethod = new \ReflectionMethod($fullMethodName);
        $isStatic = $targetMethod->isStatic();


        foreach ($testArr as $testcase) {
            $expR = end($testcase);
            array_pop($testcase);
            if ($isStatic) {
                $actR = call_user_func_array($fullMethodName, $testcase);
            } else {
                throw new \Exception(__FUNCTION__ . " Does not support non static function");
            }
            TestCase::assertEquals($expR, $actR);
        }
    }
}
