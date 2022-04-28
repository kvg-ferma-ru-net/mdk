<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Collections\ReceiptCollection;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Collections\ReceiptCollection
 * @uses Innokassa\MDK\Collections\BaseCollection
 * @uses Innokassa\MDK\Collections\ReceiptItemCollection
 * @uses Innokassa\MDK\Entities\AtomAbstract
 * @uses Innokassa\MDK\Entities\Atoms\PaymentMethod
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptItemType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptStatus
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptSubType
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptType
 * @uses Innokassa\MDK\Entities\Atoms\Vat
 * @uses Innokassa\MDK\Entities\Atoms\Unit
 * @uses Innokassa\MDK\Entities\Receipt
 * @uses Innokassa\MDK\Entities\ReceiptItem
 */
class ReceiptCollectionTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Collections\ReceiptCollection::getAmount
     * @covers Innokassa\MDK\Collections\ReceiptCollection::merge
     * @covers Innokassa\MDK\Collections\ReceiptCollection::getByType
     */
    public function test()
    {
        $receipts = new ReceiptCollection();
        $receipts[] = (new Receipt())
            ->setType(ReceiptType::COMING)
            ->setSubType(ReceiptSubType::FULL)
            ->addItem(
                (new ReceiptItem())
                    ->setPrice(100)
            );

        $this->assertSame(100.0, $receipts->getAmount());

        $receipts2 = new ReceiptCollection();
        $receipts2[] = (new Receipt())
            ->setType(ReceiptType::REFUND_COMING)
            ->addItem(
                (new ReceiptItem())
                    ->setPrice(200)
            );

        $this->assertInstanceOf(ReceiptCollection::class, $receipts->merge($receipts2));

        $this->assertSame(300.0, $receipts->getAmount());

        $this->assertInstanceOf(Receipt::class, $receipts->getByType(ReceiptType::COMING, ReceiptSubType::FULL));
        $this->assertInstanceOf(Receipt::class, $receipts->getByType(ReceiptType::REFUND_COMING));
        $this->assertNull($receipts->getByType(ReceiptType::REFUND_COMING, ReceiptSubType::FULL));
    }
}
