<?php

use Dat\Utils\GeoUtil;
use Dat\Utils\AbstractTest;
use Dat\Utils\Coordinate;

require_once dirname(__DIR__) . '/vendor/autoload.php';
class GeoUtilTest extends AbstractTest
{
    const TARGET_CLASS = GeoUtil::class;

    /**
     * 
     * @param type $input
     * @param type $er
     * @dataProvider providerGetDistanceByLatLng
     */
    public function testGetDistanceByLatLng($input, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $r = call_user_func_array($fullMethodName, $input);
        self::assertEquals($er, $r);
    }

    public function providerGetDistanceByLatLng()
    {
        return [
            [[13.7456416, 100.5408628, 13.7456416, 100.5408628], 0],
            [[13.746037244124633, 100.53468288675624, 13.737303599084951, 100.56030331220745], 2936.057447565073],
        ];
    }

    /**
     * 
     * @param type $input
     * @param type $er
     * @dataProvider providerGetDistanceByLatLngAlt
     */
    public function testGetDistanceByLatLngAlt($input, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $r = call_user_func_array($fullMethodName, $input);
        self::assertEquals($er, $r);
    }

    public function providerGetDistanceByLatLngAlt()
    {
        return [
            [[13.7456416, 100.5408628, 0, 13.7456416, 100.5408628, 0], 0],
            [[13.7456416, 100.5408628, 0, 13.7456416, 100.5408628, 2.5], 2.5],
            [[13.746037244124633, 100.53468288675624, 0, 13.737303599084951, 100.56030331220745, 0], 2936.057447565073],
            [[13.746037244124633, 100.53468288675624, 0, 13.737303599084951, 100.56030331220745, 50], 2936.483157690902],
        ];
    }

    /**
     * 
     * @param type $input
     * @param type $er
     * @dataProvider providerGetDistanceByCoordinates
     */
    public function testGetDistanceByCoordinates($input, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $r = call_user_func_array($fullMethodName, $input);
        self::assertEquals($er, $r);
    }

    public function providerGetDistanceByCoordinates()
    {
        return [
            [[new Coordinate(0.0, 0.0), new Coordinate(0.0, 0.0)], 0],
            [[new Coordinate(0.0, 0.0, 50), new Coordinate(0.0, 0.0)], 0],
            [[new Coordinate(0.0, 0.0, 50), new Coordinate(0.0, 0.0, 0)], 50],
            [[new Coordinate(0.0, 0.0, 50), new Coordinate(0.0, 0.0, 0), true], 0],
            [[new Coordinate(0.0, 0.0), new Coordinate(0.0, 0.0, 50)], 0],
            [[new Coordinate(13.746037244124633, 100.53468288675624), new Coordinate(13.737303599084951, 100.56030331220745)], 2936.057447565073],
            [[new Coordinate(13.746037244124633, 100.53468288675624), new Coordinate(13.737303599084951, 100.56030331220745, 50.2)], 2936.057447565073],
            [[new Coordinate(13.746037244124633, 100.53468288675624, 0), new Coordinate(13.737303599084951, 100.56030331220745, 50), true], 2936.057447565073],
            [[new Coordinate(13.746037244124633, 100.53468288675624, 0), new Coordinate(13.737303599084951, 100.56030331220745, 50)], 2936.483157690902],
        ];
    }
}