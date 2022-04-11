<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Entities\Primitives\Notify
 */
class NotifyTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Entities\Primitives\Notify::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Notify::setEmail
     * @covers Innokassa\MDK\Entities\Primitives\Notify::getEmail
     * @covers Innokassa\MDK\Entities\Primitives\Notify::setPhone
     * @covers Innokassa\MDK\Entities\Primitives\Notify::getPhone
     */
    public function testConstructSuccess()
    {
        $notify = new Notify();
        $notify->setEmail('box@domain.zone');
        $this->assertSame('box@domain.zone', $notify->getEmail());

        $notify = new Notify('box@domain.zone');
        $this->assertSame('box@domain.zone', $notify->getEmail());

        $notify = new Notify('+79998887766');
        $this->assertSame('+79998887766', $notify->getPhone());
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Notify::__construct
     */
    public function testConstructFail1()
    {
        $this->expectException(InvalidArgumentException::class);
        $notify = new Notify('');
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Notify::__construct
     */
    public function testConstructFail2()
    {
        $this->expectException(InvalidArgumentException::class);
        $notify = new Notify('d 8-999 888-77 66 ');
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Notify::__construct
     */
    public function testConstructFail3()
    {
        $this->expectException(InvalidArgumentException::class);
        $notify = new Notify('box @domain.zone');
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Notify::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Notify::setEmail
     * @covers Innokassa\MDK\Entities\Primitives\Notify::getEmail
     */
    public function testSetEmail()
    {
        $notify = new Notify('box@domain.zone');
        $notify->setEmail(' box2@domain.zone');
        $this->assertSame('box2@domain.zone', $notify->getEmail());
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Notify::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Notify::setEmail
     */
    public function testSetEmailFail1()
    {
        $this->expectException(InvalidArgumentException::class);
        $notify = new Notify();
        $notify->setEmail('box @domain.zone');
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Notify::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Notify::setEmail
     */
    public function testSetEmailFail2()
    {
        $this->expectException(InvalidArgumentException::class);
        $notify = new Notify();
        $notify->setEmail('');
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Notify::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Notify::setPhone
     */
    public function testSetPhone()
    {
        $notify = new Notify('+79998887766');
        $notify->setPhone('+79998887755');
        $this->assertSame('+79998887755', $notify->getPhone());

        $notify = new Notify();
        $notify->setPhone('89998887766');
        $this->assertSame('+79998887766', $notify->getPhone());

        $notify = new Notify(' 8-999 888-77 66 ');
        $this->assertSame('+79998887766', $notify->getPhone());
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Notify::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Notify::setPhone
     */
    public function testSetPhoneFail1()
    {
        $this->expectException(InvalidArgumentException::class);
        $notify = new Notify();
        $notify->setPhone('d 8-999 888-77 66 ');
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Notify::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Notify::setPhone
     */
    public function testSetPhoneFail2()
    {
        $this->expectException(InvalidArgumentException::class);
        $notify = new Notify();
        $notify->setPhone(' 8-999 888-77 6 ');
    }

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Notify::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Notify::setPhone
     */
    public function testSetPhoneFail3()
    {
        $this->expectException(InvalidArgumentException::class);
        $notify = new Notify();
        $notify->setPhone('');
    }

    //######################################################################

    /**
     * @covers Innokassa\MDK\Entities\Primitives\Notify::__construct
     * @covers Innokassa\MDK\Entities\Primitives\Notify::setEmail
     * @covers Innokassa\MDK\Entities\Primitives\Notify::setPhone
     */
    public function testSetPhoneEmail()
    {
        $notify = new Notify();
        $notify->setEmail('box@domain.zone');
        $notify->setPhone('+79998887766');
        $this->assertSame('box@domain.zone', $notify->getEmail());
        $this->assertSame('+79998887766', $notify->getPhone());
    }
}
