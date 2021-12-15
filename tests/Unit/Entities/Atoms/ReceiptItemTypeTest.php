<?php

use PHPUnit\Framework\TestCase;

use Innokassa\MDK\Entities\Atoms\ReceiptItemType;

use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptItemType
 */
class ReceiptItemTypeTest extends TestCase
{

    /**
     * @covers Innokassa\MDK\Entities\Atoms\ReceiptItemType::__construct
     * @covers Innokassa\MDK\Entities\Atoms\ReceiptItemType::getCode
     */
    public function test()
    {
        $this->assertSame(
            ReceiptItemType::AGENT_COMMISSION,
            (new ReceiptItemType(ReceiptItemType::AGENT_COMMISSION))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::COMPOSITE,
            (new ReceiptItemType(ReceiptItemType::COMPOSITE))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::EXCISE,
            (new ReceiptItemType(ReceiptItemType::EXCISE))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::GAMING_BET,
            (new ReceiptItemType(ReceiptItemType::GAMING_BET))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::GAMING_WIN,
            (new ReceiptItemType(ReceiptItemType::GAMING_WIN))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::INSURANCE_FEE,
            (new ReceiptItemType(ReceiptItemType::INSURANCE_FEE))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::LOTTERY_TICKET,
            (new ReceiptItemType(ReceiptItemType::LOTTERY_TICKET))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::LOTTERY_WIN,
            (new ReceiptItemType(ReceiptItemType::LOTTERY_WIN))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::OTHER,
            (new ReceiptItemType(ReceiptItemType::OTHER))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::OUTSTANDING_INCOME,
            (new ReceiptItemType(ReceiptItemType::OUTSTANDING_INCOME))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::PAYMENT,
            (new ReceiptItemType(ReceiptItemType::PAYMENT))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::PLEDGE,
            (new ReceiptItemType(ReceiptItemType::PLEDGE))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::PRODUCT,
            (new ReceiptItemType(ReceiptItemType::PRODUCT))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::PROPERTY_RIGHT,
            (new ReceiptItemType(ReceiptItemType::PROPERTY_RIGHT))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::RESORT_FEE,
            (new ReceiptItemType(ReceiptItemType::RESORT_FEE))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::RIA,
            (new ReceiptItemType(ReceiptItemType::RIA))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::SERVICE,
            (new ReceiptItemType(ReceiptItemType::SERVICE))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::TRADING_FEES,
            (new ReceiptItemType(ReceiptItemType::TRADING_FEES))->getCode()
        );
        $this->assertSame(
            ReceiptItemType::WORK,
            (new ReceiptItemType(ReceiptItemType::WORK))->getCode()
        );

        $this->expectException(InvalidArgumentException::class);
        new ReceiptItemType(0);
    }

    /**
     * @covers Innokassa\MDK\Entities\Atoms\ReceiptItemType::all
     */
    public function testAll()
    {
        $a = ReceiptItemType::all();
        $this->assertIsArray($a);
        $this->assertContainsOnlyInstancesOf(ReceiptItemType::class, $a);
        $this->assertCount(19, $a);
    }
};
