<?php

use Dat\Utils\GeoUtil;
use Dat\Utils\AbstractTest;
use Dat\Utils\Coordinate;
use Location\Polygon;

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

    public function testGetRadius()
    {
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $p = new Polygon();
        $points = GeoUtil::createCoordinatesByStrArr(['0,0', '0,180', '90,0', '90,180']);
        $p->addPoints($points);
        $input = [$p];
        $r = call_user_func_array($fullMethodName, $input);
        $er = GeoUtil::EARTH_RADIUS * pi() / 4;
        self::assertEqualsWithDelta($er, $r, 0.00001);
    }

    public function testSimplifyPolygon()
    {
        $a = [
            '14.714730262756,100.4697876',
            '14.697169303894,100.4965286',
            '14.672991752625,100.5109711',
            '14.610842704773,100.4935913',
            '14.547979354858,100.4926529',
            '14.501179695129,100.5013885',
            '14.457901000977,100.4928284',
            '14.437748908997,100.4612808',
            '14.44324016571,100.4189301',
            '14.504490852356,100.4141159',
            '14.488221168518,100.3177185',
            '14.496430397034,100.2872009',
            '14.481008529663,100.25383',
            '14.488760948181,100.2326431',
            '14.52184009552,100.2076111',
            '14.555229187012,100.197197',
            '14.577770233154,100.2111816',
            '14.607510566711,100.1959915',
            '14.650389671326,100.2316284',
            '14.690299987793,100.2161026',
            '14.740090370178,100.2068176',
            '14.776480674743,100.2114182',
            '14.789620399475,100.2285385',
            '14.796950340271,100.2469101',
            '14.783900260925,100.2919998',
            '14.799441337586,100.3323898',
            '14.773131370544,100.3421326',
            '14.763200759888,100.3701782',
            '14.741200447082,100.3821411',
            '14.75426197052,100.4081268',
            '14.726392745972,100.4161758',
            '14.714730262756,100.4697876'];
        $points = GeoUtil::createCoordinatesByStrArr($a);
        $polygon = new Polygon();
        $polygon->addPoints($points);
        $method = self::getTargetMethod(__FUNCTION__);
        $fullMethodName = self::TARGET_CLASS . '::' . $method;
        $i = count($a);
        while (--$i > 2) {
            $r = call_user_func_array($fullMethodName, [$polygon, $i]);
            self::assertLessThan($i + 1, $r->getNumberOfPoints());
            self::assertLessThan($r->getNumberOfPoints(), $i - 3);
        }
    }
}