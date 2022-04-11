<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Services\ManualBase;
use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\ConverterAbstract;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Collections\ReceiptCollection;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Collections\ReceiptItemCollection;
use Innokassa\MDK\Exceptions\Services\ManualException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Services\ManualBase
 * @uses Innokassa\MDK\Entities\UUID
 * @uses Innokassa\MDK\Entities\ReceiptItem
 * @uses Innokassa\MDK\Entities\Receipt
 * @uses Innokassa\MDK\Entities\Primitives\Notify
 * @uses Innokassa\MDK\Entities\Primitives\Amount
 * @uses Innokassa\MDK\Collections\BaseCollection
 * @uses Innokassa\MDK\Collections\ReceiptItemCollection
 * @uses Innokassa\MDK\Collections\ReceiptCollection
 * @uses Innokassa\MDK\Entities\AtomAbstract
 * @uses Innokassa\MDK\Entities\Atoms\PaymentMethod
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptItemType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptStatus
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptSubType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptType
 * @uses Innokassa\MDK\Entities\Atoms\Taxation
 * @uses Innokassa\MDK\Entities\Atoms\Vat
 * @uses Innokassa\MDK\Storage\ReceiptFilter
 * @uses Innokassa\MDK\Services\FiscalizationBaseAbstract
 * @uses Innokassa\MDK\Exceptions\TransferException
 */
class ManualBaseFakeTest extends TestCase
{
    private $storage;
    private $transfer;
    private $settings;

    protected function setUp(): void
    {
        $this->transfer = $this->createMock(TransferInterface::class);
        $this->storage = $this->createMock(ReceiptStorageInterface::class);

        $this->settings = $this->createMock(SettingsInterface::class);
        $this->settings->method('getActorId')
            ->willReturn(TEST_ACTOR_ID);
        $this->settings->method('getActorToken')
            ->willReturn(TEST_ACTOR_TOKEN);
        $this->settings->method('getCashbox')
            ->willReturn(TEST_CASHBOX_WITHOUT_AGENT);
        $this->settings->method('getTaxation')
            ->willReturn(Taxation::ORN);
        $this->settings->method('getLocation')
            ->willReturn('https://example.com/');
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Services\ManualBase::__construct
     * @covers Innokassa\MDK\Services\ManualBase::fiscalize
     * @covers Innokassa\MDK\Services\ManualBase::supplementReceipt
     */
    public function testFiscalizeSuccess()
    {
        $this->transfer
            ->method('sendReceipt')
            ->will($this->returnArgument(0));

        $manual = new ManualBase($this->storage, $this->transfer, $this->settings);

        $items = new ReceiptItemCollection();
        $items[] = (new ReceiptItem())
            ->setPrice(100.0)
            ->setQuantity(2)
            ->setName('name');

        $notify = new Notify('+79998887766');

        $receipt = $manual->fiscalize('0', $items, $notify);
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertSame(ReceiptType::COMING, $receipt->getType());
        $this->assertSame(ReceiptSubType::HAND, $receipt->getSubType());
        $this->assertSame($items->getAmount(), $receipt->getAmount()->get(Amount::CASHLESS));

        $amount = new Amount(Amount::CASH, 200.0);

        $receipt = $manual->fiscalize('0', $items, $notify, $amount);
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertSame(ReceiptType::COMING, $receipt->getType());
        $this->assertSame(ReceiptSubType::HAND, $receipt->getSubType());
        $this->assertSame(null, $receipt->getAmount()->get(Amount::CASHLESS));
        $this->assertSame($items->getAmount(), $receipt->getAmount()->get(Amount::CASH));
    }

    /**
     * @covers Innokassa\MDK\Services\ManualBase::__construct
     * @covers Innokassa\MDK\Services\ManualBase::fiscalize
     * @covers Innokassa\MDK\Services\ManualBase::supplementReceipt
     */
    public function testFiscalizeFailReceipt()
    {
        $this->transfer
            ->method('sendReceipt')
            ->will($this->throwException(new TransferException('', 400)));

        $manual = new ManualBase($this->storage, $this->transfer, $this->settings);

        $items = new ReceiptItemCollection();
        $items[] = (new ReceiptItem())
            ->setPrice(100.0)
            ->setQuantity(2)
            ->setName('name');

        $notify = new Notify('+79998887766');

        $this->expectException(ManualException::class);
        $this->expectExceptionCode(400);
        $receipt = $manual->fiscalize('0', $items, $notify);
    }

    //**********************************************************************

    /**
     * @covers Innokassa\MDK\Services\ManualBase::__construct
     * @covers Innokassa\MDK\Services\ManualBase::refund
     * @covers Innokassa\MDK\Services\ManualBase::supplementReceipt
     */
    public function testRefundSuccess()
    {
        $this->transfer
            ->method('sendReceipt')
            ->will($this->returnArgument(0));

        $items = new ReceiptItemCollection();
        $items[] = (new ReceiptItem())
            ->setPrice(100.0)
            ->setQuantity(2)
            ->setName('name');

        $receipts = new ReceiptCollection();
        $receipts[] = (new Receipt())
            ->setItems($items)
            ->setAmount(new Amount(Amount::CASHLESS, 200));

        $this->storage
            ->method('getCollection')
            ->will($this->onConsecutiveCalls($receipts, new ReceiptCollection()));

        $manual = new ManualBase($this->storage, $this->transfer, $this->settings);

        $notify = new Notify('+79998887766');

        $receipt = $manual->refund('0', $items, $notify);
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertSame(ReceiptType::REFUND_COMING, $receipt->getType());
        $this->assertSame(ReceiptSubType::HAND, $receipt->getSubType());
        $this->assertSame($items->getAmount(), $receipt->getAmount()->get(Amount::CASHLESS));
    }

    /**
     * @covers Innokassa\MDK\Services\ManualBase::__construct
     * @covers Innokassa\MDK\Services\ManualBase::refund
     * @covers Innokassa\MDK\Services\ManualBase::supplementReceipt
     */
    public function testRefundErrorGreater()
    {
        $this->transfer
            ->method('sendReceipt')
            ->will($this->returnArgument(0));

        $items = new ReceiptItemCollection();
        $items[] = (new ReceiptItem())
            ->setPrice(100.0)
            ->setQuantity(2)
            ->setName('name');

        $receipts = new ReceiptCollection();
        $receipts[] = (new Receipt())
            ->setItems($items)
            ->setAmount(new Amount(Amount::CASHLESS, 200));

        $this->storage
            ->method('getCollection')
            ->willReturn($receipts);

        $manual = new ManualBase($this->storage, $this->transfer, $this->settings);

        $notify = new Notify('+79998887766');

        $this->expectException(ManualException::class);
        $manual->refund('0', $items, $notify);
    }

    /**
     * @covers Innokassa\MDK\Services\ManualBase::__construct
     * @covers Innokassa\MDK\Services\ManualBase::refund
     * @covers Innokassa\MDK\Services\ManualBase::supplementReceipt
     */
    public function testRefundFailReceipt()
    {
        $this->transfer
            ->method('sendReceipt')
            ->will($this->throwException(new TransferException('', 400)));

        $items = new ReceiptItemCollection();
        $items[] = (new ReceiptItem())
            ->setPrice(100.0)
            ->setQuantity(2)
            ->setName('name');

        $receipts = new ReceiptCollection();
        $receipts[] = (new Receipt())
            ->setItems($items)
            ->setAmount(new Amount(Amount::CASHLESS, 200));

        $this->storage
            ->method('getCollection')
            ->will($this->onConsecutiveCalls($receipts, new ReceiptCollection()));

        $manual = new ManualBase($this->storage, $this->transfer, $this->settings);

        $notify = new Notify('+79998887766');

        $this->expectException(ManualException::class);
        $this->expectExceptionCode(400);
        $manual->refund('0', $items, $notify);
    }
}
