<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Settings\SettingsInterface;

/**
 * Интерфейс сервиса работы соединения с сервером фискализации
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

    /**
     * Получить ссылку на чек
     *
     * @throws PrinterException
     * 
     * @param integer $idReceipt
     * @return string
     */
    public function getReceiptLink(int $idReceipt): string;
};
