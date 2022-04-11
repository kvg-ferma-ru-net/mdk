<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Entities\UUID;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Entities\UUID
 */
class UUIDTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Entities\UUID::__construct
     * @covers Innokassa\MDK\Entities\UUID::get
     */
    public function testConstructGet()
    {
        $uuid = new UUID();
        $this->assertTrue(boolval(preg_match("/\w{32}/", $uuid->get())));

        $uuid = new UUID('c2b5df2d9ab5444d9fcd26b42e2a53e3');
        $this->assertSame('c2b5df2d9ab5444d9fcd26b42e2a53e3', $uuid->get());

        $this->expectException(InvalidArgumentException::class);
        new UUID('q2b5df2d9ab5444d9fcd26b42e2a53e3');
    }
}
