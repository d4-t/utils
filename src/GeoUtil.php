<?php

/* * ********************************
 * *     ____      _____    _______  *
 * **   /\  __`\  /| |\ \  /\__  __\  *
 *  **  \ \ \_\ \ || |_\ \ \/_/\ \_/   *
 *   **  \ \____| ||_|\ \_\   \ \ \     *
 *    **  \/___/  /_/  \/_/    \/_/      *
 *     **       Copyright 2014-2023 Dat   *
 *      *********************************** */

// Refer to https://github.com/mjaschen/phpgeo

namespace Dat\Utils;

use Dat\Utils\Coordinate;
use Location\Coordinate as OriCoordinate;
use InvalidArgumentException;
use Location\Bounds;

require_once dirname(__DIR__) . '/vendor/autoload.php';
class GeoUtil
{
    const EARTH_RADIUS = 6378137.0; // in meters

    /**
     * et Distance by coordinates, return meters
     * @param Coordinate $coor1
     * @param Coordinate $coor2
     * @param bool $ignoreAlt
     * @return float
     */
    public static function getDistanceByCoordinates(Coordinate $coor1, Coordinate $coor2, bool $ignoreAlt = false): float
    {
        return($ignoreAlt || $coor1->getAlt() === null || $coor2->getAlt() === null) ?
                self::getDistanceByLatLng($coor1->getLat(), $coor1->getLng(), $coor2->getLat(), $coor2->getLng()) :
                self::getDistanceByLatLngAlt($coor1->getLat(), $coor1->getLng(), $coor1->getAlt(), $coor2->getLat(), $coor2->getLng(), $coor2->getAlt());
    }

    /**
     * Get Distance by coordinates in latitude and longitude, return meters
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return float
     */
    public static function getDistanceByLatLng(float $lat1, float $lng1, float $lat2, float $lng2): float
    {

        $lngr1 = deg2rad($lng1);
        $lngr2 = deg2rad($lng2);
        $latr1 = deg2rad($lat1);
        $latr2 = deg2rad($lat2);
        $dlong = $lngr2 - $lngr1;
        $dlati = $latr2 - $latr1;
        $val = pow(sin($dlati / 2), 2) + cos($latr1) * cos($latr2) * pow(sin($dlong / 2), 2);
        $res = 2 * asin(sqrt($val));
        return ($res * self::EARTH_RADIUS);
    }

    /**
     * Get Distance by coordinates in latitude and longitude and altitude, return meters
     * @param float $lat1
     * @param float $lng1
     * @param float $alt1
     * @param float $lat2
     * @param float $lng2
     * @param float $alt2
     * @return float
     */
    public static function getDistanceByLatLngAlt(float $lat1, float $lng1, float $alt1, float $lat2, float $lng2, float $alt2): float
    {
        $d = self::getDistanceByLatLng($lat1, $lng1, $lat2, $lng2);
        return sqrt(pow($d, 2) + pow($alt1 - $alt2, 2));
    }

    /**
     * Create array of /Dat/Util/Coordinate by array of string containing latitude, longitude (and optionaly altitude)
     * @param array $strArr
     * @return type
     */
    public static function createCoordinatesByStrArr(array $strArr)
    {
        $r = [];
        foreach ($strArr as $cStr) {
            $r[] = self::createCoordinateByStr($cStr);
        }
        return $r;
    }

    /**
     * Create /Dat/Util/Coordinate by string containing latitude, longitude (and optionaly altitude)
     * @param string $str
     * @return Coordinate
     * @throws InvalidArgumentException
     */
    public static function createCoordinateByStr(string $str): Coordinate
    {
        $a = explode(',', $str);
        if (count($a) < 2 || count($a) > 3)
                throw new InvalidArgumentException(__FUNCTION__ . ' must have 2 or 3 components ' . count($a) . ' are given');
        return (count($a) === 2) ? new Coordinate($a[0], $a[1]) : new Coordinate($a[0], $a[1], $a[2]);
    }

//    /**
//     * Get the smallest rectangular that contains all the given coordinates
//     * @param array $coordinates
//     * @return array
//     * @throws InvalidArgumentException
//     * 
//     * todo fix rectangular through date line
//     */
//    public static function getContainingRectangular(array $coordinates): array
//    {
//        if (count($coordinates) < 2)
//                throw new InvalidArgumentException(__FUNCTION__ . ' input must have at least 2 points');
//        if (!($coordinates[0] instanceof Coordinate))
//                throw new InvalidArgumentException(__FUNCTION__ . " input must be array of \Location\Coordinate");
//        $north = $south = $coordinates[0]->getLat();
//        $east = $west = $coordinates[0]->getLng();
//
//        foreach ($coordinates as $coordinate) {
//            if (!($coordinate instanceof Coordinate))
//                    throw new InvalidArgumentException(__FUNCTION__ . " input must be array of \Location\Coordinate");
//            $east = max($east, $coordinate->getLng());
//            $west = min($west, $coordinate->getLng());
//            $north = max($north, $coordinate->getLat());
//            $south = min($south, $coordinate->getLat());
//        }
//        return ['east' => $east, 'south' => $south, 'west' => $west, 'north' => $north];
//    }
//
//    /**
//     *  Get the smallest rectangular that contains the given polygon
//     * @param Polygon $polygon
//     * @return type
//     */
//    public static function getPloygonContainingRectangular(Polygon $polygon)
//    {
//        return self::getContainingRectangular($polygon->getPoints());
//    }

    public static function northOf(OriCoordinate $coord, float $distance)
    {
        $lat = $coord->getLat();
        $newLat = max(min($lat + rad2deg($distance / self::EARTH_RADIUS), 90), -90);
        return new Coordinate($newLat, $coord->getLng());
    }

    public static function southOf(OriCoordinate $coord, float $distance)
    {
        return self::northOf($coord, -$distance);
    }

    public static function eastOf(OriCoordinate $coord, float $distance)
    {
        $lat = $coord->getLat();
        $lng = $coord->getLng();
        $latr = deg2rad($lat);
        $r = self::EARTH_RADIUS * cos(abs($latr));
        $newLng = fmod($lng + rad2deg($distance / $r), 360); // % 360;
        $newLng = $newLng > 180 ? ($newLng - 360) : ($newLng < -180 ? $newLng + 360 : $newLng);
        return new Coordinate($lat, $newLng);
    }

    public static function westOf(OriCoordinate $coord, float $distance)
    {
        return self::eastOf($coord, -$distance);
    }

    public static function getExtendedBounds(Bounds $bounds, float $distance): Bounds
    {
        $nw = $bounds->getNorthWest();
        $se = $bounds->getSouthEast();
        $newNw = self::westOf(self::northOf($nw, $distance), $distance);
        $newSe = self::eastOf(self::southOf($se, $distance), $distance);
        return new Bounds($newNw, $newSe);
    }
}