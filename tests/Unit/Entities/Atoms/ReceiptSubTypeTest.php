<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptSubType
 */
class ReceiptSubTypeTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Entities\Atoms\ReceiptSubType::__construct
     * @covers Innokassa\MDK\Entities\Atoms\ReceiptSubType::getCode
     */
    public function testResponseCode()
    {
        $this->assertSame(
            ReceiptSubType::HAND,
            (new ReceiptSubType(ReceiptSubType::HAND))->getCode()
        );
        $this->assertSame(
            ReceiptSubType::PRE,
            (new ReceiptSubType(ReceiptSubType::PRE))->getCode()
        );
        $this->assertSame(
            ReceiptSubType::FULL,
            (new ReceiptSubType(ReceiptSubType::FULL))->getCode()
        );

        $this->expectException(InvalidArgumentException::class);
        $vat = new ReceiptSubType(3);
    }

    /**
     * @covers Innokassa\MDK\Entities\Atoms\ReceiptSubType::all
     */
    public function testAll()
    {
        $a = ReceiptSubType::all();
        $this->assertIsArray($a);
        $this->assertContainsOnlyInstancesOf(ReceiptSubType::class, $a);
        $this->assertCount(3, $a);
    }
}
