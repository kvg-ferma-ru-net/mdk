<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Settings\SettingsInterface;

/**
 * Интерфейс сервиса тестирования соединения и настроек
 */
interface ConnectorInterface
{
    /**
     * Тестирование соединения и настроек модуля на соответствие данным кассы
     *
     * @throws SettingsException
     * @return bool true в случае успеха
     */
    public function testSettings(SettingsInterface $settings): bool;
}
