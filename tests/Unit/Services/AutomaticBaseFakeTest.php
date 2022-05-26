<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Settings\SettingsConn;
use Innokassa\MDK\Services\AutomaticBase;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Settings\SettingsAbstract;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\ConverterAbstract;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Entities\Atoms\PaymentMethod;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Entities\Primitives\Customer;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Collections\ReceiptCollection;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Entities\ReceiptAdapterInterface;
use Innokassa\MDK\Collections\ReceiptItemCollection;
use Innokassa\MDK\Exceptions\Services\AutomaticException;
use Innokassa\MDK\Entities\ReceiptId\ReceiptIdFactoryMeta;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;
use Innokassa\MDK\Exceptions\Services\AutomaticErrorException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Services\AutomaticBase
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
 * @uses Innokassa\MDK\Exceptions\TransferException
 * @uses Innokassa\MDK\Entities\ReceiptId\ReceiptIdFactoryMeta
 * @uses Innokassa\MDK\Settings\SettingsConn
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
            ->will($this->returnArgument(1));

        $this->storage = $this->createMock(ReceiptStorageInterface::class);

        $this->settings = $this->createMock(SettingsAbstract::class);
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
        $this->settings->method('extrudeConn')
            ->willReturn(new SettingsConn(TEST_ACTOR_ID, TEST_ACTOR_TOKEN, TEST_CASHBOX_WITHOUT_AGENT));
    }

    //######################################################################

    /**
     * Тест чека предоплаты
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeSuccessPre()
    {
        $this->settings->method('getScheme')
            ->willReturn(SettingsAbstract::SCHEME_PRE_FULL);

        $this->storage
            ->method('getCollection')
            ->willReturn(new ReceiptCollection());

        $automatic = new AutomaticBase(
            $this->settings,
            $this->storage,
            $this->transfer,
            $this->adapter,
            new ReceiptIdFactoryMeta()
        );

        $receipt = $automatic->fiscalize('0', 's1');
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertSame(ReceiptType::COMING, $receipt->getType());
        $this->assertSame(ReceiptSubType::PRE, $receipt->getSubType());
        $this->assertSame('s1', $receipt->getSiteId());
        $this->assertSame(200.0, $receipt->getAmount()->get(Amount::CASHLESS));
        $this->assertSame('Test', $receipt->getCustomer()->getName());
        $this->assertSame('+79998887766', $receipt->getNotify()->getPhone());

        $receipt = $automatic->fiscalize('0', '', ReceiptSubType::PRE);
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertSame(ReceiptType::COMING, $receipt->getType());
        $this->assertSame(ReceiptSubType::PRE, $receipt->getSubType());
        $this->assertSame('', $receipt->getSiteId());
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
        $this->settings->method('getScheme')
            ->willReturn(SettingsAbstract::SCHEME_PRE_FULL);

        $this->storage
            ->method('getCollection')
            ->willReturn(new ReceiptCollection());

        $automatic = new AutomaticBase(
            $this->settings,
            $this->storage,
            $this->transfer,
            $this->adapter,
            new ReceiptIdFactoryMeta()
        );

        $receipt = $automatic->fiscalize('0', '', ReceiptSubType::FULL);
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertSame(ReceiptSubType::FULL, $receipt->getSubType());
        $this->assertSame(200.0, $receipt->getAmount()->get(Amount::CASHLESS));
    }

    /**
     * Тест чека полного расчета с еще не пробитым чеком предоплаты
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    /*public function testFiscalizeFailFull2()
    {
        $this->settings->method('getScheme')
            ->willReturn(SettingsAbstract::SCHEME_PRE_FULL);

        $receipts = new ReceiptCollection();
        $receipt = new Receipt();
        $receipt->setType(ReceiptType::COMING);
        $receipt->setSubType(ReceiptSubType::PRE);
        $receipts[] = $receipt;

        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $this->storage
            ->method('getCollection')
            ->will($this->onConsecutiveCalls($receipts));

        $automatic = new AutomaticBase(
            $this->settings,
            $this->storage,
            $this->transfer,
            $this->adapter,
            new ReceiptIdFactoryMeta()
        );

        $this->expectException(AutomaticException::class);
        $receipt = $automatic->fiscalize('0');
    }*/

    /**
     * Тест чека полного расчета с чеком предоплаты
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeSuccessFull2()
    {
        $this->settings->method('getScheme')
            ->willReturn(SettingsAbstract::SCHEME_PRE_FULL);

        $receipts = new ReceiptCollection();
        $receipt = new Receipt();
        $receipt->setType(ReceiptType::COMING);
        $receipt->setSubType(ReceiptSubType::PRE);
        $receipt->setStatus(new ReceiptStatus(ReceiptStatus::COMPLETED));
        $receipt->addItem(
            (new ReceiptItem())
                ->setName('test')
                ->setPrice(100)
                ->setQuantity(2)
                ->setPaymentMethod(PaymentMethod::PREPAYMENT_FULL)
        );
        $receipts[] = $receipt;

        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $this->storage
            ->method('getCollection')
            ->will($this->onConsecutiveCalls($receipts));

        $automatic = new AutomaticBase(
            $this->settings,
            $this->storage,
            $this->transfer,
            $this->adapter,
            new ReceiptIdFactoryMeta()
        );
        $receipt = $automatic->fiscalize('0');
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertSame(ReceiptSubType::FULL, $receipt->getSubType());
        $this->assertSame(200.0, $receipt->getAmount()->get(Amount::PREPAYMENT));
    }

    /**
     * Тест чека полного расчета с чеком предоплаты, при этом их суммы не будут равны
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeFailFull2()
    {
        $this->settings->method('getScheme')
            ->willReturn(SettingsAbstract::SCHEME_PRE_FULL);

        $receipts = new ReceiptCollection();
        $receipt = new Receipt();
        $receipt->setType(ReceiptType::COMING);
        $receipt->setSubType(ReceiptSubType::PRE);
        $receipt->setStatus(new ReceiptStatus(ReceiptStatus::COMPLETED));
        $receipt->addItem(
            (new ReceiptItem())
                ->setName('test')
                ->setPrice(100)
                ->setQuantity(1)
                ->setPaymentMethod(PaymentMethod::PREPAYMENT_FULL)
        );
        $receipts[] = $receipt;

        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $this->storage
            ->method('getCollection')
            ->will($this->onConsecutiveCalls($receipts));

        $automatic = new AutomaticBase(
            $this->settings,
            $this->storage,
            $this->transfer,
            $this->adapter,
            new ReceiptIdFactoryMeta()
        );

        $this->expectException(AutomaticErrorException::class);
        $receipt = $automatic->fiscalize('0');
    }

    //**********************************************************************

    /**
     * Тест чека полного рачета при наличии настройки "пробивать только второй чек"
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeSuccessGetOnly2()
    {
        $this->settings->method('getScheme')
            ->willReturn(SettingsAbstract::SCHEME_ONLY_FULL);

        $this->storage
            ->method('getCollection')
            ->willReturn(new ReceiptCollection());

        $automatic = new AutomaticBase(
            $this->settings,
            $this->storage,
            $this->transfer,
            $this->adapter,
            new ReceiptIdFactoryMeta()
        );

        $receipt = $automatic->fiscalize('0');
        $this->assertInstanceOf(Receipt::class, $receipt);
        $this->assertSame(ReceiptSubType::FULL, $receipt->getSubType());
        $this->assertSame(200.0, $receipt->getAmount()->get(Amount::CASHLESS));
    }

    //**********************************************************************

    /**
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeSuccessServerError()
    {
        $this->settings->method('getScheme')
            ->willReturn(SettingsAbstract::SCHEME_PRE_FULL);

        $this->storage
            ->method('getCollection')
            ->willReturn(new ReceiptCollection());

        $this->transfer
            ->method('sendReceipt')
            ->will($this->throwException(new TransferException('', 500)));

        $automatic = new AutomaticBase(
            $this->settings,
            $this->storage,
            $this->transfer,
            $this->adapter,
            new ReceiptIdFactoryMeta()
        );

        $receipt = $automatic->fiscalize('0');
        $this->assertInstanceOf(Receipt::class, $receipt);
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeFailReceipt()
    {
        $this->settings->method('getScheme')
            ->willReturn(SettingsAbstract::SCHEME_ONLY_FULL);

        $this->storage
            ->method('getCollection')
            ->willReturn(new ReceiptCollection());

        $this->transfer
            ->method('sendReceipt')
            ->will($this->throwException(new TransferException('', 400)));

        $automatic = new AutomaticBase(
            $this->settings,
            $this->storage,
            $this->transfer,
            $this->adapter,
            new ReceiptIdFactoryMeta()
        );

        $this->expectException(TransferException::class);
        $automatic->fiscalize('0');
    }

    /**
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeFailExistsType()
    {
        $this->settings->method('getScheme')
            ->willReturn(SettingsAbstract::SCHEME_PRE_FULL);

        $receipts = new ReceiptCollection();
        $receipt = new Receipt();
        $receipt->setType(ReceiptType::COMING);
        $receipt->setSubType(ReceiptSubType::PRE);
        $receipts[] = $receipt;

        $this->storage
            ->method('getCollection')
            ->will($this->onConsecutiveCalls($receipts));

        $automatic = new AutomaticBase(
            $this->settings,
            $this->storage,
            $this->transfer,
            $this->adapter,
            new ReceiptIdFactoryMeta()
        );

        $this->expectException(AutomaticException::class);
        $automatic->fiscalize('0', '', ReceiptSubType::PRE);
    }

    /**
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeFailExistsComingFull()
    {
        $this->settings->method('getScheme')
            ->willReturn(SettingsAbstract::SCHEME_PRE_FULL);

        $receipts = new ReceiptCollection();
        $receipt = new Receipt();
        $receipt->setType(ReceiptType::COMING);
        $receipt->setSubType(ReceiptSubType::FULL);
        $receipts[] = $receipt;

        $this->storage
            ->method('getCollection')
            ->will($this->onConsecutiveCalls($receipts));

        $automatic = new AutomaticBase(
            $this->settings,
            $this->storage,
            $this->transfer,
            $this->adapter,
            new ReceiptIdFactoryMeta()
        );

        $this->expectException(AutomaticException::class);
        $automatic->fiscalize('0');
    }

    /**
     * @covers Innokassa\MDK\Services\AutomaticBase::__construct
     * @covers Innokassa\MDK\Services\AutomaticBase::fiscalize
     */
    public function testFiscalizeFailErrorAdapter()
    {
        $this->adapter->method('getTotal')
            ->will($this->throwException(new InvalidArgumentException()));

        $this->storage
            ->method('getCollection')
            ->willReturn(new ReceiptCollection());

        $automatic = new AutomaticBase(
            $this->settings,
            $this->storage,
            $this->transfer,
            $this->adapter,
            new ReceiptIdFactoryMeta()
        );

        $this->expectException(InvalidArgumentException::class);
        $automatic->fiscalize('0');
    }
}
