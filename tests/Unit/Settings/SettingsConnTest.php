<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Settings\SettingsConn;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Settings\SettingsConn
 */
class SettingsConnTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Settings\SettingsConn::__construct
     * @covers Innokassa\MDK\Settings\SettingsConn::getActorId
     * @covers Innokassa\MDK\Settings\SettingsConn::getActorToken
     * @covers Innokassa\MDK\Settings\SettingsConn::getCashbox
     */
    public function test()
    {
        $settingsConn = new SettingsConn('0', '1', '2');

        $this->assertSame('0', $settingsConn->getActorId());
        $this->assertSame('1', $settingsConn->getActorToken());
        $this->assertSame('2', $settingsConn->getCashbox());
    }
}
