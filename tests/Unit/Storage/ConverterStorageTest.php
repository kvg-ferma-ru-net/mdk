<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Entities\Atoms\Unit;
use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Storage\ConverterStorage;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Entities\Primitives\Customer;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;
use Innokassa\MDK\Exceptions\ConverterException;
use Innokassa\MDK\Entities\ReceiptId\ReceiptIdFactoryMeta;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Storage\ConverterStorage
 * @uses Innokassa\MDK\Entities\Primitives\Notify
 * @uses Innokassa\MDK\Collections\BaseCollection
 * @uses Innokassa\MDK\Entities\AtomAbstract
 * @uses Innokassa\MDK\Entities\Atoms\PaymentMethod
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptItemType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptStatus
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptSubType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptType
 * @uses Innokassa\MDK\Entities\Atoms\Taxation
 * @uses Innokassa\MDK\Entities\Atoms\Vat
 * @uses Innokassa\MDK\Entities\Atoms\Unit
 * @uses Innokassa\MDK\Entities\ConverterAbstract
 * @uses Innokassa\MDK\Entities\Primitives\Amount
 * @uses Innokassa\MDK\Entities\Receipt
 * @uses Innokassa\MDK\Entities\ReceiptItem
 * @uses Innokassa\MDK\Entities\Primitives\Customer
 * @uses Innokassa\MDK\Entities\ReceiptId\ReceiptIdFactoryMeta
 */
class ConverterStorageTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Storage\ConverterStorage::__construct
     * @covers Innokassa\MDK\Storage\ConverterStorage::receiptToArray
     */
    public function testReceiptToArray()
    {
        $conv = new ConverterStorage(new ReceiptIdFactoryMeta());
        $receipt = new Receipt();
        $receipt
            ->setType(ReceiptType::COMING)
            ->setStatus(new ReceiptStatus(ReceiptStatus::COMPLETED))
            ->setSubType(ReceiptSubType::PRE)
            ->addItem(
                (new ReceiptItem())
                    ->setPrice(100.0)
                    ->setQuantity(2)
                    ->setName('name')
                    ->setItemId('123')
                    ->setUnit(Unit::DEFAULT)
            )
            ->setTaxation(Taxation::ORN)
            ->setAmount((new Amount())->setCashless(200.0))
            ->setNotify(new Notify('box@domain.zone'))
            ->setLocation('http://example.com/')
            ->setOrderId('456');

        $a = [
            'id' => 0,
            'receipt_id' => $receipt->getReceiptId(),
            'site_id' => '',
            'order_id' => '456',
            'status' => ReceiptStatus::COMPLETED,
            'subtype' => ReceiptSubType::PRE,
            'type' => ReceiptType::COMING,
            'items' => [[
                'item_id' => '123',
                'type' => 1,
                'name' => 'name',
                'price' => 100.0,
                'quantity' => 2.0,
                'amount' => 200.0,
                'payment_method' => 4,
                'vat' => 6,
                'unit' => Unit::DEFAULT
            ]],
            'taxation' => Taxation::ORN,
            'amount' => [
                'cashless' => 200.0,
            ],
            'notify' => [
                'email' => 'box@domain.zone'
            ],
            'customer' => null,
            'location' =>  'http://example.com/',
            'start_time' => date("Y-m-d H:i:s")
        ];

        $expect = $a;
        ksort($expect);
        $actual = $conv->receiptToArray($receipt);
        ksort($actual);

        $this->assertSame($expect, $actual);

        $expect['customer'] = [
            'name' => 'Test'
        ];
        $receipt->setCustomer(new Customer('Test'));
        $actual = $conv->receiptToArray($receipt);
        ksort($actual);

        $this->assertSame($expect, $actual);
    }

    /**
     * @covers Innokassa\MDK\Storage\ConverterStorage::receiptToArray
     */
    public function testReceiptToArrayFailItems()
    {
        $conv = new ConverterStorage(new ReceiptIdFactoryMeta());
        $receipt = new Receipt();
        $receipt
            ->setType(ReceiptType::COMING)
            ->setTaxation(Taxation::ORN)
            ->setAmount((new Amount())->setCashless(200.0))
            ->setNotify(new Notify('box@domain.zone'))
            ->setCustomer(new Customer('Test'))
            ->setLocation('http://example.com/');

        $this->expectException(ConverterException::class);
        $conv->receiptToArray($receipt);
    }

    /**
     * @covers Innokassa\MDK\Storage\ConverterStorage::receiptToArray
     */
    public function testReceiptToArrayFailTaxation()
    {
        $conv = new ConverterStorage(new ReceiptIdFactoryMeta());
        $receipt = new Receipt();
        $receipt
            ->setType(ReceiptType::COMING)
            ->addItem(
                (new ReceiptItem())
                    ->setPrice(100.0)
                    ->setQuantity(2)
                    ->setName('name')
            )
            ->setAmount((new Amount())->setCashless(200.0))
            ->setNotify(new Notify('box@domain.zone'))
            ->setCustomer(new Customer('Test'))
            ->setLocation('http://example.com/');

        $this->expectException(ConverterException::class);
        $conv->receiptToArray($receipt);
    }

    /**
     * @covers Innokassa\MDK\Storage\ConverterStorage::receiptToArray
     */
    public function testReceiptToArrayFailAmount()
    {
        $conv = new ConverterStorage(new ReceiptIdFactoryMeta());
        $receipt = new Receipt();
        $receipt
            ->setType(ReceiptType::COMING)
            ->addItem(
                (new ReceiptItem())
                    ->setPrice(100.0)
                    ->setQuantity(2)
                    ->setName('name')
            )
            ->setTaxation(Taxation::ORN)
            ->setNotify(new Notify('box@domain.zone'))
            ->setCustomer(new Customer('Test'))
            ->setLocation('http://example.com/');

        $this->expectException(ConverterException::class);
        $conv->receiptToArray($receipt);
    }

    /**
     * @covers Innokassa\MDK\Storage\ConverterStorage::receiptToArray
     */
    public function testReceiptToArrayFailNotify()
    {
        $conv = new ConverterStorage(new ReceiptIdFactoryMeta());
        $receipt = new Receipt();
        $receipt
            ->setType(ReceiptType::COMING)
            ->addItem(
                (new ReceiptItem())
                    ->setPrice(100.0)
                    ->setQuantity(2)
                    ->setName('name')
            )
            ->setTaxation(Taxation::ORN)
            ->setAmount((new Amount())->setCashless(200.0))
            ->setCustomer(new Customer('Test'))
            ->setLocation('http://example.com/');

        $this->expectException(ConverterException::class);
        $conv->receiptToArray($receipt);
    }

    /**
     * @covers Innokassa\MDK\Storage\ConverterStorage::receiptToArray
     */
    public function testReceiptToArrayFailLocation()
    {
        $conv = new ConverterStorage(new ReceiptIdFactoryMeta());
        $receipt = new Receipt();
        $receipt
            ->setType(ReceiptType::COMING)
            ->addItem(
                (new ReceiptItem())
                    ->setPrice(100.0)
                    ->setQuantity(2)
                    ->setName('name')
            )
            ->setTaxation(Taxation::ORN)
            ->setAmount((new Amount())->setCashless(200.0))
            ->setNotify(new Notify('box@domain.zone'))
            ->setCustomer(new Customer('Test'));

        $this->expectException(ConverterException::class);
        $conv->receiptToArray($receipt);
    }

    //**********************************************************************

    /**
     * @covers Innokassa\MDK\Storage\ConverterStorage::receiptFromArray
     */
    public function testReceiptFromArray()
    {
        $receiptIdFactory = new ReceiptIdFactoryMeta();
        $conv = new ConverterStorage($receiptIdFactory);
        $receiptId = '20010310:171618-woo-mdk-1234567891234567-7c0b89b58d4f4af9';
        $a = [
            'id' => 0,
            'receipt_id' => $receiptId,
            'site_id' => '0',
            'order_id' => '',
            'status' => ReceiptStatus::COMPLETED,
            'subtype' => ReceiptSubType::PRE,
            'type' => ReceiptType::COMING,
            'items' => [[
                'type' => 1,
                'name' => 'name',
                'price' => 100.0,
                'quantity' => 2.0,
                'amount' => 200.0,
                'payment_method' => 4,
                'vat' => 6,
                'unit' => Unit::DEFAULT
            ]],
            'taxation' => Taxation::ORN,
            'amount' => [
                'cashless' => 200.0
            ],
            'notify' => [
                'email' => 'box@domain.zone'
            ],
            'customer' => null,
            'location' =>  'http://example.com/',
            'start_time' => date("Y-m-d H:i:s")
        ];

        $receipt = $conv->receiptFromArray($a);
        $this->assertInstanceOf(Receipt::class, $receipt);

        $this->assertSame(0, $receipt->getId());
        $this->assertSame($receiptId, $receipt->getReceiptId());
        $this->assertSame('0', $receipt->getSiteId());
        $this->assertSame('', $receipt->getOrderId());
        $this->assertSame(ReceiptType::COMING, $receipt->getType());
        $this->assertSame(ReceiptStatus::COMPLETED, $receipt->getStatus()->getCode());
        $this->assertSame(ReceiptSubType::PRE, $receipt->getSubType());

        $this->assertSame(Taxation::ORN, $receipt->getTaxation());
        $this->assertSame(null, $receipt->getCustomer());
        $this->assertSame('box@domain.zone', $receipt->getNotify()->getEmail());
        $this->assertSame(200.0, $receipt->getAmount()->getCashless());
        $this->assertSame('http://example.com/', $receipt->getLocation());
    }

    /**
     * @covers Innokassa\MDK\Storage\ConverterStorage::receiptFromArray
     */
    public function testReceiptFromArrayFailEmpty()
    {
        $this->expectException(ConverterException::class);
        $conv = new ConverterStorage(new ReceiptIdFactoryMeta());
        $conv->receiptFromArray([]);
    }

    /**
     * @covers Innokassa\MDK\Storage\ConverterStorage::receiptFromArray
     */
    public function testReceiptFromArrayFailPartial()
    {
        $conv = new ConverterStorage(new ReceiptIdFactoryMeta());

        $a = [
            'id' => 0,
            'cashbox' => '',
            'site_id' => '0',
            'order_id' => '',
            'status' => ReceiptStatus::COMPLETED,
            'subtype' => ReceiptSubType::PRE,
            'type' => ReceiptType::COMING,
        ];

        $this->expectException(ConverterException::class);
        $conv->receiptFromArray($a);
    }

    /**
     * @covers Innokassa\MDK\Storage\ConverterStorage::receiptFromArray
     */
    public function testReceiptFromArrayFailInvalid()
    {
        $conv = new ConverterStorage(new ReceiptIdFactoryMeta());
        $a = [
            'id' => 0,
            'receipt_id' => '',
            'cashbox' => '',
            'site_id' => '0',
            'order_id' => '',
            'status' => ReceiptStatus::COMPLETED,
            'accepted' => 1,
            'available' => 1,
            'subtype' => ReceiptSubType::PRE,
            'type' => ReceiptType::COMING,
            'items' => [[
                'type' => 1,
                'name' => 'name',
                'price' => 100.0,
                'quantity' => 2.0,
                'amount' => 200.0,
                'payment_method' => 4,
                'vat' => 6
            ]],
            'taxation' => Taxation::ORN,
            'amount' => [
                'cashless' => 200.0
            ],
            'notify' => [
                'email' => 'box@domain.zone'
            ],
            'customer' => null,
            'location' =>  'http://example.com/',
            'start_time' => date('Y-m-d H:i:s')
        ];

        $this->expectException(ConverterException::class);
        $conv->receiptFromArray($a);
    }
}
