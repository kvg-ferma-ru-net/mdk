<?php

use Innokassa\MDK\Client;
use Innokassa\MDK\Net\Transfer;
use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Net\ConverterApi;
use Innokassa\MDK\Logger\LoggerFile;
use Innokassa\MDK\Net\NetClientCurl;
use Innokassa\MDK\Services\PipelineForModule;
use Innokassa\MDK\Logger\LoggerInterface;
use Innokassa\MDK\Services\AutomaticBase;
use Innokassa\MDK\Services\ConnectorBase;
use Innokassa\MDK\Settings\SettingsAbstract;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Entities\ReceiptAdapterInterface;
use Innokassa\MDK\Entities\ReceiptId\ReceiptIdFactoryMeta;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Client
 * @uses Innokassa\MDK\Net\NetClientCurl
 * @uses Innokassa\MDK\Net\Transfer
 * @uses Innokassa\MDK\Services\AutomaticBase
 * @uses Innokassa\MDK\Services\ConnectorBase
 * @uses Innokassa\MDK\Services\PipelineForModule
 * @uses Innokassa\MDK\Logger\LoggerFile
 */
class ClientTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Client::__construct
     * @covers Innokassa\MDK\Client::serviceAutomatic
     * @covers Innokassa\MDK\Client::servicePipeline
     * @covers Innokassa\MDK\Client::serviceConnector
     *
     * @covers Innokassa\MDK\Client::componentSettings
     * @covers Innokassa\MDK\Client::componentStorage
     * @covers Innokassa\MDK\Client::componentLogger
     */
    public function test()
    {
        $settings = $this->createMock(SettingsAbstract::class);
        $storage = $this->createMock(ReceiptStorageInterface::class);
        $adapter = $this->createMock(ReceiptAdapterInterface::class);

        $logger = new LoggerFile();

        $transfer = new Transfer(new NetClientCurl(), new ConverterApi(), $logger);

        $automatic = new AutomaticBase($settings, $storage, $transfer, $adapter, new ReceiptIdFactoryMeta());
        $pipeline = new PipelineForModule($settings, $storage, $transfer);
        $connector = new ConnectorBase($transfer);

        $client = new Client(
            $settings,
            $storage,
            $automatic,
            $pipeline,
            $connector,
            $logger
        );

        $this->assertSame($automatic, $client->serviceAutomatic());
        $this->assertSame($pipeline, $client->servicePipeline());
        $this->assertSame($connector, $client->serviceConnector());

        $this->assertSame($settings, $client->componentSettings());
        $this->assertSame($storage, $client->componentStorage());
        $this->assertSame($logger, $client->componentLogger());
    }
}
