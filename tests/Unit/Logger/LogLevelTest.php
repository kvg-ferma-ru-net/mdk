<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Logger\LogLevel;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Logger\LogLevel
 */
class LogLevelTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Logger\LogLevel::getName
     */
    public function testGetName()
    {
        $this->assertSame(
            'INFO',
            LogLevel::getName(LogLevel::INFO)
        );
        $this->assertSame(
            'ERROR',
            LogLevel::getName(LogLevel::ERROR)
        );
        $this->assertSame(
            '0',
            LogLevel::getName(0)
        );
    }
}
