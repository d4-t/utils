<?php

namespace Dat\Utils;

class Coordinate extends \Location\Coordinate
{
    protected $alt;

    const MIN_ALT = -500.0;
    const MAX_ALT = 10000.0;

    public function __construct(float $lat, float $lng, ?float $alt = null, ?Ellipsoid $ellipsoid = null)
    {
        parent::__construct($lat, $lng, $ellipsoid);

        if (!$this->isValidAltitude($alt))
                throw new InvalidArgumentException("Altitude value must be numeric " . self::MIN_ALT . " .. " . self::MAX_ALT . "0 (given: $alt)");

        $this->alt = $alt;
    }

    public function getAlt(): ?float
    {
        return $this->alt;
    }

    protected function isValidAltitude(?float $alt): bool
    {
        return $alt === null ? true : $this->isNumericInBounds($alt, self::MIN_ALT, self::MAX_ALT);
    }

    public function toString()
    {
        return $this->lat . ',' . $this->lng;
    }
}