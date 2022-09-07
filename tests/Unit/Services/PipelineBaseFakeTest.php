<?php

use Innokassa\MDK\Net\Transfer;
use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Settings\SettingsConn;
use Innokassa\MDK\Net\NetClientInterface;
use Innokassa\MDK\Settings\SettingsAbstract;
use Innokassa\MDK\Entities\ConverterAbstract;
use Innokassa\MDK\Services\PipelineBase;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\SettingsException;
use Innokassa\MDK\Collections\ReceiptCollection;
use Innokassa\MDK\Entities\ReceiptId\ReceiptIdFactoryInterface;
use Innokassa\MDK\Storage\ReceiptStorageInterface;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Services\PipelineBase
 * @uses Innokassa\MDK\Net\Transfer
 * @uses Innokassa\MDK\Exceptions\TransferException
 * @uses Innokassa\MDK\Entities\AtomAbstract
 * @uses Innokassa\MDK\Entities\Atoms\Taxation
 * @uses Innokassa\MDK\Collections\BaseCollection
 * @uses Innokassa\MDK\Collections\ReceiptCollection
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptStatus
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptSubType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptType
 * @uses Innokassa\MDK\Entities\Receipt
 * @uses Innokassa\MDK\Storage\ReceiptFilter
 * @uses Innokassa\MDK\Exceptions\BaseException
 * @uses Innokassa\MDK\Settings\SettingsConn
 */
class PipelineBaseFakeTest extends TestCase
{
    private $client;
    private $converter;
    private $storage;
    private $settings;
    private $fileLock;

    protected function setUp(): void
    {
        $this->fileLock = __DIR__ . '/../../../.pipeline';
        $this->client = $this->createMock(NetClientInterface::class);
        $this->client->method('send')
            ->will($this->returnSelf());
        $this->client->method('write')
            ->will($this->returnSelf());
        $this->client->method('reset')
            ->will($this->returnSelf());

        $this->converter = $this->createMock(ConverterAbstract::class);
        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $this->receiptIdFactory = $this->createMock(ReceiptIdFactoryInterface::class);
        $this->receiptIdFactory
            ->method('build')
            ->willReturn('asdf');

        $this->settings = $this->createMock(SettingsAbstract::class);
        $this->settings
            ->method('getActorId')
            ->willReturn(TEST_ACTOR_ID);
        $this->settings
            ->method('getActorToken')
            ->willReturn(TEST_ACTOR_TOKEN);
        $this->settings
            ->method('getCashbox')
            ->willReturn(TEST_CASHBOX_WITHOUT_AGENT);
        $this->settings
            ->method('extrudeConn')
            ->willReturn(new SettingsConn(TEST_ACTOR_ID, TEST_ACTOR_TOKEN, TEST_CASHBOX_WITHOUT_AGENT));
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::update
     */
    public function testUpdateLock()
    {
        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $pipeline = new PipelineBase($this->settings, $this->storage, $transfer, $this->receiptIdFactory);

        $fp = fopen($this->fileLock, "w+");
        flock($fp, LOCK_EX);
        $this->assertFalse($pipeline->update($this->fileLock));
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::update
     * @covers Innokassa\MDK\Services\PipelineBase::processing
     * @covers Innokassa\MDK\Services\PipelineBase::extrudeConn
     */
    public function testUpdateSuccess200()
    {
        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $receipts = new ReceiptCollection();
        for ($i = 0; $i < PipelineBase::COUNT_SELECT; ++$i) {
            $receipts[] = (new Receipt())->setId($i + 1);
        }

        /*
            ожидание 2 вызова т.к. в БД PipelineBase::COUNT_SELECT чеков, для одной итерации одного статуса хватит,
            но будет взведена вторая, в которой будет пустая коллекция
        */
        $this->storage
            ->expects($this->exactly(2))
            ->method('getCollection')
            ->will(
                $this->onConsecutiveCalls(
                    $receipts,
                    new ReceiptCollection(),
                    new ReceiptCollection()
                )
            );

        $this->storage
            ->expects($this->exactly(PipelineBase::COUNT_SELECT))
            ->method('save');

        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, '{}'],
                [NetClientInterface::CODE, 200]
            ]));

        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $pipeline = new PipelineBase($this->settings, $this->storage, $transfer, $this->receiptIdFactory);
        $this->assertTrue($pipeline->update($this->fileLock));

        for ($i = 0; $i < PipelineBase::COUNT_SELECT; ++$i) {
            $this->assertSame(ReceiptStatus::COMPLETED, $receipts[$i]->getStatus()->getCode());
        }
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::update
     * @covers Innokassa\MDK\Services\PipelineBase::processing
     * @covers Innokassa\MDK\Services\PipelineBase::extrudeConn
     */
    public function testUpdateChangeReceiptId()
    {
        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $receipts = new ReceiptCollection();
        $receipts[] = (new Receipt())->setId(1);

        $this->storage
            ->expects($this->exactly(1))
            ->method('getCollection')
            ->will(
                $this->onConsecutiveCalls(
                    $receipts
                )
            );

        $this->storage
            ->expects($this->exactly(1))
            ->method('save');

        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 500]
            ]));

        $transfer = new Transfer(
            $this->client,
            $this->converter
        );

        $receiptIdFactory = $this->createMock(ReceiptIdFactoryInterface::class);
        $receiptIdFactory
            ->expects($this->exactly(1))
            ->method('build')
            ->willReturn('asdf');

        $pipeline = new PipelineBase($this->settings, $this->storage, $transfer, $receiptIdFactory);
        $this->assertTrue($pipeline->update($this->fileLock));
        $this->assertSame(ReceiptStatus::PREPARED, $receipts[0]->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::update
     * @covers Innokassa\MDK\Services\PipelineBase::processing
     * @covers Innokassa\MDK\Services\PipelineBase::extrudeConn
     */
    public function testUpdateFailExtrudeConn()
    {
        $settings = $this->createMock(SettingsAbstract::class);
        $settings
            ->method('extrudeConn')
            ->will($this->throwException(new SettingsException('')));

        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $receipts = new ReceiptCollection();
        $receipts[] = (new Receipt())->setId(1);
        $receipts[] = (new Receipt())->setId(2);

        // ожидание одного вызова т.к. запросы из БД по двум статусам
        $this->storage
            ->expects($this->exactly(1))
            ->method('getCollection')
            ->will($this->onConsecutiveCalls($receipts, new ReceiptCollection()));

        $this->storage
            ->expects($this->exactly(2))
            ->method('save');

        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, '{}'],
                [NetClientInterface::CODE, 404]
            ]));

        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $pipeline = new PipelineBase($settings, $this->storage, $transfer, $this->receiptIdFactory);
        $this->assertTrue($pipeline->update($this->fileLock));

        $this->assertSame(ReceiptStatus::PREPARED, $receipts[0]->getStatus()->getCode());
        $this->assertSame(ReceiptStatus::PREPARED, $receipts[1]->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::update
     * @covers Innokassa\MDK\Services\PipelineBase::processing
     */
    public function testUpdateSuccess404()
    {
        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $receipts = new ReceiptCollection();
        $receipts[] = (new Receipt())->setId(1);
        $receipts[] = (new Receipt())->setId(2);

        // ожидание одного вызова т.к. запросы из БД по двум статусам
        $this->storage
            ->expects($this->exactly(1))
            ->method('getCollection')
            ->will($this->onConsecutiveCalls($receipts, new ReceiptCollection()));

        $this->storage
            ->expects($this->exactly(2))
            ->method('save');

        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, '{}'],
                [NetClientInterface::CODE, 404]
            ]));

        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $pipeline = new PipelineBase($this->settings, $this->storage, $transfer, $this->receiptIdFactory);
        $this->assertTrue($pipeline->update($this->fileLock));

        $this->assertSame(ReceiptStatus::ERROR, $receipts[0]->getStatus()->getCode());
        $this->assertSame(ReceiptStatus::ERROR, $receipts[1]->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::update
     * @covers Innokassa\MDK\Services\PipelineBase::processing
     */
    public function testUpdateError()
    {
        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $receipts1 = new ReceiptCollection();
        for ($i = 0; $i < PipelineBase::COUNT_SELECT; ++$i) {
            $receipts1[] = new Receipt();
        }

        $receipts2 = new ReceiptCollection();
        $receipts2[] = new Receipt();

        /*
            ожидание одного вызова потому что в БД только PipelineBase::COUNT_SELECT+1 чеков
            и все получат ошибочный статус
        */
        $this->storage
            ->expects($this->exactly(1))
            ->method('getCollection')
            ->will($this->onConsecutiveCalls($receipts1, $receipts2));

        $this->storage
            ->expects($this->exactly(PipelineBase::COUNT_SELECT))
            ->method('save');

        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 500]
            ]));

        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $pipeline = new PipelineBase($this->settings, $this->storage, $transfer, $this->receiptIdFactory);
        $this->assertTrue($pipeline->update($this->fileLock));

        for ($i = 0; $i < PipelineBase::COUNT_SELECT; ++$i) {
            $this->assertSame(ReceiptStatus::PREPARED, $receipts1[$i]->getStatus()->getCode());
        }

        $this->assertSame(ReceiptStatus::PREPARED, $receipts2[0]->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::update
     * @covers Innokassa\MDK\Services\PipelineBase::processing
     */
    public function testUpdateExpired()
    {
        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $receipts = new ReceiptCollection();
        $receipts[] = (new Receipt())
            ->setStartTime(date("Y-m-d H:i:s", time() - (Receipt::ALLOWED_ATTEMPT_TIME + 1)))
            ->setStatus(new ReceiptStatus(ReceiptStatus::PREPARED));

        $this->storage
            ->method('getCollection')
            ->willReturn($receipts);

        $this->storage
            ->expects($this->exactly(1))
            ->method('save');

        /*$this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 200]
            ]));*/

        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $pipeline = new PipelineBase($this->settings, $this->storage, $transfer, $this->receiptIdFactory);

        $this->assertTrue($pipeline->update($this->fileLock));
        $this->assertSame(ReceiptStatus::EXPIRED, $receipts[0]->getStatus()->getCode());
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::monitoring
     */
    public function testMonitoring()
    {
        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $pipeline = new PipelineBase($this->settings, $this->storage, $transfer, $this->receiptIdFactory);

        $this->storage
            ->expects($this->exactly(5))
            ->method('count')
            ->will($this->onConsecutiveCalls(1, 2, 3, 4, 5));

        $this->storage
            ->expects($this->exactly(1))
            ->method('max')
            ->will($this->onConsecutiveCalls(date('Y-m-d H:i:s', time() - 60 * 60)));

        $this->storage
            ->expects($this->exactly(4))
            ->method('min')
            ->will($this->onConsecutiveCalls(
                date('Y-m-d H:i:s', time() - 60 * 60 * 1),
                date('Y-m-d H:i:s', time() - 60 * 60 * 2),
                date('Y-m-d H:i:s', time() - 60 * 60 * 3),
                date('Y-m-d H:i:s', time() - 60 * 60 * 4)
            ));

        $file = __DIR__ . '/../../../.monitoring';
        if (file_exists($file)) {
            unlink($file);
        }
        $this->assertTrue($pipeline->monitoring($file, 'start_time'));
        $this->assertTrue(file_exists($file));
    }
}
