<?php

use PHPUnit\Framework\TestCase;

use Innokassa\MDK\Entities\Primitives\Amount;

use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * @uses Innokassa\MDK\Entities\Primitives\Amount
 */
class AmountTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Entities\Primitives\Amount::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Amount::get
     */
    public function testConstructSuccess()
    {
        $amount = new Amount(Amount::CASHLESS, 100);
        $this->assertSame(100.0, $amount->get(Amount::CASHLESS));

        $amount = new Amount(Amount::CASH, 0);
        $this->assertSame(0.0, $amount->get(Amount::CASH));

        $amount = new Amount(Amount::PREPAYMENT, 100);
        $this->assertSame(100.0, $amount->get(Amount::PREPAYMENT));

        $amount = new Amount(Amount::POSTPAYMENT, 100);
        $this->assertSame(100.0, $amount->get(Amount::POSTPAYMENT));

        $amount = new Amount(Amount::BARTER, 100);
        $this->assertSame(100.0, $amount->get(Amount::BARTER));
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Amount::__construct
     */
    public function testConstructFail1()
    {
        $this->expectException(InvalidArgumentException::class);
        $amount = new Amount(0, 100);
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Amount::__construct
     */
    public function testConstructFail2()
    {
        $this->expectException(InvalidArgumentException::class);
        $amount = new Amount(Amount::PREPAYMENT, -1);
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Amount::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Amount::set
     * @covers Innokassa\MDK\Entities\Primitives\Amount::get
     */
    public function testSetSuccess()
    {
        $amount = new Amount();
        $amount->set(Amount::CASHLESS, 100);
        $this->assertSame(100.0, $amount->get(Amount::CASHLESS));

        $amount = new Amount();
        $amount->set(Amount::CASH, 0);
        $this->assertSame(0.0, $amount->get(Amount::CASH));

        $amount = new Amount();
        $amount->set(Amount::PREPAYMENT, 100);
        $this->assertSame(100.0, $amount->get(Amount::PREPAYMENT));

        $amount = new Amount();
        $amount->set(Amount::POSTPAYMENT, 100);
        $this->assertSame(100.0, $amount->get(Amount::POSTPAYMENT));

        $amount = new Amount();
        $amount->set(Amount::BARTER, 100);
        $this->assertSame(100.0, $amount->get(Amount::BARTER));
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Amount::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Amount::set
     */
    public function testSetFail1()
    {
        $amount = new Amount();
        $this->expectException(InvalidArgumentException::class);
        $amount->set(0, 100);
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Amount::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Amount::set
     */
    public function testSetFail2()
    {
        $amount = new Amount();
        $this->expectException(InvalidArgumentException::class);
        $amount->set(Amount::PREPAYMENT, -1);
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Amount::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Amount::get
     */
    public function testGetFail()
    {
        $amount = new Amount();
        $this->expectException(InvalidArgumentException::class);
        $amount->get(0);
    }
};
