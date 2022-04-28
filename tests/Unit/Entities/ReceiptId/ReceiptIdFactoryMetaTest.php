<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Entities\ReceiptId\ReceiptIdFactoryMeta;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Entities\ReceiptId\ReceiptIdFactoryMeta
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptStatus
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptSubType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptType
 * @uses Innokassa\MDK\Entities\Receipt
 */
class ReceiptIdFactoryMetaTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Entities\ReceiptId\ReceiptIdFactoryMeta::build
     * @covers Innokassa\MDK\Entities\ReceiptId\ReceiptIdFactoryMeta::verify
     * @covers Innokassa\MDK\Entities\ReceiptId\ReceiptIdFactoryMeta::getEngine
     */
    public function test()
    {
        $receiptIdFactory = new ReceiptIdFactoryMeta();

        $receipt = new Receipt();
        $receipt->setOrderId('123');
        $receiptId = $receiptIdFactory->build($receipt);
        $this->assertIsString($receiptId);
        $this->assertTrue($receiptIdFactory->verify($receiptId));
    }
}
