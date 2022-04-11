<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Net\Transfer;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Services\PrinterBase;
use Innokassa\MDK\Net\NetClientInterface;
use Innokassa\MDK\Services\ConnectorBase;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Entities\ConverterAbstract;
use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\SettingsException;
use Innokassa\MDK\Exceptions\NetConnectException;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Exceptions\Services\PrinterException;
use Innokassa\MDK\Logger\LoggerInterface;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Services\ConnectorBase
 * @uses Innokassa\MDK\Net\Transfer
 * @uses Innokassa\MDK\Exceptions\TransferException
 * @uses Innokassa\MDK\Entities\AtomAbstract
 * @uses Innokassa\MDK\Entities\Atoms\Taxation
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptStatus
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptSubType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptType
 * @uses Innokassa\MDK\Entities\Receipt
 * @uses Innokassa\MDK\Entities\UUID
 */
class PrinterBaseTest extends TestCase
{
    private $client;
    private $converter;
    private $storage;
    private $logger;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(ReceiptStorageInterface::class);

        $this->client = $this->createMock(NetClientInterface::class);
        $this->client->method('send')
            ->will($this->returnSelf());
        $this->client->method('write')
            ->will($this->returnSelf());
        $this->client->method('reset')
            ->will($this->returnSelf());

        $this->converter = $this->createMock(ConverterAbstract::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Services\PrinterBase::__construct
     * @covers Innokassa\MDK\Services\PrinterBase::getLinkVerify
     */
    public function testGetLinkVerifySuccess()
    {
        $receipt = new Receipt();
        $receipt->setStatus(new ReceiptStatus(ReceiptStatus::COMPLETED));
        $this->storage->method('getOne')
            ->willReturn($receipt);

        $transfer = new Transfer($this->client, $this->converter, '0', '0', '0', $this->logger);
        $printer = new PrinterBase($this->storage, $transfer);
        $this->assertIsString($printer->getLinkVerify(1));
    }

    /**
     * @covers Innokassa\MDK\Services\PrinterBase::__construct
     * @covers Innokassa\MDK\Services\PrinterBase::getLinkVerify
     */
    public function testGetLinkVerifyFailNotFound()
    {
        $this->storage->method('getOne')
            ->willReturn(null);

        $transfer = new Transfer($this->client, $this->converter, '0', '0', '0', $this->logger);
        $printer = new PrinterBase($this->storage, $transfer);
        $this->expectException(PrinterException::class);
        $printer->getLinkVerify(1);
    }

    /**
     * @covers Innokassa\MDK\Services\PrinterBase::__construct
     * @covers Innokassa\MDK\Services\PrinterBase::getLinkVerify
     */
    public function testGetLinkVerifyFailNotCompleted()
    {
        $receipt = new Receipt();
        $receipt->setStatus(new ReceiptStatus(ReceiptStatus::WAIT));
        $this->storage->method('getOne')
            ->willReturn($receipt);

        $transfer = new Transfer($this->client, $this->converter, '0', '0', '0', $this->logger);
        $printer = new PrinterBase($this->storage, $transfer);
        $this->expectException(PrinterException::class);
        $printer->getLinkVerify(1);
    }

    //**********************************************************************

    /**
     * @covers Innokassa\MDK\Services\PrinterBase::__construct
     * @covers Innokassa\MDK\Services\PrinterBase::getLinkRaw
     */
    public function testGetLinkRawSuccess()
    {
        $receipt = new Receipt();
        $receipt->setStatus(new ReceiptStatus(ReceiptStatus::COMPLETED));
        $this->storage->method('getOne')
            ->willReturn($receipt);

        $transfer = new Transfer($this->client, $this->converter, '0', '0', '0', $this->logger);
        $printer = new PrinterBase($this->storage, $transfer);
        $this->assertIsString($printer->getLinkRaw($receipt));
    }
}
