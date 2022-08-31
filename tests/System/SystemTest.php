<?php

use Innokassa\MDK\Client;
use Innokassa\MDK\Net\Transfer;
use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Net\ConverterApi;
use Innokassa\MDK\Logger\LoggerFile;
use Innokassa\MDK\Net\NetClientCurl;
use Innokassa\MDK\Entities\Atoms\Vat;
use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Services\PipelineBase;
use Innokassa\MDK\Storage\ReceiptFilter;
use Innokassa\MDK\Services\AutomaticBase;
use Innokassa\MDK\Services\ConnectorBase;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Storage\ConverterStorage;
use Innokassa\MDK\Settings\SettingsAbstract;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Entities\Atoms\PaymentMethod;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Entities\Primitives\Customer;
use Innokassa\MDK\Exceptions\SettingsException;
use Innokassa\MDK\Collections\ReceiptCollection;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;
use Innokassa\MDK\Entities\Atoms\ReceiptItemType;
use Innokassa\MDK\Collections\ReceiptItemCollection;
use Innokassa\MDK\Exceptions\Services\AutomaticException;
use Innokassa\MDK\Entities\ReceiptId\ReceiptIdFactoryMeta;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Collections\BaseCollection
 * @uses Innokassa\MDK\Entities\AtomAbstract
 * @uses Innokassa\MDK\Entities\Atoms\PaymentMethod
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptItemType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptStatus
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptSubType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptType
 * @uses Innokassa\MDK\Entities\Atoms\Taxation
 * @uses Innokassa\MDK\Entities\Atoms\Vat
 * @uses Innokassa\MDK\Entities\ConverterAbstract
 * @uses Innokassa\MDK\Entities\Primitives\Amount
 * @uses Innokassa\MDK\Entities\Primitives\Customer
 * @uses Innokassa\MDK\Entities\Primitives\Notify
 * @uses Innokassa\MDK\Entities\Receipt
 * @uses Innokassa\MDK\Entities\ReceiptItem
 * @uses Innokassa\MDK\Storage\ConverterStorage
 * @uses Innokassa\MDK\Storage\ReceiptFilter
 * @uses Innokassa\MDK\Client
 * @uses Innokassa\MDK\Collections\ReceiptCollection
 * @uses Innokassa\MDK\Collections\ReceiptItemCollection
 * @uses Innokassa\MDK\Exceptions\TransferException
 * @uses Innokassa\MDK\Net\ConverterApi
 * @uses Innokassa\MDK\Net\NetClientCurl
 * @uses Innokassa\MDK\Net\Transfer
 */
class SystemTest extends TestCase
{
    protected static $db;

    /** @var SettingsConcrete */
    protected static $settings;

    /** @var ReceiptStorageConcrete */
    protected static $storage;

    /** @var ReceiptAdapterConcrete */
    protected static $adapter;

    /** @var Client */
    protected static $client;

    /** @var LoggerFile */
    protected static $logger;

    public static function setUpBeforeClass(): void
    {
        self::$db = new db('db', 'root', 'root');
        self::$db->query(file_get_contents(__DIR__ . '/db.sql'));

        self::$settings = new SettingsConcrete([
            'actor_id' => TEST_ACTOR_ID,
            'actor_token' => TEST_ACTOR_TOKEN,
            'cashbox' => TEST_CASHBOX_WITHOUT_AGENT,
            'site' => 'https://example.com/',
            'taxation' => Taxation::USN,
            'scheme' => SettingsAbstract::SCHEME_PRE_FULL,
            'type_default_items' => ReceiptItemType::PRODUCT,
            'vat_default_items' => Vat::CODE_WITHOUT
        ]);

        self::$storage = new ReceiptStorageConcrete(
            new ConverterStorage(new ReceiptIdFactoryMeta()),
            self::$db
        );
        self::$adapter = new ReceiptAdapterConcrete(self::$db, self::$settings);

        self::$logger = new LoggerFile();

        $transfer = new Transfer(
            new NetClientCurl(),
            new ConverterApi(),
            self::$logger
        );

        $automatic = new AutomaticBase(
            self::$settings,
            self::$storage,
            $transfer,
            self::$adapter,
            new ReceiptIdFactoryMeta()
        );
        $pipeline = new PipelineBase(self::$settings, self::$storage, $transfer);
        $connector = new ConnectorBase($transfer);

        self::$client = new Client(
            self::$settings,
            self::$storage,
            $automatic,
            $pipeline,
            $connector,
            self::$logger
        );
    }

    //######################################################################

    /**
     * @covers ReceiptStorageConcrete
     */
    public function testStorage()
    {
        $receipt = new Receipt();
        $receipt
            ->setType(ReceiptType::COMING)
            ->setOrderId('456')
            ->addItem(
                (new ReceiptItem())
                    ->setPrice(100.0)
                    ->setQuantity(2)
                    ->setName('name')
            )
            ->setTaxation(Taxation::USN)
            ->setAmount((new Amount())->setCashless(200))
            ->setNotify(new Notify('box@domain.zone'))
            ->setCustomer(new Customer('Test'))
            ->setLocation('https://example.com/');
        $receipt->setReceiptId((new ReceiptIdFactoryMeta())->build($receipt));

        $index = self::$storage->save($receipt);
        $this->assertSame($index, $receipt->getId());

        $receipt->setType(ReceiptType::REFUND_COMING);
        $index = self::$storage->save($receipt);
        $this->assertSame($index, $receipt->getId());

        $receipt->setId(0);
        $receipt->setType(ReceiptType::COMING);
        $index2 = self::$storage->save($receipt);
        $this->assertSame($index + 1, $index2);


        $receiptFromDB = self::$storage->getOne($index);
        $this->assertSame($index, $receiptFromDB->getId());
        $this->assertSame(ReceiptType::REFUND_COMING, $receiptFromDB->getType());
        $this->assertSame(Taxation::USN, $receiptFromDB->getTaxation());
        $this->assertSame(200.0, $receiptFromDB->getAmount()->getCashless());
        $this->assertSame('box@domain.zone', $receiptFromDB->getNotify()->getEmail());
        $this->assertSame('Test', $receiptFromDB->getCustomer()->getName());
        $this->assertSame('https://example.com/', $receiptFromDB->getLocation());
        $this->assertSame(1, $receiptFromDB->getItems()->count());


        $receiptsComing = self::$storage->getCollection(
            (new ReceiptFilter())
                ->setType(ReceiptType::COMING)
        );

        $this->assertInstanceOf(ReceiptCollection::class, $receiptsComing);
    }

    //######################################################################

    /**
     * @covers ReceiptAdapterConcrete
     */
    public function testAdapter()
    {
        $items = self::$adapter->getItems(1, null, ReceiptSubType::PRE);
        $this->assertInstanceOf(ReceiptItemCollection::class, $items);
        $this->assertSame(PaymentMethod::PREPAYMENT_FULL, $items[0]->getPaymentMethod());

        $items = self::$adapter->getItems(1, null, ReceiptSubType::FULL);
        $this->assertSame(PaymentMethod::PAYMENT_FULL, $items[0]->getPaymentMethod());

        $total = self::$adapter->getTotal(1, null);
        $this->assertIsFloat($total);
        $this->assertTrue($total > 0);

        $customer = self::$adapter->getCustomer(1, null);
        $this->assertInstanceOf(Customer::class, $customer);

        $notify = self::$adapter->getNotify(1, null);
        $this->assertInstanceOf(Notify::class, $notify);
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Services\AutomaticBase
     * @depends testConnectorSuccess
     * @depends testStorage
     * @depends testAdapter
     */
    public function testAutomatic()
    {
        $automatic = self::$client->serviceAutomatic();
        $receipt1 = $automatic->fiscalize(1, '', ReceiptSubType::PRE);
        $this->assertTrue($receipt1->getStatus()->getCode() != ReceiptStatus::ERROR);
        $receipt2 = $automatic->fiscalize(1, '', ReceiptSubType::FULL);
        $this->assertTrue($receipt2->getStatus()->getCode() != ReceiptStatus::ERROR);

        $this->expectException(AutomaticException::class);
        $automatic->fiscalize(1, '', ReceiptSubType::FULL);
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Services\PipelineBase
     * @depends testConnectorSuccess
     * @depends testStorage
     * @depends testAdapter
     * @depends testAutomatic
     */
    public function testPipelineSuccess()
    {
        $receipts = [];
        $automatic = self::$client->serviceAutomatic();

        /*
            создадим чек для заказа 5, пробьем и специально установим статус ACCEPTED,
            в тестах будем ждать COMPLETED | ACCEPTED
        */
        $orderId = 5;
        $receiptComing = $automatic->fiscalize($orderId);
        $receiptComing->setStatus(new ReceiptStatus(ReceiptStatus::ACCEPTED));
        self::$storage->save($receiptComing);
        $receipts[$receiptComing->getId()] = [ReceiptStatus::COMPLETED, ReceiptStatus::ACCEPTED];

        /*
            создадим чек для заказа 6, пробьем и специально установим статус PREPARED,
            в тестах будем ждать EXPIRED
        */
        $orderId = 6;
        $receiptComing = $automatic->fiscalize($orderId);
        $receiptComing->setStatus(new ReceiptStatus(ReceiptStatus::PREPARED));
        $receiptComing->setStartTime(date('Y-m-d H:i:s', time() - (Receipt::ALLOWED_ATTEMPT_TIME + 1)));
        self::$storage->save($receiptComing);
        $receipts[$receiptComing->getId()] = [ReceiptStatus::EXPIRED];

        /*
            создадим чек для заказа 3, присвоим ему статус PREPARED,
            в тестах будем ждать COMPLETED | ACCEPTED
        */
        $orderId = 3;
        $receiptComing = $automatic->fiscalize($orderId);
        $receiptComing->setStatus(new ReceiptStatus(ReceiptStatus::PREPARED));
        self::$storage->save($receiptComing);
        $receipts[$receiptComing->getId()] = [ReceiptStatus::COMPLETED, ReceiptStatus::ACCEPTED];


        /*
            создадим еще один чек для несуществующего заказа 10,
            не будем фискализировать и установим статус PREPARED
            в тестах будем ждать COMPLETED | ACCEPTED
        */
        $receipt = new Receipt();
        $receipt
            ->setId(0)
            ->setStatus(new ReceiptStatus(ReceiptStatus::PREPARED))
            ->setOrderId(10)
            ->setType(ReceiptType::COMING)
            ->addItem(
                (new ReceiptItem())
                    ->setPrice(100.0)
                    ->setQuantity(2)
                    ->setName('name')
            )
            ->setTaxation(Taxation::USN)
            ->setAmount((new Amount())->setCashless(200))
            ->setNotify(new Notify('box@domain.zone'))
            ->setCustomer(new Customer('Test'))
            ->setLocation('https://example.com/');
        $receipt->setReceiptId((new ReceiptIdFactoryMeta())->build($receipt));
        self::$storage->save($receipt);
        $receipts[$receipt->getId()] = [ReceiptStatus::COMPLETED, ReceiptStatus::ACCEPTED];


        $pipeline = self::$client->servicePipeline();
        $pipeline->update(__DIR__ . '/../../.pipeline');
        foreach ($receipts as $key => $value) {
            $receipt = self::$storage->getOne($key);
            $statusCode = $receipt->getStatus()->getCode();
            $this->assertContains($statusCode, $value);
        }
    }

    /**
     * @covers Innokassa\MDK\Services\Monitoring
     * @depends testPipelineSuccess
     */
    public function testMonitoringSuccess()
    {
        $file = __DIR__ . '/../../.monitoring';
        $pipeline = self::$client->servicePipeline();
        $this->assertTrue($pipeline->monitoring($file, 'start_time'));
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Services\ConnectorBase
     */
    public function testConnectorSuccess()
    {
        $connector = self::$client->serviceConnector();

        $this->assertTrue($connector->testSettings(self::$settings));

        $transfer = new Transfer(
            new NetClientCurl(),
            new ConverterApi(),
            self::$logger
        );
        $connector = new ConnectorBase($transfer);

        $this->assertTrue($connector->testSettings(self::$settings));
    }

    /**
     * @covers Innokassa\MDK\Services\ConnectorBase
     */
    public function testConnectorFailAuth()
    {
        $settings = new SettingsConcrete([
            'actor_id' => '0',
            'actor_token' => TEST_ACTOR_TOKEN,
            'cashbox' => TEST_CASHBOX_WITHOUT_AGENT,
            'site' => 'https://example.com/',
            'taxation' => Taxation::USN,
        ]);
        $transfer = new Transfer(
            new NetClientCurl(),
            new ConverterApi(),
            self::$logger
        );
        $connector = new ConnectorBase($transfer);

        $this->expectException(SettingsException::class);
        $connector->testSettings($settings);
    }

    /**
     * @covers Innokassa\MDK\Services\ConnectorBase
     */
    public function testConnectorFailCashbox()
    {
        $settings = new SettingsConcrete([
            'actor_id' => TEST_ACTOR_ID,
            'actor_token' => TEST_ACTOR_TOKEN,
            'cashbox' => '0',
            'site' => 'https://example.com/',
            'taxation' => Taxation::USN,
        ]);
        $transfer = new Transfer(
            new NetClientCurl(),
            new ConverterApi(),
            self::$logger
        );
        $connector = new ConnectorBase($transfer);

        $this->expectException(SettingsException::class);
        $connector->testSettings($settings);
    }

    /**
     * @covers Innokassa\MDK\Services\ConnectorBase
     */
    public function testConnectorFailTaxation()
    {
        $connector = self::$client->serviceConnector();

        $this->expectException(SettingsException::class);
        $connector->testSettings(
            new SettingsConcrete([
                'actor_id' => TEST_ACTOR_ID,
                'actor_token' => TEST_ACTOR_TOKEN,
                'cashbox' => '0',
                'site' => 'https://example.com/',
                'taxation' => Taxation::ESN,
            ])
        );
    }
}
