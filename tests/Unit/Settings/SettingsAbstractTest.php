<?php

use PHPUnit\Framework\TestCase;
use Innokassa\MDK\Settings\SettingsConn;
use Innokassa\MDK\Settings\SettingsAbstract;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * @uses Innokassa\MDK\Settings\SettingsAbstract
 * @uses Innokassa\MDK\Settings\SettingsConn
 */
class SettingsAbstractTest extends TestCase
{
    /**
     * @covers Innokassa\MDK\Settings\SettingsAbstract::extrudeConn
     */
    public function test()
    {
        $settings = $this->getMockForAbstractClass(SettingsAbstract::class);
        $settings
            ->method('getActorId')
            ->willReturn(TEST_ACTOR_ID);
        $settings
            ->method('getActorToken')
            ->willReturn(TEST_ACTOR_TOKEN);
        $settings
            ->method('getCashbox')
            ->willReturn(TEST_CASHBOX_WITHOUT_AGENT);

        $settingsConn = $settings->extrudeConn();
        $this->assertInstanceOf(SettingsConn::class, $settingsConn);
    }
}
