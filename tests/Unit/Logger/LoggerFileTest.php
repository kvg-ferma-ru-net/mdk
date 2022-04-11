<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Logger\LoggerFile;
use Innokassa\MDK\Logger\LogLevel;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Logger\LoggerFile
 * @uses Innokassa\MDK\Logger\LogLevel
 */
class LoggerFileTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Logger\LoggerFile::__construct
     * @covers Innokassa\MDK\Logger\LoggerFile::getFile
     * @covers Innokassa\MDK\Logger\LoggerFile::getLastMsg
     */
    public function testConstruct()
    {
        $logger = new LoggerFile();
        $this->assertIsString($logger->getFile());
        $this->assertTrue(strlen($logger->getFile()) > 0);
        $this->assertSame([], $logger->getLastMsg());

        $dir = dirname($logger->getFile());
        $this->assertTrue(file_exists($dir));

        array_map('unlink', glob("$dir/*.*"));
        rmdir($dir);
        $logger = new LoggerFile();
        $this->assertTrue(file_exists($dir));

        array_map('unlink', glob("$dir/*.*"));
        rmdir($dir);
    }

    /**
     * @covers Innokassa\MDK\Logger\LoggerFile::__construct
     * @covers Innokassa\MDK\Logger\LoggerFile::getFile
     * @covers Innokassa\MDK\Logger\LoggerFile::getLastMsg
     */
    public function testConstructArg()
    {
        $dirLog = __DIR__ . '/../../logs';
        $logger = new LoggerFile($dirLog);
        $this->assertIsString($logger->getFile());
        $this->assertTrue(strlen($logger->getFile()) > 0);
        $this->assertSame([], $logger->getLastMsg());

        $dir = dirname($logger->getFile());
        $this->assertTrue(file_exists($dir));
        $this->assertIsString(realpath($dirLog));

        array_map('unlink', glob("$dir/*.*"));
        rmdir($dir);
    }

    /**
     * @covers Innokassa\MDK\Logger\LoggerFile::log
     * @covers Innokassa\MDK\Logger\LoggerFile::stacktrace
     * @covers Innokassa\MDK\Logger\LoggerFile::canStacktrace
     * @depends testConstruct
     */
    public function testLog()
    {
        $context = [
            'var1' => 'vav1',
            'var2' => 'vav1',
        ];

        $logger = new LoggerFile();
        $logger->log(
            LogLevel::INFO,
            'success ' . __METHOD__,
            $context
        );

        $a = $logger->getLastMsg();
        $this->assertSame(LogLevel::getName(LogLevel::INFO), $a['level']);
        $this->assertTrue(strlen($a['message']) > 0);
        $this->assertTrue(strlen($a['context']) > 0);
        $this->assertFalse(isset($a['stacktrace']));
        $this->assertTrue(
            strlen(file_get_contents($logger->getFile())) > 0
        );

        $dir = dirname($logger->getFile());
        array_map('unlink', glob("$dir/*.*"));
        rmdir($dir);


        $logger = new LoggerFile();
        $logger->log(
            LogLevel::ERROR,
            'error ' . __METHOD__,
            $context
        );

        $a = $logger->getLastMsg();
        $this->assertSame(LogLevel::getName(LogLevel::ERROR), $a['level']);
        $this->assertTrue(strlen($a['message']) > 0);
        $this->assertTrue(strlen($a['context']) > 0);
        $this->assertTrue(strlen($a['stacktrace']) > 0);
        $this->assertTrue(
            strlen(file_get_contents($logger->getFile())) > 0
        );
    }
}
