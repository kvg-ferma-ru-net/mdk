<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Atoms\Vat;
use Innokassa\MDK\Entities\Atoms\Unit;
use Innokassa\MDK\Entities\Atoms\PaymentMethod;
use Innokassa\MDK\Entities\Atoms\ReceiptItemType;
use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Entities\ReceiptItem
 * @uses Innokassa\MDK\Entities\Atoms\Vat
 * @uses Innokassa\MDK\Entities\Atoms\Unit
 * @uses Innokassa\MDK\Entities\Atoms\PaymentMethod
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptItemType
 * @uses Innokassa\MDK\Entities\AtomAbstract
 */
class ReceiptItemTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Entities\ReceiptItem::__construct
     */
    public function testConstruct()
    {
        $receiptItem = new ReceiptItem();
        $this->assertSame(1.0, $receiptItem->getQuantity());
        $this->assertSame(Vat::CODE_WITHOUT, $receiptItem->getVat()->getCode());
        $this->assertSame(ReceiptItemType::PRODUCT, $receiptItem->getType());
        $this->assertSame(PaymentMethod::PAYMENT_FULL, $receiptItem->getPaymentMethod());
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\ReceiptItem::setType
     * @covers Innokassa\MDK\Entities\ReceiptItem::getType
     */
    public function testSetGetType()
    {
        $receiptItem = new ReceiptItem();

        $this->assertSame($receiptItem, $receiptItem->setType(ReceiptItemType::PRODUCT));
        $this->assertSame(ReceiptItemType::PRODUCT, $receiptItem->getType());

        $this->expectException(InvalidArgumentException::class);
        $receiptItem->setType(0);
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\ReceiptItem::setName
     * @covers Innokassa\MDK\Entities\ReceiptItem::getName
     */
    public function testSetGetName()
    {
        $receiptItem = new ReceiptItem();

        $this->assertSame($receiptItem, $receiptItem->setName('name'));
        $this->assertSame('name', $receiptItem->getName());
    }

    /**
     * @covers Innokassa\MDK\Entities\ReceiptItem::setName
     * @covers Innokassa\MDK\Entities\ReceiptItem::getName
     */
    public function testSetGetNameFail0()
    {
        $receiptItem = new ReceiptItem();
        $this->expectException(InvalidArgumentException::class);
        $receiptItem->setName('');
    }

    /**
     * @covers Innokassa\MDK\Entities\ReceiptItem::setName
     * @covers Innokassa\MDK\Entities\ReceiptItem::getName
     */
    public function testSetGetNameFail128()
    {
        $receiptItem = new ReceiptItem();
        $this->expectException(InvalidArgumentException::class);
        $receiptItem->setName('name name name name name name name name name name name name name name name name name name name name name name name name name name name name name name');
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\ReceiptItem::setPrice
     * @covers Innokassa\MDK\Entities\ReceiptItem::getPrice
     */
    public function testSetGetPrice()
    {
        $receiptItem = new ReceiptItem();

        $this->assertSame($receiptItem, $receiptItem->setPrice(100));
        $this->assertSame(100.0, $receiptItem->getPrice());

        $this->expectException(InvalidArgumentException::class);
        $receiptItem->setPrice(0);
    }

    /**
     * @covers Innokassa\MDK\Entities\ReceiptItem::setQuantity
     * @covers Innokassa\MDK\Entities\ReceiptItem::getQuantity
     */
    public function testSetGetQuantity()
    {
        $receiptItem = new ReceiptItem();

        $this->assertSame(1.0, $receiptItem->getQuantity());
        $this->assertSame($receiptItem, $receiptItem->setQuantity(2));
        $this->assertSame(2.0, $receiptItem->getQuantity());

        $this->expectException(InvalidArgumentException::class);
        $receiptItem->setQuantity(0);
    }

    /**
     * @covers Innokassa\MDK\Entities\ReceiptItem::setAmount
     * @covers Innokassa\MDK\Entities\ReceiptItem::getAmount
     */
    public function testSetGetAmount()
    {
        $receiptItem = new ReceiptItem();
        $receiptItem->setPrice(100.0)
                    ->setQuantity(3);
        $this->assertSame(300.0, $receiptItem->getAmount());
        $this->assertSame($receiptItem, $receiptItem->setAmount(300.0));
        $this->assertSame(300.0, $receiptItem->getAmount());

        $this->expectException(InvalidArgumentException::class);
        $receiptItem->setAmount(100);
    }

    /**
     * @covers Innokassa\MDK\Entities\ReceiptItem::setPaymentMethod
     * @covers Innokassa\MDK\Entities\ReceiptItem::getPaymentMethod
     */
    public function testSetGetPaymentMethod()
    {
        $receiptItem = new ReceiptItem();

        $this->assertSame($receiptItem, $receiptItem->setPaymentMethod(PaymentMethod::PAYMENT_FULL));
        $this->assertSame(PaymentMethod::PAYMENT_FULL, $receiptItem->getPaymentMethod());

        $this->expectException(InvalidArgumentException::class);
        $receiptItem->setPaymentMethod(0);
    }

    /**
     * @covers Innokassa\MDK\Entities\ReceiptItem::setVat
     * @covers Innokassa\MDK\Entities\ReceiptItem::getVat
     */
    public function testSetGetVat()
    {
        $receiptItem = new ReceiptItem();

        $this->assertSame($receiptItem, $receiptItem->setVat(new Vat('20')));
        $this->assertSame('20', $receiptItem->getVat()->getName());
        $this->assertSame(Vat::CODE_20, $receiptItem->getVat()->getCode());

        $this->expectException(InvalidArgumentException::class);
        $receiptItem->setType(0);
    }
    
    /**
     * @covers Innokassa\MDK\Entities\ReceiptItem::setUnit
     * @covers Innokassa\MDK\Entities\ReceiptItem::getUnit
     */
    public function testSetGetUnit()
    {
        $receiptItem = new ReceiptItem();

        $this->assertSame(Unit::DEFAULT, $receiptItem->getUnit());
        $this->assertSame($receiptItem, $receiptItem->setUnit(Unit::DAY));
        $this->assertSame(Unit::DAY, $receiptItem->getUnit());

        $this->expectException(InvalidArgumentException::class);
        $receiptItem->setUnit(-1);
    }
}
