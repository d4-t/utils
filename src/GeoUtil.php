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
use InvalidArgumentException;
use Location\Bounds;
use Location\Coordinate as OriCoordinate;
use Location\Polygon;
use Location\Polyline;
use Location\Processor\Polyline\SimplifyBearing;

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
    public static function getDistanceByCoordinates(OriCoordinate $coor1, OriCoordinate $coor2, bool $ignoreAlt = false): float
    {
        if ($coor1 instanceof Coordinate && $coor2 instanceof OriCoordinate && !$ignoreAlt && $coor1->getAlt() !== null && $coor2->getAlt() !== null)
                return self::getDistanceByLatLngAlt($coor1->getLat(), $coor1->getLng(), $coor1->getAlt(), $coor2->getLat(), $coor2->getLng(), $coor2->getAlt());
        else
                return self::getDistanceByLatLng($coor1->getLat(), $coor1->getLng(), $coor2->getLat(), $coor2->getLng());
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
     * @return Array of Coordinates
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

    /**
     * Get new coordinate north of given. Stop at north pole
     * @param OriCoordinate $coord
     * @param float $distance in meters
     * @return Coordinate
     */
    public static function northOf(OriCoordinate $coord, float $distance): Coordinate
    {
        $lat = $coord->getLat();
        $newLat = max(min($lat + rad2deg($distance / self::EARTH_RADIUS), 90), -90);
        return new Coordinate($newLat, $coord->getLng());
    }

    /**
     * Get new coordinate sourth of given. Stop at south pole
     * @param OriCoordinate $coord
     * @param float $distance
     * @return Coordinate
     */
    public static function southOf(OriCoordinate $coord, float $distance): Coordinate
    {
        return self::northOf($coord, -$distance);
    }

    /**
     * Get new coordinate east of given
     * @param OriCoordinate $coord
     * @param float $distance in meters
     * @return Coordinate
     */
    public static function eastOf(OriCoordinate $coord, float $distance): Coordinate
    {
        $lat = $coord->getLat();
        $lng = $coord->getLng();
        $latr = deg2rad($lat);
        $r = self::EARTH_RADIUS * cos(abs($latr));
        $newLng = fmod($lng + rad2deg($distance / $r), 360); // % 360;
        $newLng = $newLng > 180 ? ($newLng - 360) : ($newLng < -180 ? $newLng + 360 : $newLng);
        return new Coordinate($lat, $newLng);
    }

    /**
     *  Get new coordinate west of given
     * @param OriCoordinate $coord
     * @param float $distance
     * @return Coordinate
     */
    public static function westOf(OriCoordinate $coord, float $distance): Coordinate
    {
        return self::eastOf($coord, -$distance);
    }

    /**
     * Get new bounds extended by distance
     * @param Bounds $bounds
     * @param float $distance in meters
     * @return Bounds
     */
    public static function getExtendedBounds(Bounds $bounds, float $distance): Bounds
    {
        $nw = $bounds->getNorthWest();
        $se = $bounds->getSouthEast();
        $newNw = self::westOf(self::northOf($nw, $distance), $distance);
        $newSe = self::eastOf(self::southOf($se, $distance), $distance);
        return new Bounds($newNw, $newSe);
    }

    public static function getRadius(Polygon $polygon): float
    {
        $bounds = $polygon->getBounds();
        return self::getDistanceByCoordinates($bounds->getNorthWest(), $bounds->getSouthEast()) / 2;
    }

    public static function simplifyPolygon(Polygon $polygon, int $maxPoints = 10)
    {
        if ($maxPoints < 3)
                throw new InvalidArgumentException(__FUNCTION__ . " maxPoints must be larger than 2, $maxPoints is given");
        $r = $polygon;
        $bearing = 0;
        while ($r->getNumberOfPoints() > $maxPoints) {
            $processor = new SimplifyBearing(++$bearing);
            $polyline = new Polyline();
            $polyline->addPoints($polygon->getPoints());
            $newPolyline = $processor->simplify($polyline);
            $r = new Polygon();
            $r->addPoints($newPolyline->getPoints());
        }
        return $r;
    }

    /**
     * Get the center of array of coordinates. Use average of all points method, cannot guarantee the center falls in polygon formed by coordinates
     * 
     * @param Array $arr Array of Coordinates
     * @return Coordinate|null
     * @throws InvalidArgumentException
     */
    public static function getCenterOfCoords(Array $arr): ?Coordinate
    {
        $numPoints = count($arr);
        if ($numPoints === 0) return null;

        $sumX = 0;
        $sumY = 0;

        foreach ($arr as $coord) {
            if (!$coord instanceof OriCoordinate)
                    throw new \InvalidArgumentException("Input must be array of Location\Coordinate");
            $sumX += $coord->getLat();
            $sumY += $coord->getLng();
        }

        $centerX = $sumX / $numPoints;
        $centerY = $sumY / $numPoints;

        return new Coordinate($centerX, $centerY);
    }

    /**
     * Sort array of coordinates, by default clockwise
     * 
     * @param Array $arr array of Coordinates
     * @param bool $isClockwise default true
     * @return Array|null
     * @throws InvalidArgumentException
     */
    public static function sortCoords(Array $arr, bool $isClockwise = true): ?Array
    {
        $numPoints = count($arr);
        if ($numPoints === 0) return null;

        $center = self::getCenterOfCoords($arr);
        if (!$center) return null;

        $coordinates = $arr;
        usort($coordinates, function ($a, $b) use ($center, $isClockwise) {
            if (!$a instanceof OriCoordinate || !$b instanceof OriCoordinate)
                    throw new \InvalidArgumentException("Input must be array of Location\Coordinate");
            $angleA = atan2($a->getLat() - $center->getLat(), $a->getLng() - $center->getLng());
            $angleB = atan2($b->getLat() - $center->getLat(), $b->getLng() - $center->getLng());
            if ($angleA === $angleB) return 0;
            $cw = (bool)($angleA > $angleB);
            return $isClockwise ? ($cw ? 1 : -1) : ($cw ? -1 : 1);
        });
        return $coordinates;
    }
}