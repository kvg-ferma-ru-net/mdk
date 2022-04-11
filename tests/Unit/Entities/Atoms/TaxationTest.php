<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Entities\Atoms\Taxation
 * @uses Innokassa\MDK\Entities\AtomAbstract
 */
class TaxationTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Entities\Atoms\Taxation::__construct
     * @covers Innokassa\MDK\Entities\Atoms\Taxation::getCode
     * @covers Innokassa\MDK\Entities\Atoms\Taxation::getName
     */
    public function test()
    {
        $taxation = new Taxation(Taxation::ORN);
        $this->assertSame(Taxation::ORN, $taxation->getCode());
        $this->assertSame("ОРН", $taxation->getName());

        $taxation = new Taxation(Taxation::USN);
        $this->assertSame(Taxation::USN, $taxation->getCode());
        $this->assertSame("УСН доход", $taxation->getName());

        $taxation = new Taxation(Taxation::USNDR);
        $this->assertSame(Taxation::USNDR, $taxation->getCode());
        $this->assertSame("УСН доход - расход", $taxation->getName());

        $taxation = new Taxation(Taxation::ESN);
        $this->assertSame(Taxation::ESN, $taxation->getCode());
        $this->assertSame("ЕСН", $taxation->getName());

        $taxation = new Taxation(Taxation::PSN);
        $this->assertSame(Taxation::PSN, $taxation->getCode());
        $this->assertSame("ПСН", $taxation->getName());

        $this->expectException(InvalidArgumentException::class);
        new Taxation(3);
    }

    /**
     * @covers Innokassa\MDK\Entities\Atoms\Taxation::all
     */
    public function testAll()
    {
        $a = Taxation::all();
        $this->assertIsArray($a);
        $this->assertContainsOnlyInstancesOf(Taxation::class, $a);
        $this->assertCount(5, $a);
    }
}
