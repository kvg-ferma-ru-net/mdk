<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Exceptions\BaseException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Exceptions\BaseException
 */
class BaseExceptionTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Exceptions\BaseException::toArray
     */
    public function testToArray()
    {
        $exception = new BaseException('message', 1);
        $this->assertEquals(
            [
                'code' => 1,
                'message' => 'message'
            ],
            $exception->toArray()
        );
    }
}
