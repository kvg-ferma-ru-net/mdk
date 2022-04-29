<?php

use Innokassa\MDK\Net\Transfer;
use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Settings\SettingsConn;
use Innokassa\MDK\Logger\LoggerInterface;
use Innokassa\MDK\Net\NetClientInterface;
use Innokassa\MDK\Settings\SettingsAbstract;
use Innokassa\MDK\Entities\ConverterAbstract;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Exceptions\ConverterException;
use Innokassa\MDK\Exceptions\NetConnectException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Net\Transfer
 * @uses Innokassa\MDK\Exceptions\TransferException
 * @uses Innokassa\MDK\Entities\Receipt
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptSubType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptStatus
 * @uses Innokassa\MDK\Entities\AtomAbstract
 * @uses Innokassa\MDK\Exceptions\BaseException
 * @uses Innokassa\MDK\Settings\SettingsConn
 */
class TransferFakeTest extends TestCase
{
    /** @var NetClientInterface */
    private $client;

    /** @var ConverterAbstract */
    private $converter;

    /** @var LoggerInterface */
    private $logger;

    /** @var SettingsAbstract */
    private $settings;

    //######################################################################

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
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->settings = $this->createMock(SettingsAbstract::class);
        $this->settings->method('getActorId')
            ->willReturn(TEST_ACTOR_ID);
        $this->settings->method('getActorToken')
            ->willReturn(TEST_ACTOR_TOKEN);
        $this->settings->method('getCashbox')
            ->willReturn(TEST_CASHBOX_WITHOUT_AGENT);
        $this->settings->method('extrudeConn')
            ->willReturn(new SettingsConn(TEST_ACTOR_ID, TEST_ACTOR_TOKEN, TEST_CASHBOX_WITHOUT_AGENT));
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Net\Transfer::__construct
     * @covers Innokassa\MDK\Net\Transfer::getCashbox
     */
    public function testGetCashbox()
    {
        $cashbox = '{"type": "online_store", "taxation": 1, "billing_place_list": ["https://example.com/"]}';
        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, $cashbox],
                [NetClientInterface::CODE, 200]
            ]));

        $transfer = new Transfer($this->client, $this->converter, $this->logger);
        $response = $transfer->getCashbox($this->settings->extrudeConn());
        $this->assertIsObject($response);
        $this->assertEquals(json_decode($cashbox), $response);
    }

    /**
     * @covers Innokassa\MDK\Net\Transfer::__construct
     * @covers Innokassa\MDK\Net\Transfer::getCashbox
     */
    public function testGetCashboxFailConnect()
    {
        $this->client
            ->method('send')
            ->will($this->throwException(new NetConnectException()));

        $transfer = new Transfer($this->client, $this->converter, $this->logger);

        $this->expectException(TransferException::class);
        $transfer->getCashbox($this->settings->extrudeConn());
    }

    /**
     * @covers Innokassa\MDK\Net\Transfer::__construct
     * @covers Innokassa\MDK\Net\Transfer::getCashbox
     */
    public function testGetCashboxFailApi()
    {
        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 401]
            ]));

        $transfer = new Transfer($this->client, $this->converter, $this->logger);

        $this->expectException(TransferException::class);
        $this->expectExceptionCode(401);
        $transfer->getCashbox($this->settings->extrudeConn());
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Net\Transfer::__construct
     * @covers Innokassa\MDK\Net\Transfer::getReceipt
     */
    public function testGetReceipt()
    {
        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 202]
            ]));

        $transfer = new Transfer($this->client, $this->converter, $this->logger);
        $receipt = new Receipt();
        $this->assertEquals($receipt, $transfer->getReceipt($this->settings->extrudeConn(), $receipt));
        $this->assertEquals(ReceiptStatus::WAIT, $receipt->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Net\Transfer::__construct
     * @covers Innokassa\MDK\Net\Transfer::getReceipt
     */
    public function testGetReceiptFailConnect()
    {
        $this->client
            ->method('send')
            ->will($this->throwException(new NetConnectException('error connect', 18)));

        $transfer = new Transfer($this->client, $this->converter, $this->logger);
        $receipt = new Receipt();

        try {
            $transfer->getReceipt($this->settings->extrudeConn(), $receipt);
        } catch (TransferException $e) {
            $this->assertEquals(18, $e->getCode());
            $this->assertEquals(ReceiptStatus::PREPARED, $receipt->getStatus()->getCode());
        }
    }

    /**
     * @covers Innokassa\MDK\Net\Transfer::__construct
     * @covers Innokassa\MDK\Net\Transfer::getReceipt
     */
    public function testGetReceiptFailApi()
    {
        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 401]
            ]));

        $transfer = new Transfer($this->client, $this->converter, $this->logger);
        $receipt = new Receipt();

        try {
            $transfer->getReceipt($this->settings->extrudeConn(), $receipt);
        } catch (TransferException $e) {
            $this->assertEquals(401, $e->getCode());
            $this->assertEquals(ReceiptStatus::REPEAT, $receipt->getStatus()->getCode());
        }
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Net\Transfer::__construct
     * @covers Innokassa\MDK\Net\Transfer::sendReceipt
     */
    public function testSendReceipt()
    {
        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 202]
            ]));

        $this->converter
            ->method('receiptToArray')
            ->willReturn([]);

        $transfer = new Transfer($this->client, $this->converter, $this->logger);
        $receipt = new Receipt();

        $this->assertEquals($receipt, $transfer->sendReceipt($this->settings->extrudeConn(), $receipt));
        $this->assertEquals(ReceiptStatus::WAIT, $receipt->getStatus()->getCode());
    }

    /**
     * @covers Innokassa\MDK\Net\Transfer::__construct
     * @covers Innokassa\MDK\Net\Transfer::sendReceipt
     */
    public function testSendReceiptFailConnect()
    {
        $this->client
            ->method('send')
            ->will($this->throwException(new NetConnectException('connect error', 18)));

        $this->converter
            ->method('receiptToArray')
            ->willReturn([]);

        $transfer = new Transfer($this->client, $this->converter, $this->logger);
        $receipt = new Receipt();

        try {
            $transfer->sendReceipt($this->settings->extrudeConn(), $receipt);
        } catch (TransferException $e) {
            $this->assertEquals(18, $e->getCode());
            $this->assertEquals(ReceiptStatus::PREPARED, $receipt->getStatus()->getCode());
        }
    }

    /**
     * @covers Innokassa\MDK\Net\Transfer::__construct
     * @covers Innokassa\MDK\Net\Transfer::sendReceipt
     */
    public function testSendReceiptFailApi()
    {
        $this->client
            ->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, ''],
                [NetClientInterface::CODE, 401]
            ]));

        $this->converter
            ->method('receiptToArray')
            ->willReturn([]);

        $transfer = new Transfer($this->client, $this->converter, $this->logger);
        $receipt = new Receipt();

        try {
            $transfer->sendReceipt($this->settings->extrudeConn(), $receipt);
        } catch (TransferException $e) {
            $this->assertEquals(401, $e->getCode());
            $this->assertEquals(ReceiptStatus::REPEAT, $receipt->getStatus()->getCode());
        }
    }

    /**
     * @covers Innokassa\MDK\Net\Transfer::__construct
     * @covers Innokassa\MDK\Net\Transfer::sendReceipt
     */
    public function testSendReceiptFailConverter()
    {
        $this->converter
            ->method('receiptToArray')
            ->will($this->throwException(new ConverterException()));

        $transfer = new Transfer($this->client, $this->converter, $this->logger);
        $receipt = new Receipt();

        try {
            $transfer->sendReceipt($this->settings->extrudeConn(), $receipt);
        } catch (TransferException $e) {
            $this->assertEquals(ReceiptStatus::ERROR, $e->getCode());
            $this->assertEquals(ReceiptStatus::ERROR, $receipt->getStatus()->getCode());
        }
    }
}
