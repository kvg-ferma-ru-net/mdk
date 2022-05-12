<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Atoms\Vat;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Entities\Atoms\Vat
 */
class VatTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Entities\Atoms\Vat::__construct
     * @covers Innokassa\MDK\Entities\Atoms\Vat::getCode
     * @covers Innokassa\MDK\Entities\Atoms\Vat::getName
     */
    public function testGetRatValue()
    {
        $vat = new Vat("");
        $this->assertSame(Vat::CODE_WITHOUT, $vat->getCode());
        $this->assertSame("Не облагается", $vat->getName());

        $vat = new Vat("20%");
        $this->assertSame(Vat::CODE_20, $vat->getCode());
        $this->assertSame("20", $vat->getName());

        $vat = new Vat("10%");
        $this->assertSame(Vat::CODE_10, $vat->getCode());
        $this->assertSame("10", $vat->getName());

        $vat = new Vat("20/120");
        $this->assertSame(Vat::CODE_120, $vat->getCode());
        $this->assertSame("20/120", $vat->getName());

        $vat = new Vat("10/110");
        $this->assertSame(Vat::CODE_110, $vat->getCode());
        $this->assertSame("10/110", $vat->getName());

        $vat = new Vat("0");
        $this->assertSame(Vat::CODE_0, $vat->getCode());
        $this->assertSame("0", $vat->getName());


        $vat = new Vat(1);
        $this->assertSame(Vat::CODE_20, $vat->getCode());
        $this->assertSame("20", $vat->getName());

        $vat = new Vat(2);
        $this->assertSame(Vat::CODE_10, $vat->getCode());
        $this->assertSame("10", $vat->getName());

        $vat = new Vat(3);
        $this->assertSame(Vat::CODE_120, $vat->getCode());
        $this->assertSame("20/120", $vat->getName());

        $vat = new Vat(4);
        $this->assertSame(Vat::CODE_110, $vat->getCode());
        $this->assertSame("10/110", $vat->getName());

        $vat = new Vat(5);
        $this->assertSame(Vat::CODE_0, $vat->getCode());
        $this->assertSame("0", $vat->getName());

        $vat = new Vat(6);
        $this->assertSame(Vat::CODE_WITHOUT, $vat->getCode());
        $this->assertSame("Не облагается", $vat->getName());

        $this->expectException(InvalidArgumentException::class);
        $vat = new Vat("15%");
    }

    /**
     * @covers Innokassa\MDK\Entities\Atoms\Vat::all
     */
    public function testAll()
    {
        $a = Vat::all();
        $this->assertIsArray($a);
        $this->assertContainsOnlyInstancesOf(Vat::class, $a);
        $this->assertCount(6, $a);
    }
}
