<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Net\Transfer;
use Innokassa\MDK\Net\NetClientCurl;
use Innokassa\MDK\Net\NetClientInterface;
use Innokassa\MDK\Entities\UUID;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\Atoms\PaymentMethod;
use Innokassa\MDK\Exceptions\NetConnectException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Net\NetClientCurl
 * @uses Innokassa\MDK\Exceptions\NetConnectException
 * @uses Innokassa\MDK\Entities\UUID
 */
class NetClientCurlTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Net\NetClientCurl::__construct
     * @covers Innokassa\MDK\Net\NetClientCurl::__destruct
     * @covers Innokassa\MDK\Net\NetClientCurl::write
     * @covers Innokassa\MDK\Net\NetClientCurl::read
     * @covers Innokassa\MDK\Net\NetClientCurl::reset
     */
    public function testSendSuccessGet()
    {
        $client = new NetClientCurl();
        $this->assertSame($client, $client->write(NetClientInterface::PATH, 'https://api.kassavoblake.com/'));
        $this->assertSame($client, $client->send());

        $this->assertSame(200, $client->read(NetClientInterface::CODE));
        $this->assertIsString($client->read(NetClientInterface::BODY));

        $this->assertSame('', $client->reset()->read(NetClientInterface::BODY));

        $client = new NetClientCurl();
    }

    /**
     * @covers Innokassa\MDK\Net\NetClientCurl::__construct
     * @covers Innokassa\MDK\Net\NetClientCurl::send
     * @covers Innokassa\MDK\Net\NetClientCurl::write
     * @covers Innokassa\MDK\Net\NetClientCurl::read
     */
    public function testSendSuccessPost()
    {
        $a = [
            'type' => ReceiptType::COMING,
            'items' => [[
                'type' => 1,
                'name' => 'name',
                'price' => 100.0,
                'quantity' => 1.0,
                'amount' => 100.0,
                'payment_method' => PaymentMethod::PAYMENT_FULL,
                'vat' => 1
            ]],
            'taxation' => Taxation::ORN,
            'amount' => [
                'cashless' => 100.0
            ],
            'notify' => [
                [
                    'type' => 'email',
                    'value' => 'box@domain.zone'
                ]
            ],
            'customer' => [
                'name' => 'Тест Тест Тест',
                'tin' => '0000000000'
            ],
            'loc' => [
                'billing_place' => 'https://example.com/'
            ]
        ];

        $client = new NetClientCurl();
        $client
            ->write(
                NetClientInterface::PATH,
                Transfer::API_URL . "/c_groups/" . TEST_CASHBOX_WITHOUT_AGENT . "/receipts/online_store/" . (new UUID())->get()
            )
            ->write(NetClientInterface::TYPE, 'POST')
            ->write(NetClientInterface::BODY, json_encode($a))
            ->write(NetClientInterface::HEAD, [
                "Authorization: Basic " . base64_encode(TEST_ACTOR_ID . ":" . TEST_ACTOR_TOKEN),
                "Content-type: application/json; charset=utf-8"
            ])
            ->write(-1, '')
            ->send();

        $this->assertTrue(
            $client->read(NetClientInterface::CODE) > 200 && $client->read(NetClientInterface::CODE) < 600
        );
        $this->assertIsString($client->read(NetClientInterface::HEAD));
        $this->assertNull($client->read(-1));
    }

    /**
     * @covers Innokassa\MDK\Net\NetClientCurl::__construct
     * @covers Innokassa\MDK\Net\NetClientCurl::send
     */
    public function testSendFailServer1()
    {
        $client = new NetClientCurl();
        $client->write(NetClientInterface::PATH, 'https://api0.innokassa.ru/0');

        $this->expectException(NetConnectException::class);
        $client->send();
    }
}
