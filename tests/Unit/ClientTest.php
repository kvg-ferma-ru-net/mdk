<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Client;
use Innokassa\MDK\Net\Transfer;
use Innokassa\MDK\Net\ConverterApi;
use Innokassa\MDK\Net\NetClientCurl;
use Innokassa\MDK\Services\ManualBase;
use Innokassa\MDK\Services\PrinterBase;
use Innokassa\MDK\Services\PipelineBase;
use Innokassa\MDK\Services\AutomaticBase;
use Innokassa\MDK\Services\ConnectorBase;
use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Entities\ReceiptAdapterInterface;
use Innokassa\MDK\Logger\LoggerInterface;
use Innokassa\MDK\Logger\LoggerFile;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Client
 * @uses Innokassa\MDK\Net\NetClientCurl
 * @uses Innokassa\MDK\Net\Transfer
 * @uses Innokassa\MDK\Services\AutomaticBase
 * @uses Innokassa\MDK\Services\ConnectorBase
 * @uses Innokassa\MDK\Services\ManualBase
 * @uses Innokassa\MDK\Services\PrinterBase
 * @uses Innokassa\MDK\Services\PipelineBase
 * @uses Innokassa\MDK\Logger\LoggerFile
 */
class ClientTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Client::__construct
     * @covers Innokassa\MDK\Client::serviceAutomatic
     * @covers Innokassa\MDK\Client::serviceManual
     * @covers Innokassa\MDK\Client::servicePipeline
     * @covers Innokassa\MDK\Client::servicePrinter
     * @covers Innokassa\MDK\Client::serviceConnector
     *
     * @covers Innokassa\MDK\Client::componentSettings
     * @covers Innokassa\MDK\Client::componentAdapter
     * @covers Innokassa\MDK\Client::componentStorage
     * @covers Innokassa\MDK\Client::componentLogger
     */
    public function test()
    {
        $settings = $this->createMock(SettingsInterface::class);
        $storage = $this->createMock(ReceiptStorageInterface::class);
        $adapter = $this->createMock(ReceiptAdapterInterface::class);

        $logger = new LoggerFile();

        $transfer = new Transfer(new NetClientCurl(), new ConverterApi(), '0', '0', '0', $logger);

        $automatic = new AutomaticBase($settings, $storage, $transfer, $adapter);
        $manual = new ManualBase($storage, $transfer, $settings);
        $pipeline = new PipelineBase($storage, $transfer);
        $connector = new ConnectorBase($transfer);
        $printer = new PrinterBase($storage, $transfer);

        $client = new Client(
            $settings,
            $adapter,
            $storage,
            $automatic,
            $manual,
            $pipeline,
            $printer,
            $connector,
            $logger
        );

        $this->assertSame($automatic, $client->serviceAutomatic());
        $this->assertSame($manual, $client->serviceManual());
        $this->assertSame($pipeline, $client->servicePipeline());
        $this->assertSame($printer, $client->servicePrinter());
        $this->assertSame($connector, $client->serviceConnector());

        $this->assertSame($settings, $client->componentSettings());
        $this->assertSame($adapter, $client->componentAdapter());
        $this->assertSame($storage, $client->componentStorage());
        $this->assertSame($logger, $client->componentLogger());
    }
}
