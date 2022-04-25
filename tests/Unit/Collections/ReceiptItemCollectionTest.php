<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Collections\ReceiptItemCollection;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Collections\ReceiptItemCollection
 * @uses Innokassa\MDK\Collections\BaseCollection
 * @uses Innokassa\MDK\Entities\ReceiptItem
 * @uses Innokassa\MDK\Entities\Atoms\PaymentMethod
 * @uses Innokassa\MDK\Entities\Atoms\ReceiptItemType
 * @uses Innokassa\MDK\Entities\Atoms\Vat
 * @uses Innokassa\MDK\Entities\Atoms\Unit
 */
class ReceiptItemCollectionTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Collections\ReceiptItemCollection::getAmount
     */
    public function test()
    {
        $items = new ReceiptItemCollection();
        $items[] = (new ReceiptItem())
            ->setPrice(100);
        $items[] = (new ReceiptItem())
            ->setPrice(100);

        $this->assertSame(200.0, $items->getAmount());
    }
}
