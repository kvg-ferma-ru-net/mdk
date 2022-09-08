<?php

use Innokassa\MDK\Net\Transfer;
use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Net\NetClientInterface;
use Innokassa\MDK\Services\ConnectorBase;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Entities\ConverterAbstract;
use Innokassa\MDK\Settings\SettingsAbstract;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\SettingsException;
use Innokassa\MDK\Exceptions\NetConnectException;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Exceptions\Services\PrinterException;

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
 * @uses Innokassa\MDK\Exceptions\BaseException
 */
class ConnectorBaseFakeTest extends TestCase
{
    private $client;
    private $converter;
    private $settings;

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
    }

    /**
     * @covers Innokassa\MDK\Services\ConnectorBase::__construct
     * @covers Innokassa\MDK\Services\ConnectorBase::testSettings
     */
    public function testSettingsSuccess()
    {
        $cashbox = '{"type": "online_store", "taxation": 1, "billing_place_list": ["https://example.com/"]}';
        $this->client->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, $cashbox],
                [NetClientInterface::CODE, 200]
            ]));

        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $connector = new ConnectorBase($transfer);
        $this->assertTrue($connector->testSettings($this->settings));
    }

    /**
     * @covers Innokassa\MDK\Services\ConnectorBase::__construct
     * @covers Innokassa\MDK\Services\ConnectorBase::testSettings
     */
    public function testSettingsFailServer500()
    {
        $this->client->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::CODE, 500],
                [NetClientInterface::BODY, ''],
            ]));

        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $connector = new ConnectorBase($transfer);
        $this->expectException(SettingsException::class);
        $this->expectExceptionMessage(
            sprintf('Сервер временно недоступен (%d), попробуйте позже', 500)
        );
        $connector->testSettings($this->settings);
    }

    /**
     * @covers Innokassa\MDK\Services\ConnectorBase::__construct
     * @covers Innokassa\MDK\Services\ConnectorBase::testSettings
     */
    public function testSettingsFailServer28()
    {
        $this->client
            ->method('send')
            ->will($this->throwException(new NetConnectException('', 28)));

        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $connector = new ConnectorBase($transfer);
        $this->expectException(SettingsException::class);
        $this->expectExceptionMessage(
            sprintf('Сервер временно недоступен (%d), попробуйте позже', 28)
        );
        $connector->testSettings($this->settings);
    }

    /**
     * @covers Innokassa\MDK\Services\ConnectorBase::__construct
     * @covers Innokassa\MDK\Services\ConnectorBase::testSettings
     */
    public function testSettingsFailServer401402403404()
    {
        $this->client->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::CODE, 401],
                [NetClientInterface::BODY, ''],
            ]));

        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $connector = new ConnectorBase($transfer);
        $this->expectException(SettingsException::class);
        $this->expectExceptionMessage(
            sprintf('Неверные авторизационные данные (%d)', 401)
        );
        $connector->testSettings($this->settings);
    }

    /**
     * @covers Innokassa\MDK\Services\ConnectorBase::__construct
     * @covers Innokassa\MDK\Services\ConnectorBase::testSettings
     */
    public function testSettingsFailAuth()
    {
        $this->client->method('send')
            ->will($this->throwException(new NetConnectException()));

        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $connector = new ConnectorBase($transfer);
        $this->expectException(SettingsException::class);
        $connector->testSettings($this->settings);
    }

    /**
     * @covers Innokassa\MDK\Services\ConnectorBase::__construct
     * @covers Innokassa\MDK\Services\ConnectorBase::testSettings
     */
    public function testSettingsFailTaxation()
    {
        $cashbox = '{"type": "online_store", "taxation": 32, "billing_place_list": ["https://example.com/"]}';
        $this->client->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, $cashbox],
                [NetClientInterface::CODE, 200]
            ]));

        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $connector = new ConnectorBase($transfer);
        $this->expectException(SettingsException::class);
        $connector->testSettings($this->settings);
    }

    /**
     * @covers Innokassa\MDK\Services\ConnectorBase::__construct
     * @covers Innokassa\MDK\Services\ConnectorBase::testSettings
     */
    public function testSettingsFailLocation()
    {
        $cashbox = '{"type": "online_store", "taxation": 1, "billing_place_list": ["http://example.com"]}';
        $this->client->method('read')
            ->will($this->returnValueMap([
                [NetClientInterface::BODY, $cashbox],
                [NetClientInterface::CODE, 200]
            ]));

        $transfer = new Transfer(
            $this->client,
            $this->converter
        );
        $connector = new ConnectorBase($transfer);
        $this->expectException(SettingsException::class);
        $connector->testSettings($this->settings);
    }
}
