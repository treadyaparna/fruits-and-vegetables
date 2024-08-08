<?php

namespace App\Helper;

class UnitConverter
{
    /**
     * Convert the quantity to grams.
     *
     * @param float $quantity
     * @return float
     */
    public function convertToGrams(float $quantity): float
    {
        return $quantity * 1000;
    }

    /**
     * Convert the quantity to kilograms.
     *
     * @param float $quantity
     * @return float
     */
    public function convertToKilograms(float $quantity): float
    {
        return $quantity / 1000;
    }
}