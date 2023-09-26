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
//
//    /**
//     * 
//     * @param type $input
//     * @param type $er
//     * @dataProvider providerGetContainingRectangular
//     */
//    public function testGetContainingRectangular($input, $er)
//    {
//        $method = self::getTargetMethod(__FUNCTION__);
//        $fullMethodName = self::TARGET_CLASS . '::' . $method;
//        $r = call_user_func_array($fullMethodName, $input);
//        self::assertEquals($er, $r);
//    }
//
//    public function providerGetContainingRectangular()
//    {
//        $c = ['12.306197452150442, 99.93864817827209',
//            '14.216034214829355, 99.94646272485335',
//            '14.080887496099647, 101.35137584678407',
//            '12.82388448156738, 101.17174743489089',
//            '13.48936421789897, 100.50548973201118',];
//        $points = GeoUtil::createCoordinatesByStrArr($c);
//        return [
//            [[$points], ['east' => 101.35137584678407, 'south' => 12.306197452150442, 'west' => 99.93864817827209, 'north' => 14.216034214829355]],
//        ];
//    }

    /**
     * 
     * @param type $input
     * @param type $er
     * @dataProvider providerNorthOf
     */
    public function testNorthOf($input, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $r = call_user_func_array($fullMethodName, $input);
        self::assertEquals($er, $r);
    }

    public function providerNorthOf()
    {
        return [
            [[new Coordinate(13.745519631139508, 100.53422512563881), 1.9 * 1000], new Coordinate(13.76258762153778, 100.53422512563881)],
            [[new Coordinate(89, 100), 1000 * 1000], new Coordinate(90, 100)],
            [[new Coordinate(1, 100), -20000 * 1000], new Coordinate(-90, 100)],
        ];
    }

    /**
     * 
     * @param type $input
     * @param type $er
     * @dataProvider providerEastOf
     */
    public function testEastOf($input, $er)
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $r = call_user_func_array($fullMethodName, $input);
        self::assertEquals($er, $r);
    }

    public function providerEastOf()
    {
        return [
            [[new Coordinate(13.745519631139508, 100.53422512563881), 1.9 * 1000], new Coordinate(13.745519631139508, 100.55179634426068)],
            [[new Coordinate(60, 100), GeoUtil::EARTH_RADIUS * cos(deg2rad(60)) * pi()], new Coordinate(60, -80)],
            [[new Coordinate(60, 100), GeoUtil::EARTH_RADIUS * cos(deg2rad(60)) * pi() * 5], new Coordinate(60, -80)],
            [[new Coordinate(0, 100), GeoUtil::EARTH_RADIUS * pi() * 3], new Coordinate(0, -80)],
        ];
    }
}