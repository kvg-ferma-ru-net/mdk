<?php

use PHPUnit\Framework\TestCase;

use Innokassa\MDK\Entities\Atoms\PaymentMethod;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * @uses Innokassa\MDK\Entities\Atoms\PaymentMethod
 */
class PaymentMethodTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Entities\Atoms\PaymentMethod::__construct
     * @covers Innokassa\MDK\Entities\Atoms\PaymentMethod::getCode
     */
    public function testConstructGetCode()
    {
        $this->assertSame(
            PaymentMethod::PREPAYMENT_FULL, 
            (new PaymentMethod(PaymentMethod::PREPAYMENT_FULL))->getCode()
        );
        $this->assertSame(
            PaymentMethod::PREPAYMENT_PART, 
            (new PaymentMethod(PaymentMethod::PREPAYMENT_PART))->getCode()
        );
        $this->assertSame(
            PaymentMethod::ADVANCE, 
            (new PaymentMethod(PaymentMethod::ADVANCE))->getCode()
        );
        $this->assertSame(
            PaymentMethod::CREDIT_FULL, 
            (new PaymentMethod(PaymentMethod::CREDIT_FULL))->getCode()
        );
        $this->assertSame(
            PaymentMethod::CREDIT_PART, 
            (new PaymentMethod(PaymentMethod::CREDIT_PART))->getCode()
        );
        $this->assertSame(
            PaymentMethod::CREDIT_PAY, 
            (new PaymentMethod(PaymentMethod::CREDIT_PAY))->getCode()
        );
        $this->assertSame(
            PaymentMethod::PAYMENT_FULL, 
            (new PaymentMethod(PaymentMethod::PAYMENT_FULL))->getCode()
        );

        $a = PaymentMethod::all();
        $this->assertIsArray($a);
        $this->assertContainsOnlyInstancesOf(PaymentMethod::class, $a);
        $this->assertCount(7, $a);

        $this->expectException(InvalidArgumentException::class);
        new PaymentMethod(0);
    }

    /**
     * @covers Innokassa\MDK\Entities\Atoms\PaymentMethod::all
     */
    public function testAll()
    {
        $a = PaymentMethod::all();
        $this->assertIsArray($a);
        $this->assertContainsOnlyInstancesOf(PaymentMethod::class, $a);
        $this->assertCount(7, $a);
    }
};
