<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Atoms\Unit;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Entities\Atoms\Unit
 */
class UnitTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Entities\Atoms\Unit::__construct
     * @covers Innokassa\MDK\Entities\Atoms\Unit::getCode
     */
    public function testConstructGetCode()
    {
        $this->assertSame(
            Unit::PIECES,
            (new Unit(Unit::PIECES))->getCode()
        );

        $this->assertSame(
            Unit::GRAM,
            (new Unit(Unit::GRAM))->getCode()
        );

        $this->assertSame(
            Unit::KILOGRAM,
            (new Unit(Unit::KILOGRAM))->getCode()
        );

        $this->assertSame(
            Unit::TON,
            (new Unit(Unit::TON))->getCode()
        );

        $this->assertSame(
            Unit::CENTIMETR,
            (new Unit(Unit::CENTIMETR))->getCode()
        );

        $this->assertSame(
            Unit::DECIMETER,
            (new Unit(Unit::DECIMETER))->getCode()
        );

        $this->assertSame(
            Unit::METER,
            (new Unit(Unit::METER))->getCode()
        );

        $this->assertSame(
            Unit::SQUARE_CENTIMETER,
            (new Unit(Unit::SQUARE_CENTIMETER))->getCode()
        );

        $this->assertSame(
            Unit::SQUARE_DECIMETER,
            (new Unit(Unit::SQUARE_DECIMETER))->getCode()
        );

        $this->assertSame(
            Unit::SQUARE_METER,
            (new Unit(Unit::SQUARE_METER))->getCode()
        );

        $this->assertSame(
            Unit::MILLILITER,
            (new Unit(Unit::MILLILITER))->getCode()
        );

        $this->assertSame(
            Unit::LITRE,
            (new Unit(Unit::LITRE))->getCode()
        );

        $this->assertSame(
            Unit::CUBIC_METER,
            (new Unit(Unit::CUBIC_METER))->getCode()
        );

        $this->assertSame(
            Unit::KILOWATT_HOUR,
            (new Unit(Unit::KILOWATT_HOUR))->getCode()
        );

        $this->assertSame(
            Unit::GIGACALORIES,
            (new Unit(Unit::GIGACALORIES))->getCode()
        );

        $this->assertSame(
            Unit::DAY,
            (new Unit(Unit::DAY))->getCode()
        );

        $this->assertSame(
            Unit::HOUR,
            (new Unit(Unit::HOUR))->getCode()
        );

        $this->assertSame(
            Unit::MINUTE,
            (new Unit(Unit::MINUTE))->getCode()
        );

        $this->assertSame(
            Unit::SECOND,
            (new Unit(Unit::SECOND))->getCode()
        );

        $this->assertSame(
            Unit::KILOBYTES,
            (new Unit(Unit::KILOBYTES))->getCode()
        );

        $this->assertSame(
            Unit::MEGABYTES,
            (new Unit(Unit::MEGABYTES))->getCode()
        );

        $this->assertSame(
            Unit::GIGABYTES,
            (new Unit(Unit::GIGABYTES))->getCode()
        );

        $this->assertSame(
            Unit::TERABYTES,
            (new Unit(Unit::TERABYTES))->getCode()
        );

        $this->assertSame(
            Unit::DEFAULT,
            (new Unit(Unit::DEFAULT))->getCode()
        );

        $this->expectException(InvalidArgumentException::class);
        new Unit(-1);
    }

    /**
     * @covers Innokassa\MDK\Entities\Atoms\Unit::all
     */
    public function testAll()
    {
        $a = Unit::all();
        $this->assertIsArray($a);
        $this->assertContainsOnlyInstancesOf(Unit::class, $a);
        $this->assertCount(24, $a);
    }
}
