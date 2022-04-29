<?php

namespace Innokassa\MDK\Settings;

use Innokassa\MDK\Exceptions\SettingsException;

/**
 * Абстрактный класс настроек
 */
abstract class SettingsAbstract
{
    /** Схема: первый чек и второй чек */
    public const SCHEME_PRE_FULL    = 0;

    /** Схема: только второй чек */
    public const SCHEME_ONLY_FULL   = 1;

    //######################################################################

    /**
     * Извлечь настройки соединения/авторизации для конкретного сайта
     *
     * @throws SettingsException
     *
     * @param string $siteId
     * @return SettingsConn
     */
    public function extrudeConn(string $siteId = ''): SettingsConn
    {
        $settingsConn = new SettingsConn(
            $this->getActorId($siteId),
            $this->getActorToken($siteId),
            $this->getCashbox($siteId)
        );

        return $settingsConn;
    }

    /**
     * Идентификатор актора
     *
     * @throws SettingsException
     *
     * @param string $siteId
     * @return string
     */
    abstract public function getActorId(string $siteId = ''): string;

    /**
     * Токена актора
     *
     * @throws SettingsException
     *
     * @param string $siteId
     * @return string
     */
    abstract public function getActorToken(string $siteId = ''): string;

    /**
     * Группа касс
     *
     * @throws SettingsException
     *
     * @param string $siteId
     * @return string
     */
    abstract public function getCashbox(string $siteId = ''): string;

    /**
     * Место расчетов (сайт)
     *
     * @throws SettingsException
     *
     * @param string $siteId
     * @return string
     */
    abstract public function getLocation(string $siteId = ''): string;

    /**
     * Налогообложение
     *
     * @throws SettingsException
     *
     * @param string $siteId
     * @return integer
     */
    abstract public function getTaxation(string $siteId = ''): int;

    /**
     * Получить схему фискализации
     *
     * @throws SettingsException
     *
     * @param string $siteId
     * @return integer
     */
    abstract public function getScheme(string $siteId = ''): int;

    /**
     * Получить НДС для доставки
     *
     * @throws SettingsException
     *
     * @param string $siteId
     * @return integer
     */
    abstract public function getVatShipping(string $siteId = ''): int;

    /**
     * Получить НДС по умолчанию для позиций
     *
     * @throws SettingsException
     *
     * @param string $siteId
     * @return integer
     */
    abstract public function getVatDefaultItems(string $siteId = ''): int;

    /**
     * Получить тип позиций чека по умолчанию
     *
     * @throws SettingsException
     *
     * @param string $siteId
     * @return integer
     */
    abstract public function getTypeDefaultItems(string $siteId = ''): int;

    /**
     * Получить статус заказа для чека предоплаты
     *
     * @throws SettingsException
     *
     * @param string $siteId
     * @return string
     */
    abstract public function getOrderStatusReceiptPre(string $siteId = ''): string;

    /**
     * Получить статус заказа для чека полного расчета
     *
     * @throws SettingsException
     *
     * @param string $siteId
     * @return string
     */
    abstract public function getOrderStatusReceiptFull(string $siteId = ''): string;

    //######################################################################

    /**
     * Получить произвольную настройку
     *
     * @throws SettingsException
     *
     * @param string $name название настройки
     * @param string $siteId
     * @return string
     */
    abstract public function get(string $name, string $siteId = '');
}
