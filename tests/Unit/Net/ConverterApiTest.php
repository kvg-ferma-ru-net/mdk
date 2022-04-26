<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Net\ConverterApi;
use Innokassa\MDK\Entities\Atoms\Unit;
use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Entities\Primitives\Customer;
use Innokassa\MDK\Exceptions\ConverterException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
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
 * @uses Innokassa\MDK\Entities\UUID
 * @uses Innokassa\MDK\Entities\Primitives\Customer
 */
class ConverterApiTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Net\ConverterApi::receiptToArray
     * @covers Innokassa\MDK\Net\ConverterApi::notifyToArray
     */
    public function testReceiptToArray()
    {
        $conv = new ConverterApi();
        $receipt = new Receipt();
        $receipt
            ->setOrderId('456')
            ->setType(ReceiptType::COMING)
            ->addItem(
                (new ReceiptItem())
                    ->setPrice(100.0)
                    ->setQuantity(2)
                    ->setName('name')
                    ->setItemId('123')
                    ->setUnit(Unit::DEFAULT)
            )
            ->setTaxation(Taxation::ORN)
            ->setAmount(new Amount(Amount::CASHLESS, 200.0))
            ->setNotify(new Notify('box@domain.zone'))
            ->setCustomer(new Customer('Test'))
            ->setLocation('http://example.com/');

        $this->assertEquals(
            [
                'order_id' => '456',
                'type' => ReceiptType::COMING,
                'items' => [[
                    'type' => 1,
                    'name' => 'name',
                    'price' => 100.0,
                    'quantity' => 2.0,
                    'amount' => 200.0,
                    'payment_method' => 4,
                    'vat' => 6,
                    'item_id' => '123',
                    'unit' => Unit::DEFAULT
                ]],
                'taxation' => Taxation::ORN,
                'amount' => [
                    'cashless' => 200.0
                ],
                'notify' => [
                    'emails' => ['box@domain.zone']
                ],
                'customer' => [
                    'name' => 'Test'
                ],
                'loc' => [
                    'billing_place' => 'http://example.com/'
                ]
            ],
            $conv->receiptToArray($receipt)
        );
    }

    /**
     * @covers Innokassa\MDK\Net\ConverterApi::receiptToArray
     */
    public function testReceiptToArrayFailItems()
    {
        $conv = new ConverterApi();
        $receipt = new Receipt();
        $receipt
            ->setType(ReceiptType::COMING)
            ->setTaxation(Taxation::ORN)
            ->setAmount(new Amount(Amount::CASHLESS, 200.0))
            ->setNotify(new Notify('box@domain.zone'))
            ->setCustomer(new Customer('Test'))
            ->setLocation('http://example.com/');

        $this->expectException(ConverterException::class);
        $conv->receiptToArray($receipt);
    }

    /**
     * @covers Innokassa\MDK\Net\ConverterApi::receiptToArray
     */
    public function testReceiptToArrayFailTaxation()
    {
        $conv = new ConverterApi();
        $receipt = new Receipt();
        $receipt
            ->setType(ReceiptType::COMING)
            ->addItem(
                (new ReceiptItem())
                    ->setPrice(100.0)
                    ->setQuantity(2)
                    ->setName('name')
            )
            ->setAmount(new Amount(Amount::CASHLESS, 200.0))
            ->setNotify(new Notify('box@domain.zone'))
            ->setCustomer(new Customer('Test'))
            ->setLocation('http://example.com/');

        $this->expectException(ConverterException::class);
        $conv->receiptToArray($receipt);
    }

    /**
     * @covers Innokassa\MDK\Net\ConverterApi::receiptToArray
     */
    public function testReceiptToArrayFailAmount()
    {
        $conv = new ConverterApi();
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
     * @covers Innokassa\MDK\Net\ConverterApi::receiptToArray
     */
    public function testReceiptToArrayFailNotify()
    {
        $conv = new ConverterApi();
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
            ->setAmount(new Amount(Amount::CASHLESS, 200.0))
            ->setCustomer(new Customer('Test'))
            ->setLocation('http://example.com/');

        $this->expectException(ConverterException::class);
        $conv->receiptToArray($receipt);
    }

    /**
     * @covers Innokassa\MDK\Net\ConverterApi::receiptToArray
     */
    public function testReceiptToArrayFailLocation()
    {
        $conv = new ConverterApi();
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
            ->setAmount(new Amount(Amount::CASHLESS, 200.0))
            ->setNotify(new Notify('box@domain.zone'))
            ->setCustomer(new Customer('Test'));

        $this->expectException(ConverterException::class);
        $conv->receiptToArray($receipt);
    }

    //**********************************************************************

    /**
     * @covers Innokassa\MDK\Net\ConverterApi::receiptFromArray
     */
    public function testReceiptFromArray()
    {
        $this->expectException(ConverterException::class);
        $conv = new ConverterApi();
        $conv->receiptFromArray([]);
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Net\ConverterApi::notifyToArray
     */
    public function testNotifyToArrayEmail()
    {
        $conv = new ConverterApi();
        $notify = new Notify();
        $notify
            ->setEmail('box@domain.zone')
            ->setPhone('+79998887766');

        $this->assertEquals(
            [
                'emails' => ['box@domain.zone'],
                'phone' => '+79998887766',
            ],
            $conv->notifyToArray($notify)
        );
    }

    /**
     * @covers Innokassa\MDK\Net\ConverterApi::notifyToArray
     */
    public function testNotifyToArrayPhone()
    {
        $conv = new ConverterApi();
        $notify = new Notify();
        $notify->setPhone('+79998887766');

        $this->assertEquals(
            [
                'phone' => '+79998887766',
            ],
            $conv->notifyToArray($notify)
        );
    }

    /**
     * @covers Innokassa\MDK\Net\ConverterApi::notifyToArray
     */
    public function testNotifyToArrayFail()
    {
        $this->expectException(ConverterException::class);
        $conv = new ConverterApi();
        $conv->notifyToArray(new Notify());
    }

    //**********************************************************************

    /**
     * @covers Innokassa\MDK\Net\ConverterApi::notifyFromArray
     */
    public function testNotifyFromArray()
    {
        $this->expectException(ConverterException::class);
        $conv = new ConverterApi();
        $conv->notifyFromArray([]);
    }
}
