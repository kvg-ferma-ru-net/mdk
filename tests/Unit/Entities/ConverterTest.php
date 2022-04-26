<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Atoms\Vat;
use Innokassa\MDK\Entities\Atoms\Unit;
use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Entities\ConverterAbstract;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Entities\Atoms\PaymentMethod;
use Innokassa\MDK\Entities\Primitives\Customer;
use Innokassa\MDK\Exceptions\ConverterException;
use Innokassa\MDK\Entities\Atoms\ReceiptItemType;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Entities\ConverterAbstract
 * @uses Innokassa\MDK\Entities\ReceiptItem
 * @uses Innokassa\MDK\Collections\BaseCollection
 * @uses Innokassa\MDK\Entities\AtomAbstract
 * @uses Innokassa\MDK\Entities\Atoms\PaymentMethod
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptItemType
 * @uses Innokassa\MDK\Entities\Atoms\Vat
 * @uses Innokassa\MDK\Entities\Atoms\Unit
 * @uses Innokassa\MDK\Entities\Primitives\Amount
 * @uses Innokassa\MDK\Entities\Primitives\Customer
 * @uses Innokassa\MDK\Entities\Primitives\Notify
 */
class ConverterTest extends TestCase
{
    private $converter = null;

    protected function setUp(): void
    {
        $this->converter = $this->getMockForAbstractClass(ConverterAbstract::class);
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::amountToArray
     */
    public function testAmountToArray()
    {
        $amount = new Amount();
        $amount
            ->set(Amount::CASHLESS, 100)
            ->set(Amount::CASH, 200)
            ->set(Amount::PREPAYMENT, 300)
            ->set(Amount::POSTPAYMENT, 400)
            ->set(Amount::BARTER, 500);

        $this->assertEquals(
            [
                'cashless' => 100.0,
                'cash' => 200.0,
                'prepayment' => 300.0,
                'postpayment' => 400.0,
                'barter' => 500.0,
            ],
            $this->converter->amountToArray($amount)
        );

        $this->expectException(ConverterException::class);
        $this->converter->amountToArray(new Amount());
    }

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::amountFromArray
     */
    public function testAmountFromArray()
    {
        $amount = $this->converter->amountFromArray([
            'cashless' => 100.0,
            'cash' => 200.0,
            'prepayment' => 300.0,
            'postpayment' => 400.0,
            'barter' => 500.0,
        ]);
        $this->assertSame(100.0, $amount->get(Amount::CASHLESS));
        $this->assertSame(200.0, $amount->get(Amount::CASH));
        $this->assertSame(300.0, $amount->get(Amount::PREPAYMENT));
        $this->assertSame(400.0, $amount->get(Amount::POSTPAYMENT));
        $this->assertSame(500.0, $amount->get(Amount::BARTER));

        $this->expectException(ConverterException::class);
        $this->converter->amountFromArray([]);
    }

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::amountFromArray
     */
    public function testAmountFromArrayError()
    {
        $this->expectException(ConverterException::class);
        $this->converter->amountFromArray([
            'cashless' => -100.0,
        ]);
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::customerToArray
     */
    public function testCustomerToArray()
    {
        $customer = new Customer('Тест Тест Тест', '0000000000');
        $this->assertSame(
            [
                'name' => 'Тест Тест Тест',
                'tin' => '0000000000'
            ],
            $this->converter->customerToArray($customer)
        );

        $this->expectException(ConverterException::class);
        $this->converter->customerToArray(new Customer());
    }

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::customerFromArray
     */
    public function testCustomerFromArray()
    {
        $customer = $this->converter->customerFromArray([
            'name' => 'Тест Тест Тест',
            'tin' => '0000000000'
        ]);
        $this->assertSame('Тест Тест Тест', $customer->getName());
        $this->assertSame('0000000000', $customer->getTin());

        $this->expectException(ConverterException::class);
        $this->converter->customerFromArray([]);
    }

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::customerFromArray
     */
    public function testCustomerFromArrayError()
    {
        $this->expectException(ConverterException::class);
        $this->converter->customerFromArray([
            'name' => ''
        ]);
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::notifyToArray
     */
    public function testNotifyToArray()
    {
        $notify = new Notify();
        $notify->setEmail('box@domain.zone');
        $notify->setPhone('+79998887766');
        $this->assertEquals(
            [
                'phone' => '+79998887766',
                'email' => 'box@domain.zone'
            ],
            $this->converter->notifyToArray($notify)
        );

        $this->expectException(ConverterException::class);
        $this->converter->notifyToArray(new Notify());
    }

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::notifyFromArray
     */
    public function testNotifyFromArray()
    {
        $notify = $this->converter->notifyFromArray([
            'email' => 'box@domain.zone',
            'phone' => '+79998887766',
        ]);
        $this->assertSame('box@domain.zone', $notify->getEmail());
        $this->assertSame('+79998887766', $notify->getPhone());

        $this->expectException(ConverterException::class);
        $this->converter->notifyFromArray([]);
    }

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::notifyFromArray
     */
    public function testNotifyFromArrayError()
    {
        $this->expectException(ConverterException::class);
        $this->converter->notifyFromArray([
            'email' => 'box @domain.zone',
            'phone' => '+7999888776600',
        ]);
    }

    //######################################################################
    //######################################################################
    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::itemFromArray
     * @covers Innokassa\MDK\Entities\ConverterAbstract::itemToArray
     */
    public function testItemToFromArray()
    {
        $aOut = [
            'type' => ReceiptItemType::PRODUCT,
            'name' => 'name',
            'price' => 100.0,
            'quantity' => 2.0,
            'amount' => 200.0,
            'payment_method' => PaymentMethod::PREPAYMENT_FULL,
            'vat' => Vat::CODE_WITHOUT,
            'item_id' => '123',
            'unit' => Unit::DEFAULT
        ];
        $receiptItem = $this->converter->itemFromArray($aOut);
        $this->assertEquals($aOut, $this->converter->itemToArray($receiptItem));
    }

    //**********************************************************************

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::itemToArray
     */
    public function testItemToArrayEmpty()
    {
        $this->expectException(ConverterException::class);
        $this->converter->itemToArray(new ReceiptItem());
    }

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::itemToArray
     */
    public function testItemToArrayPartial1()
    {
        $this->expectException(ConverterException::class);
        $this->converter->itemToArray(
            (new ReceiptItem())->setPrice(10)
        );
    }

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::itemToArray
     */
    public function testItemToArrayPartial2()
    {
        $this->expectException(ConverterException::class);
        $this->converter->itemToArray(
            (new ReceiptItem())->setName('name')
        );
    }

    //**********************************************************************

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::itemFromArray
     */
    public function testItemFromArrayInvalid()
    {
        $this->expectException(ConverterException::class);
        $this->converter->itemFromArray([]);
    }

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::itemFromArray
     */
    public function testItemFromArrayPartial()
    {
        $this->expectException(ConverterException::class);
        $this->converter->itemFromArray(['name' => 'name']);
    }

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::itemFromArray
     */
    public function testItemFromArrayError()
    {
        $aOut = [
            'type' => ReceiptItemType::PRODUCT,
            'name' => 'name',
            'price' => -100.0,
            'quantity' => 2.0,
            'amount' => 200.0,
            'payment_method' => PaymentMethod::PREPAYMENT_FULL,
            'vat' => Vat::CODE_WITHOUT
        ];
        $this->expectException(ConverterException::class);
        $this->converter->itemFromArray($aOut);
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\ConverterAbstract::itemsFromArray
     * @covers Innokassa\MDK\Entities\ConverterAbstract::itemsToArray
     */
    public function testItemsToArray()
    {
        $item = [
            'type' => ReceiptItemType::PRODUCT,
            'name' => 'name',
            'price' => 100.0,
            'quantity' => 2.0,
            'amount' => 200.0,
            'payment_method' => PaymentMethod::PREPAYMENT_FULL,
            'vat' => Vat::CODE_WITHOUT,
            'item_id' => '123',
            'unit' => Unit::DEFAULT
        ];
        $items = [];
        $items[] = $item;
        $items[] = $item;
        $items[] = $item;

        $receiptItems = $this->converter->itemsFromArray($items);
        $this->assertEquals($items, $this->converter->itemsToArray($receiptItems));
    }
}
