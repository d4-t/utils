<?php

namespace Dat\Utils;

use InvalidArgumentException;

class Coordinate
{
    protected $lat;
    protected $lng;
    protected $alt;

    const MIN_ALT = -500.0;
    const MAX_ALT = 10000.0;

    public function __construct(float $lat, float $lng, ?float $alt = null)
    {
        if (!$this->isValidLatitude($lat))
                throw new InvalidArgumentException("Latitude value must be numeric -90.0 .. +90.0 (given: $lat)");

        if (!$this->isValidLongitude($lng))
                throw new InvalidArgumentException("Longitude value must be numeric -180.0 .. +180.0 (given: $lng)");

        if (!$this->isValidAltitude($alt))
                throw new InvalidArgumentException("Altitude value must be numeric " . self::MIN_ALT . " .. " . self::MAX_ALT . "0 (given: $alt)");

        $this->lat = $lat;
        $this->lng = $lng;
        $this->alt = $alt;
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getLng(): float
    {
        return $this->lng;
    }

    public function getAlt(): ?float
    {
        return $this->alt;
    }

    protected function isValidLatitude(float $lat): bool
    {
        return $this->isNumericInBounds($lat, -90.0, 90.0);
    }

    protected function isValidLongitude(float $lng): bool
    {
        return $this->isNumericInBounds($lng, -180.0, 180.0);
    }

    protected function isValidAltitude(?float $alt): bool
    {
        return $alt === null ? true : $this->isNumericInBounds($alt, self::MIN_ALT, self::MAX_ALT);
    }

    /**
     * Checks if the given value is (1) numeric, and (2) between lower
     * and upper bounds (including the bounds values).
     */
    protected function isNumericInBounds(float $value, float $lower, float $upper): bool
    {
        return !($value < $lower || $value > $upper);
    }
}