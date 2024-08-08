<?php

namespace App\Tests\App\Helper;

use App\Helper\UnitConverter;
use PHPUnit\Framework\TestCase;

class UnitConverterTest extends TestCase
{
    /** @var UnitConverter */
    private $unitConverter;

    protected function setUp(): void
    {
        $this->unitConverter = new UnitConverter();
    }

    public function testConvertToGrams()
    {
        $quantity = 2.5;
        $expected = 2500;
        $result = $this->unitConverter->convertToGrams($quantity);
        $this->assertEquals($expected, $result);
    }

    public function testConvertToGramsWithZero()
    {
        $quantity = 0;
        $expected = 0;
        $result = $this->unitConverter->convertToGrams($quantity);
        $this->assertEquals($expected, $result);
    }

    public function testConvertToGramsWithNegativeValue()
    {
        $quantity = -1.5;
        $expected = -1500;
        $result = $this->unitConverter->convertToGrams($quantity);
        $this->assertEquals($expected, $result);
    }

    public function testConvertToKilograms()
    {
        $quantity = 2500;
        $expected = 2.5;
        $result = $this->unitConverter->convertToKilograms($quantity);
        $this->assertEquals($expected, $result);
    }

    public function testConvertToKilogramsWithZero()
    {
        $quantity = 0;
        $expected = 0;
        $result = $this->unitConverter->convertToKilograms($quantity);
        $this->assertEquals($expected, $result);
    }

    public function testConvertToKilogramsWithNegativeValue()
    {
        $quantity = -1500;
        $expected = -1.5;
        $result = $this->unitConverter->convertToKilograms($quantity);
        $this->assertEquals($expected, $result);
    }

    public function testConvertToGramsWithPrecision()
    {
        $quantity = 1.2345;
        $expected = 1234.5;
        $result = $this->unitConverter->convertToGrams($quantity);
        $this->assertEqualsWithDelta($expected, $result, 0.01);
    }

    public function testConvertToKilogramsWithPrecision()
    {
        $quantity = 1234.5;
        $expected = 1.2345;
        $result = $this->unitConverter->convertToKilograms($quantity);
        $this->assertEqualsWithDelta($expected, $result, 0.01);
    }

    protected function tearDown(): void
    {
        $this->unitConverter = null;
    }
}
