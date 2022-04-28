<?php

use Innokassa\MDK\Net\Transfer;
use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Services\PipelineBase;
use Innokassa\MDK\Net\NetClientInterface;
use Innokassa\MDK\Entities\ConverterAbstract;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Collections\ReceiptCollection;
use Innokassa\MDK\Exceptions\NetConnectException;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Logger\LoggerInterface;

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
 */
class PipelineBaseFakeTest extends TestCase
{
    private $client;
    private $converter;
    private $storage;
    private $logger;

    protected function setUp(): void
    {
        $this->client = $this->createMock(NetClientInterface::class);
        $this->client->method('send')
            ->will($this->returnSelf());
        $this->client->method('write')
            ->will($this->returnSelf());
        $this->client->method('reset')
            ->will($this->returnSelf());

        $this->converter = $this->createMock(ConverterAbstract::class);
        $this->storage = $this->createMock(ReceiptStorageInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateAccepted
     */
    public function testUpdateAcceptedLock()
    {
        $transfer = new Transfer(
            $this->client,
            $this->converter,
            TEST_ACTOR_ID,
            TEST_ACTOR_TOKEN,
            TEST_CASHBOX_WITHOUT_AGENT,
            $this->logger
        );
        $pipeline = new PipelineBase($this->storage, $transfer);

        $fp = fopen(PipelineBase::LOCK_FILE_ACCEPTED, "w+");
        flock($fp, LOCK_EX);
        $this->assertFalse($pipeline->updateAccepted());
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateAccepted
     * @covers Innokassa\MDK\Services\PipelineBase::processingAccepted
     * @covers Innokassa\MDK\Services\PipelineBase::canContinue
     * @covers Innokassa\MDK\Services\PipelineBase::runCycle
     */
    public function testUpdateAcceptedSuccess200()
    {
        $receipts = new ReceiptCollection();
        for ($i = 0; $i < PipelineBase::COUNT_SELECT; ++$i) {
            $receipts[] = (new Receipt())->setId($i + 1);
        }

        /*
            ожидание 3 вызова т.к. в БД PipelineBase::COUNT_SELECT чеков, для одной итерации одного статуса хватит,
            но будет взведена вторая, в которой будет пустая коллекция,
            а на второй статус будет получена пустая коллекция и цикл будет прерван
        */
        $this->storage
            ->expects($this->exactly(3))
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
            $this->converter,
            TEST_ACTOR_ID,
            TEST_ACTOR_TOKEN,
            TEST_CASHBOX_WITHOUT_AGENT,
            $this->logger
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $this->assertTrue($pipeline->updateAccepted());

        for ($i = 0; $i < PipelineBase::COUNT_SELECT; ++$i) {
            $this->assertSame(ReceiptStatus::COMPLETED, $receipts[$i]->getStatus()->getCode());
        }
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateAccepted
     * @covers Innokassa\MDK\Services\PipelineBase::processingAccepted
     * @covers Innokassa\MDK\Services\PipelineBase::canContinue
     * @covers Innokassa\MDK\Services\PipelineBase::runCycle
     */
    public function testUpdateAcceptedSuccess404()
    {
        $receipts = new ReceiptCollection();
        $receipts[] = (new Receipt())->setId(1);
        $receipts[] = (new Receipt())->setId(2);

        // ожидание двух вызовов т.к. запросы из БД по двум статусам
        $this->storage
            ->expects($this->exactly(2))
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
            $this->converter,
            TEST_ACTOR_ID,
            TEST_ACTOR_TOKEN,
            TEST_CASHBOX_WITHOUT_AGENT,
            $this->logger
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $this->assertTrue($pipeline->updateAccepted());

        $this->assertSame(ReceiptStatus::REPEAT, $receipts[0]->getStatus()->getCode());
        $this->assertSame(ReceiptStatus::REPEAT, $receipts[1]->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateAccepted
     * @covers Innokassa\MDK\Services\PipelineBase::processingAccepted
     * @covers Innokassa\MDK\Services\PipelineBase::canContinue
     * @covers Innokassa\MDK\Services\PipelineBase::runCycle
     */
    public function testUpdateAcceptedError()
    {
        $receipts = new ReceiptCollection();
        for ($i = 0; $i < PipelineBase::MAX_COUNT_ERR + 1; ++$i) {
            $receipts[] = new Receipt();
        }

        /*
            ожидание одного вызова потому что в БД только PipelineBase::MAX_COUNT_ERR+1 чеков
            и все получат ошибочный статус
        */
        $this->storage
            ->expects($this->exactly(1))
            ->method('getCollection')
            ->willReturn($receipts);

        $this->storage
            ->expects($this->exactly(PipelineBase::MAX_COUNT_ERR))
            ->method('save');

        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 500]
            ]));

        $transfer = new Transfer(
            $this->client,
            $this->converter,
            TEST_ACTOR_ID,
            TEST_ACTOR_TOKEN,
            TEST_CASHBOX_WITHOUT_AGENT,
            $this->logger
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $this->assertTrue($pipeline->updateAccepted());

        for ($i = 0; $i < PipelineBase::MAX_COUNT_ERR; ++$i) {
            $this->assertSame(ReceiptStatus::ASSUME, $receipts[$i]->getStatus()->getCode());
        }

        $this->assertSame(ReceiptStatus::PREPARED, $receipts[PipelineBase::MAX_COUNT_ERR]->getStatus()->getCode());
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateUnaccepted
     */
    public function testUpdateUnacceptedLock()
    {
        $transfer = new Transfer(
            $this->client,
            $this->converter,
            TEST_ACTOR_ID,
            TEST_ACTOR_TOKEN,
            TEST_CASHBOX_WITHOUT_AGENT,
            $this->logger
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $fp = fopen(PipelineBase::LOCK_FILE_UNACCEPTED, "w+");
        flock($fp, LOCK_EX);
        $this->assertFalse($pipeline->updateUnaccepted());
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateUnaccepted
     * @covers Innokassa\MDK\Services\PipelineBase::processingUnaccepted
     * @covers Innokassa\MDK\Services\PipelineBase::canContinue
     * @covers Innokassa\MDK\Services\PipelineBase::runCycle
     */
    public function testUpdateUnacceptedSuccess200()
    {
        $receipts = new ReceiptCollection();
        for ($i = 0; $i < PipelineBase::COUNT_SELECT; ++$i) {
            $receipts[] = (new Receipt())->setId($i + 1);
        }

        /*
            ожидание 3 вызова т.к. в БД PipelineBase::COUNT_SELECT чеков, для одной итерации одного статуса хватит,
            но будет взведена вторая, в которой будет пустая коллекция,
            а на второй статус будет получена пустая коллекция и цикл будет прерван
        */
        $this->storage
            ->expects($this->exactly(3))
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
            $this->converter,
            TEST_ACTOR_ID,
            TEST_ACTOR_TOKEN,
            TEST_CASHBOX_WITHOUT_AGENT,
            $this->logger
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $this->assertTrue($pipeline->updateUnaccepted());

        for ($i = 0; $i < PipelineBase::COUNT_SELECT; ++$i) {
            $this->assertSame(ReceiptStatus::COMPLETED, $receipts[$i]->getStatus()->getCode());
        }
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateUnaccepted
     * @covers Innokassa\MDK\Services\PipelineBase::processingUnaccepted
     * @covers Innokassa\MDK\Services\PipelineBase::canContinue
     * @covers Innokassa\MDK\Services\PipelineBase::runCycle
     */
    public function testUpdateUnacceptedSuccess409()
    {
        $receipts = new ReceiptCollection();
        $receipts[] = (new Receipt())->setId(1);
        $receipts[] = (new Receipt())->setId(2);

        // ожидание двух вызовов т.к. запросы из БД по двум статусам
        $this->storage
            ->expects($this->exactly(2))
            ->method('getCollection')
            ->will($this->onConsecutiveCalls($receipts, new ReceiptCollection()));

        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, '{}'],
                [NetClientInterface::CODE, 409]
            ]));

        $transfer = new Transfer(
            $this->client,
            $this->converter,
            TEST_ACTOR_ID,
            TEST_ACTOR_TOKEN,
            TEST_CASHBOX_WITHOUT_AGENT,
            $this->logger
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $this->assertTrue($pipeline->updateUnaccepted());

        $this->assertSame(ReceiptStatus::ERROR, $receipts[0]->getStatus()->getCode());
        $this->assertSame(ReceiptStatus::ERROR, $receipts[1]->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateUnaccepted
     * @covers Innokassa\MDK\Services\PipelineBase::processingUnaccepted
     * @covers Innokassa\MDK\Services\PipelineBase::canContinue
     * @covers Innokassa\MDK\Services\PipelineBase::runCycle
     */
    public function testUpdateUnacceptedError()
    {
        $receipts = new ReceiptCollection();
        for ($i = 0; $i < PipelineBase::MAX_COUNT_ERR + 1; ++$i) {
            $receipts[] = (new Receipt())->setId($i + 1);
        }

        /*
            ожидание одного вызова потому что в БД только PipelineBase::MAX_COUNT_ERR+1 чеков
            и все получат ошибочный статус
        */
        $this->storage
            ->expects($this->exactly(1))
            ->method('getCollection')
            ->willReturn($receipts);

        $this->storage
            ->expects($this->exactly(PipelineBase::MAX_COUNT_ERR))
            ->method('save');

        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 500]
            ]));

        $transfer = new Transfer(
            $this->client,
            $this->converter,
            TEST_ACTOR_ID,
            TEST_ACTOR_TOKEN,
            TEST_CASHBOX_WITHOUT_AGENT,
            $this->logger
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $this->assertTrue($pipeline->updateUnaccepted());

        for ($i = 0; $i < PipelineBase::MAX_COUNT_ERR; ++$i) {
            $this->assertSame(ReceiptStatus::ASSUME, $receipts[$i]->getStatus()->getCode());
        }

        $this->assertSame(ReceiptStatus::PREPARED, $receipts[PipelineBase::MAX_COUNT_ERR]->getStatus()->getCode());
    }
}
