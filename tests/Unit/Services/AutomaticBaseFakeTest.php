<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Services\AutomaticBase;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\ConverterAbstract;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Entities\Primitives\Customer;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Collections\ReceiptCollection;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Entities\ReceiptAdapterInterface;
use Innokassa\MDK\Collections\ReceiptItemCollection;
use Innokassa\MDK\Exceptions\Services\ManualException;
use Innokassa\MDK\Exceptions\Services\AutomaticException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Services\AutomaticBase
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
 * @uses Innokassa\MDK\Entities\Atoms\Unit
 * @uses Innokassa\MDK\Storage\ReceiptFilter
 * @uses Innokassa\MDK\Entities\Primitives\Customer
 * @uses Innokassa\MDK\Services\FiscalizationBaseAbstract
 * @uses Innokassa\MDK\Exceptions\TransferException
 */
class AutomaticBaseFakeTest extends TestCase
{
    private $storage;
    private $adapter;
    private $transfer;
    private $settings;

    protected function setUp(): void
    {
        $items = new ReceiptItemCollection();
        $items[] = (new ReceiptItem())
            ->setPrice(100.0)
            ->setQuantity(2)
            ->setName('name');

        $this->adapter = $this->createMock(ReceiptAdapterInterface::class);
        $this->adapter->method('getItems')
            ->willReturn($items);
        $this->adapter->method('getTotal')
            ->willReturn(200.0);
        $this->adapter->method('getCustomer')
            ->willReturn(new Customer('Test'));
        $this->adapter->method('getNotify')
            ->willReturn(new Notify('+79998887766'));

        $this->transfer = $this->createMock(TransferInterface::class);
        $this->transfer
            ->method('sendReceipt')
            ->will($this->returnArgument(0));

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
     * Тест чека предоплаты
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeSuccessPre()
    {
        $this->settings->method('getOnly2')
            ->willReturn(false);

        $this->storage
            ->method('getCollection')
            ->willReturn(new ReceiptCollection());

        $automatic = new AutomaticBase($this->settings, $this->storage, $this->transfer, $this->adapter);

        $receipt = $automatic->fiscalize('0');
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertSame(ReceiptType::COMING, $receipt->getType());
        $this->assertSame(ReceiptSubType::PRE, $receipt->getSubType());
        $this->assertSame(200.0, $receipt->getAmount()->get(Amount::CASHLESS));
        $this->assertSame('Test', $receipt->getCustomer()->getName());
        $this->assertSame('+79998887766', $receipt->getNotify()->getPhone());

        $receipt = $automatic->fiscalize('0', ReceiptSubType::PRE);
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertSame(ReceiptType::COMING, $receipt->getType());
        $this->assertSame(ReceiptSubType::PRE, $receipt->getSubType());
        $this->assertSame(200.0, $receipt->getAmount()->get(Amount::CASHLESS));
        $this->assertSame('Test', $receipt->getCustomer()->getName());
        $this->assertSame('+79998887766', $receipt->getNotify()->getPhone());
    }

    //**********************************************************************

    /**
     * Тест чека полного расчета, без чека предоплаты
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeSuccessFull1()
    {
        $this->settings->method('getOnly2')
            ->willReturn(false);

        $this->storage
            ->method('getCollection')
            ->willReturn(new ReceiptCollection());

        $automatic = new AutomaticBase($this->settings, $this->storage, $this->transfer, $this->adapter);

        $receipt = $automatic->fiscalize('0', ReceiptSubType::FULL);
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertSame(ReceiptSubType::FULL, $receipt->getSubType());
        $this->assertSame(200.0, $receipt->getAmount()->get(Amount::CASHLESS));
    }

    /**
     * Тест чека полного расчета с чеком предоплаты
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeSuccessFull2()
    {
        $this->settings->method('getOnly2')
            ->willReturn(false);

        $receipts = new ReceiptCollection();
        $receipt = new Receipt();
        $receipt->setType(ReceiptType::COMING);
        $receipt->setSubType(ReceiptSubType::PRE);
        $receipts[] = $receipt;

        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $this->storage
            ->method('getCollection')
            ->will($this->onConsecutiveCalls(new ReceiptCollection(), $receipts));

        $automatic = new AutomaticBase($this->settings, $this->storage, $this->transfer, $this->adapter);
        $receipt = $automatic->fiscalize('0');
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertSame(ReceiptSubType::FULL, $receipt->getSubType());
        $this->assertSame(200.0, $receipt->getAmount()->get(Amount::PREPAYMENT));
    }

    //**********************************************************************

    /**
     * Тест чека полного рачета при наличии настройки "пробивать только второй чек"
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeSuccessGetOnly()
    {
        $this->settings->method('getOnly2')
            ->willReturn(true);

        $this->storage
            ->method('getCollection')
            ->willReturn(new ReceiptCollection());

        $automatic = new AutomaticBase($this->settings, $this->storage, $this->transfer, $this->adapter);

        $receipt = $automatic->fiscalize('0');
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertSame(ReceiptSubType::FULL, $receipt->getSubType());
        $this->assertSame(200.0, $receipt->getAmount()->get(Amount::CASHLESS));
    }

    //**********************************************************************

    /**
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     * @covers Innokassa\MDK\Services\FiscalizationBaseAbstract::fiscalizeProc
     */
    public function testFiscalizeSuccessNotUniqUUID()
    {
        $this->settings->method('getOnly2')
            ->willReturn(false);

        $this->storage
            ->method('getCollection')
            ->willReturn(new ReceiptCollection());

        $this->transfer
            ->method('sendReceipt')
            ->will(
                $this->onConsecutiveCalls(
                    $this->throwException(new TransferException('', 409)),
                    $this->returnArgument(0)
                )
            );

        $automatic = new AutomaticBase($this->settings, $this->storage, $this->transfer, $this->adapter);

        $receipt = $automatic->fiscalize('0');
        $this->assertInstanceOf(Receipt::class, $receipt);
    }

    /**
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     * @covers Innokassa\MDK\Services\FiscalizationBaseAbstract::fiscalizeProc
     */
    public function testFiscalizeSuccessServerError()
    {
        $this->settings->method('getOnly2')
            ->willReturn(false);

        $this->storage
            ->method('getCollection')
            ->willReturn(new ReceiptCollection());

        $this->transfer
            ->method('sendReceipt')
            ->will($this->throwException(new TransferException('', 500)));

        $automatic = new AutomaticBase($this->settings, $this->storage, $this->transfer, $this->adapter);

        $receipt = $automatic->fiscalize('0');
        $this->assertInstanceOf(Receipt::class, $receipt);
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     * @covers Innokassa\MDK\Services\FiscalizationBaseAbstract::fiscalizeProc
     */
    public function testFiscalizeFailReceipt()
    {
        $this->settings->method('getOnly2')
            ->willReturn(true);

        $this->storage
            ->method('getCollection')
            ->willReturn(new ReceiptCollection());

        $this->transfer
            ->method('sendReceipt')
            ->will($this->throwException(new TransferException('', 400)));

        $automatic = new AutomaticBase($this->settings, $this->storage, $this->transfer, $this->adapter);

        $this->expectException(TransferException::class);
        $automatic->fiscalize('0');
    }

    /**
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeFailExistsHand()
    {
        $this->settings->method('getOnly2')
            ->willReturn(false);

        $receipts = new ReceiptCollection();
        $receipt = new Receipt();
        $receipt->setSubType(ReceiptSubType::HAND);
        $receipts[] = $receipt;

        $this->storage
            ->method('getCollection')
            ->willReturn($receipts);

        $automatic = new AutomaticBase($this->settings, $this->storage, $this->transfer, $this->adapter);

        $this->expectException(AutomaticException::class);
        $automatic->fiscalize('0');
    }

    /**
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeFailExistsType()
    {
        $this->settings->method('getOnly2')
            ->willReturn(false);

        $receipts = new ReceiptCollection();
        $receipt = new Receipt();
        $receipt->setType(ReceiptType::COMING);
        $receipt->setSubType(ReceiptSubType::PRE);
        $receipts[] = $receipt;

        $this->storage
            ->method('getCollection')
            ->will($this->onConsecutiveCalls(new ReceiptCollection(), $receipts));

        $automatic = new AutomaticBase($this->settings, $this->storage, $this->transfer, $this->adapter);

        $this->expectException(AutomaticException::class);
        $automatic->fiscalize('0', ReceiptSubType::PRE);
    }

    /**
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeFailExistsRefund2()
    {
        $this->settings->method('getOnly2')
            ->willReturn(false);

        $receipts = new ReceiptCollection();
        $receipt = new Receipt();
        $receipt->setType(ReceiptType::REFUND_COMING);
        $receipt->setSubType(ReceiptSubType::FULL);
        $receipts[] = $receipt;

        $this->storage
            ->method('getCollection')
            ->will($this->onConsecutiveCalls(new ReceiptCollection(), $receipts));

        $automatic = new AutomaticBase($this->settings, $this->storage, $this->transfer, $this->adapter);

        $this->expectException(AutomaticException::class);
        $automatic->fiscalize('0');
    }

    /**
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeFailExistsComingFull()
    {
        $this->settings->method('getOnly2')
            ->willReturn(false);

        $receipts = new ReceiptCollection();
        $receipt = new Receipt();
        $receipt->setType(ReceiptType::COMING);
        $receipt->setSubType(ReceiptSubType::FULL);
        $receipts[] = $receipt;

        $this->storage
            ->method('getCollection')
            ->will($this->onConsecutiveCalls(new ReceiptCollection(), $receipts));

        $automatic = new AutomaticBase($this->settings, $this->storage, $this->transfer, $this->adapter);

        $this->expectException(AutomaticException::class);
        $automatic->fiscalize('0');
    }
}
