<?php

use Innokassa\MDK\Net\Transfer;
use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Net\ConverterApi;
use Innokassa\MDK\Net\NetClientCurl;
use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Settings\SettingsConn;
use Innokassa\MDK\Logger\LoggerInterface;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Entities\Primitives\Customer;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Entities\ReceiptId\ReceiptIdFactoryMeta;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Net\Transfer
 * @uses Innokassa\MDK\Entities\Receipt
 * @uses Innokassa\MDK\Net\ConverterApi
 * @uses Innokassa\MDK\Entities\ConverterAbstract
 * @uses Innokassa\MDK\Collections\BaseCollection
 * @uses Innokassa\MDK\Entities\AtomAbstract
 * @uses Innokassa\MDK\Entities\Atoms\PaymentMethod
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptItemType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptStatus
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptSubType
 * @uses Innokassa\MDK\Entities\Primitives\Amount
 * @uses Innokassa\MDK\Entities\Primitives\Notify
 * @uses Innokassa\MDK\Entities\Primitives\Customer
 * @uses Innokassa\MDK\Entities\ReceiptItem
 * @uses Innokassa\MDK\Net\NetClientCurl
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptType
 * @uses Innokassa\MDK\Entities\Atoms\Taxation
 * @uses Innokassa\MDK\Entities\Atoms\Vat
 * @uses Innokassa\MDK\Exceptions\TransferException
 * @uses Innokassa\MDK\Settings\SettingsConn
 */
class TransferServerTest extends TestCase
{
    protected $logger;
    protected $settingsConn;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->settingsConn = new SettingsConn(TEST_ACTOR_ID, TEST_ACTOR_TOKEN, TEST_CASHBOX_WITHOUT_AGENT);
    }

    /**
     * @covers Innokassa\MDK\Net\Transfer::sendReceipt
     */
    public function testSendReceipt()
    {
        $receiptIdFactory = new ReceiptIdFactoryMeta();
        $receipt = new Receipt();
        $receipt
            ->setType(ReceiptType::COMING)
            ->addItem(
                (new ReceiptItem())
                    ->setPrice(100.0)
                    ->setQuantity(2)
                    ->setName('name')
                    ->setItemId('123')
            )
            ->setTaxation(Taxation::USN)
            ->setAmount(new Amount(Amount::CASHLESS, 200.0))
            ->setNotify(new Notify('box@domain.zone'))
            ->setCustomer(new Customer('Test'))
            ->setLocation('https://example.com/')
            ->setOrderId('456');

        $receipt->setReceiptId($receiptIdFactory->build($receipt));

        $client = new NetClientCurl();
        $converter = new ConverterApi();
        $transfer = new Transfer(
            $client,
            $converter,
            $this->logger
        );

        $transfer->sendReceipt($this->settingsConn, $receipt);
        $this->assertTrue(
            $receipt->getStatus()->getCode() == ReceiptStatus::COMPLETED
            || $receipt->getStatus()->getCode() == ReceiptStatus::WAIT
        );

        return $receipt;
    }

    /**
     * @covers Innokassa\MDK\Net\Transfer::getReceipt
     * @depends testSendReceipt
     */
    public function testGetReceiptExists($receipt)
    {
        $client = new NetClientCurl();
        $converter = new ConverterApi();
        $transfer = new Transfer(
            $client,
            $converter,
            $this->logger
        );
        $receipt = $transfer->getReceipt($this->settingsConn, $receipt);
        $this->assertTrue(
            $receipt->getStatus()->getCode() == ReceiptStatus::COMPLETED
            || $receipt->getStatus()->getCode() == ReceiptStatus::WAIT
        );
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Net\Transfer::sendReceipt
     */
    public function testSendReceiptFailReceipt()
    {
        $receiptIdFactory = new ReceiptIdFactoryMeta();
        $receipt = new Receipt();
        $receipt
            ->setType(ReceiptType::COMING)
            ->addItem(
                (new ReceiptItem())
                    ->setPrice(100.0)
                    ->setQuantity(2)
                    ->setName('name')
            )
            ->setTaxation(Taxation::USN)
            ->setAmount(new Amount(Amount::CASHLESS, 300.0))
            ->setNotify(new Notify('box@domain.zone'))
            ->setCustomer(new Customer('Test'))
            ->setLocation('https://example.com/');

        $receipt->setReceiptId($receiptIdFactory->build($receipt));

        $client = new NetClientCurl();
        $converter = new ConverterApi();
        $transfer = new Transfer(
            $client,
            $converter,
            $this->logger
        );

        $this->expectException(TransferException::class);
        $this->expectExceptionCode(400);
        $receipt = $transfer->sendReceipt($this->settingsConn, $receipt);
    }

    /**
     * @covers Innokassa\MDK\Net\Transfer::getReceipt
     */
    public function testGetReceiptFailNewUUID()
    {
        $client = new NetClientCurl();
        $converter = new ConverterApi();
        $transfer = new Transfer(
            $client,
            $converter,
            $this->logger
        );

        $this->expectException(TransferException::class);
        $this->expectExceptionCode(404);
        $receipt = $transfer->getReceipt($this->settingsConn, new Receipt());
    }

    /**
     * @covers Innokassa\MDK\Net\Transfer::getReceipt
     */
    public function testGetReceiptFailAuth()
    {
        $settingsConn = new SettingsConn(0, 0, 0);
        $client = new NetClientCurl();
        $converter = new ConverterApi();
        $transfer = new Transfer($client, $converter, $this->logger);

        $this->expectException(TransferException::class);
        $this->expectExceptionCode(401);
        $receipt = $transfer->getReceipt($settingsConn, new Receipt());
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Net\Transfer::getCashBox
     */
    public function testGetCashBox()
    {
        $client = new NetClientCurl();
        $converter = new ConverterApi();
        $transfer = new Transfer(
            $client,
            $converter,
            $this->logger
        );
        $cashbox = $transfer->getCashBox($this->settingsConn);

        $this->assertIsObject($cashbox);
        $this->assertObjectHasAttribute('type', $cashbox);
        $this->assertObjectHasAttribute('taxation', $cashbox);
        $this->assertObjectHasAttribute('billing_place_list', $cashbox);
    }

    /**
     * @covers Innokassa\MDK\Net\Transfer::getCashBox
     * @example curl -i -H 'Authorization: Basic dGVzdDojSyEtayhEN3hbUm9feTQwW3wtWA==' https://api.innokassa.ru/v2/c_groups/0
     */
    public function testGetCashBoxFail()
    {
        $settingsConn = new SettingsConn(TEST_ACTOR_ID, TEST_ACTOR_TOKEN, -1);

        $client = new NetClientCurl();
        $converter = new ConverterApi();
        $transfer = new Transfer($client, $converter, $this->logger);

        $this->expectException(TransferException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage(TransferException::CODE_404);
        $transfer->getCashBox($settingsConn);
    }
}
