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

/**
 * @uses Innokassa\MDK\Services\PipelineBase
 * @uses Innokassa\MDK\Net\Transfer
 * @uses Innokassa\MDK\Exceptions\TransferException
 * @uses Innokassa\MDK\Entities\AtomAbstract
 * @uses Innokassa\MDK\Entities\Atoms\Taxation
 * 
 * @uses Innokassa\MDK\Collections\BaseCollection
 * @uses Innokassa\MDK\Collections\ReceiptCollection
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptStatus
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptSubType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptType
 * @uses Innokassa\MDK\Entities\Receipt
 * @uses Innokassa\MDK\Entities\UUID
 * @uses Innokassa\MDK\Storage\ReceiptFilter
 */
class PipelineBaseFakeTest extends TestCase
{
    private $client;
    private $converter;
    private $storage;

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
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateAccepted
     */
    public function testUpdateAcceptedSuccess()
    {
        $receipts = new ReceiptCollection();
        $receipts[] = new Receipt();
        $receipts[] = new Receipt();
        $this->storage->method('getCollection')
            ->willReturn($receipts);

        $this->client->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, '{}'],
                [NetClientInterface::CODE, 200]
            ]));

        $transfer = new Transfer(
            $this->client, 
            $this->converter, 
            TEST_ACTOR_ID, 
            TEST_ACTOR_TOKEN, 
            TEST_CASHBOX_WITHOUT_AGENT
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $pipeline->updateAccepted();

        $this->assertSame(ReceiptStatus::COMPLETED, $receipts[0]->getStatus()->getCode());
        $this->assertSame(ReceiptStatus::COMPLETED, $receipts[1]->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateAccepted
     */
    public function testUpdateAcceptedServerError()
    {
        $receipts = new ReceiptCollection();
        $receipts[] = new Receipt();
        $receipts[] = new Receipt();
        $this->storage->method('getCollection')
            ->willReturn($receipts);

        $this->client->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 500]
            ]));

        $transfer = new Transfer(
            $this->client, 
            $this->converter, 
            TEST_ACTOR_ID, 
            TEST_ACTOR_TOKEN, 
            TEST_CASHBOX_WITHOUT_AGENT
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $pipeline->updateAccepted();

        $this->assertSame(ReceiptStatus::ASSUME, $receipts[0]->getStatus()->getCode());
        $this->assertSame(ReceiptStatus::PREPARED, $receipts[1]->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateAccepted
     */
    public function testUpdateAcceptedAuthError()
    {
        $receipts = new ReceiptCollection();
        $receipts[] = new Receipt();
        $receipts[] = new Receipt();
        $this->storage->method('getCollection')
            ->willReturn($receipts);

        $this->client->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 401]
            ]));

        $transfer = new Transfer(
            $this->client, 
            $this->converter, 
            TEST_ACTOR_ID, 
            TEST_ACTOR_TOKEN, 
            TEST_CASHBOX_WITHOUT_AGENT
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $pipeline->updateAccepted();

        $this->assertSame(ReceiptStatus::REPEAT, $receipts[0]->getStatus()->getCode());
        $this->assertSame(ReceiptStatus::PREPARED, $receipts[1]->getStatus()->getCode());
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateUnaccepted
     */
    public function testUpdateUnacceptedSuccess()
    {
        $receipts = new ReceiptCollection();
        $receipts[] = new Receipt();
        $receipts[] = new Receipt();
        $this->storage->method('getCollection')
            ->willReturn($receipts);

        $this->client->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, '{}'],
                [NetClientInterface::CODE, 200]
            ]));

        $transfer = new Transfer(
            $this->client, 
            $this->converter, 
            TEST_ACTOR_ID, 
            TEST_ACTOR_TOKEN, 
            TEST_CASHBOX_WITHOUT_AGENT
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $pipeline->updateUnaccepted();

        $this->assertSame(ReceiptStatus::COMPLETED, $receipts[0]->getStatus()->getCode());
        $this->assertSame(ReceiptStatus::COMPLETED, $receipts[1]->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateUnaccepted
     */
    public function testUpdateUnacceptedSuccess409()
    {
        $receipts = new ReceiptCollection();
        $receipts[] = new Receipt();
        $receipts[] = new Receipt();
        $this->storage->method('getCollection')
            ->willReturn($receipts);

        $this->client->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, '{}'],
                [NetClientInterface::CODE, 409]
            ]));

        /*$transfer = $this->createMock(TransferInterface::class);
        $transfer->method('getReceipt')
            ->will($this->returnValueMap([
                [$receipts[0], $receipts[0]->setStatus(new ReceiptStatus(ReceiptStatus::COMPLETED))],
                [$receipts[1], $receipts[1]->setStatus(new ReceiptStatus(ReceiptStatus::COMPLETED))]
            ]));
        $transfer->method('sendReceipt')
            ->will($this->throwException(new TransferException('', 409)));*/

        $transfer = new Transfer(
            $this->client, 
            $this->converter, 
            TEST_ACTOR_ID, 
            TEST_ACTOR_TOKEN, 
            TEST_CASHBOX_WITHOUT_AGENT
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $pipeline->updateUnaccepted();

        $this->assertSame(ReceiptStatus::ERROR, $receipts[0]->getStatus()->getCode());
        $this->assertSame(ReceiptStatus::ERROR, $receipts[1]->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateUnaccepted
     */
    public function testUpdateUnacceptedServerError()
    {
        $receipts = new ReceiptCollection();
        $receipts[] = new Receipt();
        $receipts[] = new Receipt();
        $this->storage->method('getCollection')
            ->willReturn($receipts);

        $this->client->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 500]
            ]));

        $transfer = new Transfer(
            $this->client, 
            $this->converter, 
            TEST_ACTOR_ID, 
            TEST_ACTOR_TOKEN, 
            TEST_CASHBOX_WITHOUT_AGENT
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $pipeline->updateUnaccepted();

        $this->assertSame(ReceiptStatus::ASSUME, $receipts[0]->getStatus()->getCode());
        $this->assertSame(ReceiptStatus::PREPARED, $receipts[1]->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Services\PipelineBase::__construct
     * @covers Innokassa\MDK\Services\PipelineBase::updateUnaccepted
     */
    public function testUpdateUnacceptedAuthError()
    {
        $receipts = new ReceiptCollection();
        $receipts[] = new Receipt();
        $receipts[] = new Receipt();
        $this->storage->method('getCollection')
            ->willReturn($receipts);

        $this->client->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 401]
            ]));

        $transfer = new Transfer(
            $this->client, 
            $this->converter, 
            TEST_ACTOR_ID, 
            TEST_ACTOR_TOKEN, 
            TEST_CASHBOX_WITHOUT_AGENT
        );
        $pipeline = new PipelineBase($this->storage, $transfer);
        $pipeline->updateUnaccepted();

        $this->assertSame(ReceiptStatus::REPEAT, $receipts[0]->getStatus()->getCode());
        $this->assertSame(ReceiptStatus::PREPARED, $receipts[1]->getStatus()->getCode());
    }
};
