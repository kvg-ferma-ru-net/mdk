<?php

use Innokassa\MDK\Client;

use Innokassa\MDK\Net\Transfer;
use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Services\AutomaticBase;
use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Entities\ReceiptAdapterInterface;
use Innokassa\MDK\Net\ConverterV2;
use Innokassa\MDK\Net\NetClientCurl;
use Innokassa\MDK\Services\ConnectorBase;
use Innokassa\MDK\Services\ManualBase;
use Innokassa\MDK\Services\PipelineBase;

/**
 * @uses Innokassa\MDK\Client
 * @uses Innokassa\MDK\Net\NetClientCurl
 * @uses Innokassa\MDK\Net\Transfer
 * @uses Innokassa\MDK\Services\AutomaticBase
 * @uses Innokassa\MDK\Services\ConnectorBase
 * @uses Innokassa\MDK\Services\ManualBase
 * @uses Innokassa\MDK\Services\PipelineBase
 */
class ClientTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Client::__construct
     * @covers Innokassa\MDK\Client::serviceAutomatic
     * @covers Innokassa\MDK\Client::serviceManual
     * @covers Innokassa\MDK\Client::servicePipeline
     * @covers Innokassa\MDK\Client::serviceConnector
     * 
     * @covers Innokassa\MDK\Client::componentSettings
     * @covers Innokassa\MDK\Client::componentAdapter
     * @covers Innokassa\MDK\Client::componentStorage
     */
    public function test()
    {
        $settings = $this->createMock(SettingsInterface::class);
        $storage = $this->createMock(ReceiptStorageInterface::class);
        $adapter = $this->createMock(ReceiptAdapterInterface::class);

        $transfer = new Transfer(new NetClientCurl(), new ConverterV2(), '0', '0', '0');

        $automatic = new AutomaticBase($settings, $storage, $transfer, $adapter);
        $manual = new ManualBase($storage, $transfer, $settings);
        $pipeline = new PipelineBase($storage, $transfer);
        $connector = new ConnectorBase($storage, $transfer);
        
        $client = new Client(
            $settings, $adapter, $storage,
            $automatic, $manual, $pipeline, $connector
        );

        $this->assertSame($automatic, $client->serviceAutomatic());
        $this->assertSame($manual, $client->serviceManual());
        $this->assertSame($pipeline, $client->servicePipeline());
        $this->assertSame($connector, $client->serviceConnector());

        $this->assertSame($settings, $client->componentSettings());
        $this->assertSame($adapter, $client->componentAdapter());
        $this->assertSame($storage, $client->componentStorage());
    }
};
