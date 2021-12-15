<?php

use PHPUnit\Framework\TestCase;

use Innokassa\MDK\Storage\ReceiptFilter;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;

/**
 * @uses Innokassa\MDK\Storage\ReceiptFilter
 */
class ReceiptFilterTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Storage\ReceiptFilter::setType
     * @covers Innokassa\MDK\Storage\ReceiptFilter::setSubType
     * @covers Innokassa\MDK\Storage\ReceiptFilter::setStatus
     * @covers Innokassa\MDK\Storage\ReceiptFilter::setOrderId
     * @covers Innokassa\MDK\Storage\ReceiptFilter::toArray
     */
    public function test()
    {
        $filter = new ReceiptFilter();
        $filter->setType(ReceiptType::COMING);
        $this->assertSame(
            [
                'type' => ReceiptType::COMING,
            ],
            $filter->toArray()
        );

        $filter = new ReceiptFilter();
        $filter->setType(ReceiptType::COMING);
        $filter->setSubType(ReceiptSubType::FULL);
        $filter->setStatus(ReceiptStatus::COMPLETED);
        $filter->setOrderId('0');
        $this->assertSame(
            [
                'type' => ReceiptType::COMING,
                'subType' => ReceiptSubType::FULL,
                'status' => ReceiptStatus::COMPLETED,
                'orderId' => '0'
            ],
            $filter->toArray()
        );
    }
};
