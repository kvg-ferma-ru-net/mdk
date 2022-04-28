<?php

namespace Innokassa\MDK\Settings;

use Innokassa\MDK\Exceptions\SettingsException;

/**
 * Интерфейс настроек
 */
interface SettingsInterface
{
    /** Схема: первый чек и второй чек */
    public const SCHEME_PRE_FULL    = 0;

    /** Схема: только второй чек */
    public const SCHEME_ONLY_FULL   = 1;

    //######################################################################

    /**
     * Идентификатор актора
     *
     * @param string|int|null $siteId
     * @return string
     */
    public function getActorId($siteId = null): string;

    /**
     * Токена актора
     *
     * @param string|int|null $siteId
     * @return string
     */
    public function getActorToken($siteId = null): string;

    /**
     * Группа касс
     *
     * @param string|int|null $siteId
     * @return string
     */
    public function getCashbox($siteId = null): string;

    /**
     * Место расчетов (сайт)
     *
     * @param string|int|null $siteId
     * @return string
     */
    public function getLocation($siteId = null): string;

    /**
     * Налогообложение
     *
     * @param string|int|null $siteId
     * @return integer
     */
    public function getTaxation($siteId = null): int;

    /**
     * Получить схему фискализации
     *
     * @param string|int|null $siteId
     * @return integer
     */
    public function getScheme($siteId = null): int;

    /**
     * Получить НДС для доставки
     *
     * @param string|int|null $siteId
     * @return integer
     */
    public function getVatShipping($siteId = null): int;

    /**
     * Получить НДС по умолчанию для позиций
     *
     * @param string|int|null $siteId
     * @return integer
     */
    public function getVatDefaultItems($siteId = null): int;

    /**
     * Получить тип позиций чека по умолчанию
     *
     * @param string|int|null $siteId
     * @return integer
     */
    public function getTypeDefaultItems($siteId = null): int;

    /**
     * Получить статус заказа для чека предоплаты
     *
     * @param string|int|null $siteId
     * @return string
     */
    public function getOrderStatusReceiptPre($siteId = null): string;

    /**
     * Получить статус заказа для чека полного расчета
     *
     * @param string|int|null $siteId
     * @return string
     */
    public function getOrderStatusReceiptFull($siteId = null): string;

    //######################################################################

    /**
     * Получить произвольную настройку
     *
     * @throws SettingsException
     *
     * @param string $name название настройки
     * @param string|int|null $siteId
     * @return string
     */
    public function get(string $name, $siteId = null);
}
