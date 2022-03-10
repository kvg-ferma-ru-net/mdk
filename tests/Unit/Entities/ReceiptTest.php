<?php

use PHPUnit\Framework\TestCase;

use Innokassa\MDK\Entities\UUID;
use Innokassa\MDK\Entities\Receipt;

use Innokassa\MDK\Entities\Atoms\Vat;

use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Entities\Atoms\PaymentMethod;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Entities\Primitives\Customer;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;

use Innokassa\MDK\Entities\Atoms\ReceiptItemType;
use Innokassa\MDK\Collections\ReceiptItemCollection;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * @uses Innokassa\MDK\Entities\Receipt
 * @uses Innokassa\MDK\Entities\ReceiptItem
 * 
 * @uses Innokassa\MDK\Entities\UUID
 * 
 * @uses Innokassa\MDK\Entities\AtomAbstract
 * @uses Innokassa\MDK\Entities\Atoms\Taxation
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptSubType
 * @uses Innokassa\MDK\Entities\Atoms\PaymentMethod
 * @uses Innokassa\MDK\Entities\Atoms\Vat
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptItemType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptStatus
 * 
 * @uses Innokassa\MDK\Entities\Primitives\Amount
 * @uses Innokassa\MDK\Entities\Primitives\Notify
 * @uses Innokassa\MDK\Entities\Primitives\Customer
 * 
 * @uses Innokassa\MDK\Collections\BaseCollection
 * @uses Innokassa\MDK\Collections\ReceiptItemCollection
 */
class ReceiptTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     */
    public function testConstructEmpty()
    {
        $receipt = new Receipt();
        $this->assertSame(0, $receipt->getId());
        $this->assertSame(ReceiptType::COMING, $receipt->getType());
        $this->assertSame(ReceiptSubType::HAND, $receipt->getSubType());
        $this->assertSame(ReceiptStatus::PREPARED, $receipt->getStatus()->getCode());

        $this->assertInstanceOf(ReceiptItemCollection::class, $receipt->getItems());
        $this->assertCount(0, $receipt->getItems());
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setType
     * @covers Innokassa\MDK\Entities\Receipt::getType
     */
    public function testSetGetType()
    {
        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setType(ReceiptType::COMING));
        $this->assertSame(ReceiptType::COMING, $receipt->getType());
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setSubType
     * @covers Innokassa\MDK\Entities\Receipt::getSubType
     */
    public function testSetGetSubType()
    {
        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setSubType(ReceiptSubType::HAND));
        $this->assertSame(ReceiptSubType::HAND, $receipt->getSubType());
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setTaxation
     * @covers Innokassa\MDK\Entities\Receipt::getTaxation
     */
    public function testSetGetTaxation()
    {
        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setTaxation(Taxation::ORN));
        $this->assertSame(Taxation::ORN, $receipt->getTaxation());
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setAmount
     * @covers Innokassa\MDK\Entities\Receipt::getAmount
     */
    public function testSetGetAmount()
    {
        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setAmount(new Amount(Amount::CASHLESS, 100)));
        $this->assertSame(100.0, $receipt->getAmount()->get(Amount::CASHLESS));
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setNotify
     * @covers Innokassa\MDK\Entities\Receipt::getNotify
     */
    public function testSetGetNotify()
    {
        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setNotify(new Notify('89998887766')));
        $this->assertSame('+79998887766', $receipt->getNotify()->getPhone());
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setCustomer
     * @covers Innokassa\MDK\Entities\Receipt::getCustomer
     */
    public function testSetGetCustomer()
    {
        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setCustomer(new Customer('Тест Тест Тест', '0000000000')));
        $this->assertSame('Тест Тест Тест', $receipt->getCustomer()->getName());
        $this->assertSame('0000000000', $receipt->getCustomer()->getTin());

        $receipt = new Receipt();
        $this->assertNull($receipt->getCustomer());
        $this->assertSame($receipt, $receipt->setCustomer(null));
        $this->assertNull($receipt->getCustomer());
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::addItem
     * @covers Innokassa\MDK\Entities\Receipt::getItems
     */
    public function testAddItem()
    {
        $receiptItem = [
            'type' => ReceiptItemType::PRODUCT,
            'name' => 'name',
            'price' => 100.0,
            'quantity' => 2.0,
            'amount' => 200.0,
            'payment_method' => PaymentMethod::PAYMENT_FULL,
            'vat' => Vat::CODE_WITHOUT
        ];

        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->addItem(new ReceiptItem($receiptItem)));
        $this->assertInstanceOf(ReceiptItemCollection::class, $receipt->getItems());
        $this->assertContainsOnlyInstancesOf(ReceiptItem::class, $receipt->getItems());
        $this->assertCount(1, $receipt->getItems());
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setItems
     * @covers Innokassa\MDK\Entities\Receipt::getItems
     */
    public function testSetItems()
    {
        $receiptItem = [
            'type' => ReceiptItemType::PRODUCT,
            'name' => 'name',
            'price' => 100.0,
            'quantity' => 2.0,
            'amount' => 200.0,
            'payment_method' => PaymentMethod::PAYMENT_FULL,
            'vat' => Vat::CODE_WITHOUT
        ];

        $items = new ReceiptItemCollection();
        $items[] = (new ReceiptItem())
            ->setPrice(100)
            ->setName('name');

        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setItems($items));
        $this->assertInstanceOf(ReceiptItemCollection::class, $receipt->getItems());
        $this->assertContainsOnlyInstancesOf(ReceiptItem::class, $receipt->getItems());
        $this->assertCount(1, $receipt->getItems());
        $this->assertSame($items, $receipt->getItems());
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setLocation
     * @covers Innokassa\MDK\Entities\Receipt::getLocation
     */
    public function testSetGetLocation()
    {
        $site = 'http://domain.zone';
        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setLocation($site));
        $this->assertSame($site, $receipt->getLocation());
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setLocation
     * @covers Innokassa\MDK\Entities\Receipt::getLocation
     */
    public function testSetGetLocationCyr()
    {
        $site = 'http://мойсайт.рф';
        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setLocation($site));
        $this->assertSame($site, $receipt->getLocation());
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setLocation
     */
    public function testSetGetLocationError()
    {
        $this->expectException(InvalidArgumentException::class);
        $site = 'domain';
        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setLocation($site));
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setStatus
     * @covers Innokassa\MDK\Entities\Receipt::getStatus
     */
    public function testSetGetStatus()
    {
        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setStatus(new ReceiptStatus(201)));
        $this->assertSame(ReceiptStatus::COMPLETED, $receipt->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setCashbox
     * @covers Innokassa\MDK\Entities\Receipt::getCashbox
     */
    public function testSetGetCashbox()
    {
        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setCashbox('123456'));
        $this->assertSame('123456', $receipt->getCashbox());
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setSiteId
     * @covers Innokassa\MDK\Entities\Receipt::getSiteId
     */
    public function testSetGetSiteId()
    {
        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setSiteId('s1'));
        $this->assertSame('s1', $receipt->getSiteId());
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setOrderId
     * @covers Innokassa\MDK\Entities\Receipt::getOrderId
     */
    public function testSetGetOrderId()
    {
        $receipt = new Receipt();
        $this->assertSame($receipt, $receipt->setOrderId('2'));
        $this->assertSame('2', $receipt->getOrderId());
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setUUID
     * @covers Innokassa\MDK\Entities\Receipt::getUUID
     */
    public function testSetGetUUID()
    {
        $receipt = new Receipt();
        $this->assertIsString($receipt->getUUID()->get());

        $uuid = new UUID();
        $this->assertSame($receipt, $receipt->setUUID($uuid));
        $this->assertSame($uuid, $receipt->getUUID());
    }

    /**
     * @covers Innokassa\MDK\Entities\Receipt::__construct
     * @covers Innokassa\MDK\Entities\Receipt::setId
     * @covers Innokassa\MDK\Entities\Receipt::getId
     */
    public function testSetGetId()
    {
        $receipt = new Receipt();
        $this->assertSame(0, $receipt->getId());

        $receipt->setId(10);
        $this->assertSame(10, $receipt->getId());
    }
};
