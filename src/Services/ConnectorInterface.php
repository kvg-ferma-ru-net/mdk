<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Settings\SettingsInterface;

/**
 * Интерфейс сервиса тестирования соединения и настроек
 */
interface ConnectorInterface
{
    /**
     * Тестирование настроек модуля на соответствие данным кассы
     *
     * @throws SettingsExceptionв случае некорректности настроек
     * @return bool true в случае успеха
     */
    public function testSettings(SettingsInterface $settings): bool;
};
