<?php

use PHPUnit\Framework\TestCase;

use Innokassa\MDK\Entities\Primitives\Customer;

use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * @uses Innokassa\MDK\Entities\Primitives\Customer
 */
class CustomerTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Entities\Primitives\Customer::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Customer::setName
     * @covers Innokassa\MDK\Entities\Primitives\Customer::getName
     * @covers Innokassa\MDK\Entities\Primitives\Customer::setTin
     * @covers Innokassa\MDK\Entities\Primitives\Customer::getTin
     */
    public function testConstructSuccess()
    {
        $customer = new Customer();
        $this->assertSame('', $customer->getName());
        $this->assertSame('', $customer->getTin());

        $customer = new Customer('Тест Тест Тест', '0000000000');
        $this->assertSame('Тест Тест Тест', $customer->getName());
        $this->assertSame('0000000000', $customer->getTin());
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Customer::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Customer::setName
     */
    public function testConstructFail1()
    {
        $this->expectException(InvalidArgumentException::class);
        $customer = new Customer('', '0000000000');
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Customer::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Customer::setTin
     */
    public function testConstructFail2()
    {
        $this->expectException(InvalidArgumentException::class);
        $customer = new Customer('Тест Тест Тест', '');
    }

    //######################################################################
};
