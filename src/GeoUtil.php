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
}