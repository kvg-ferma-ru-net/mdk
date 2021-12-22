<?php

namespace Innokassa\MDK\Settings;

/**
 * Интерфейс настроек
 */
interface SettingsInterface
{
    /**
     * Идентификатор актора
     *
     * @return string
     */
    public function getActorId(): string;

    /**
     * Токена актора
     *
     * @return string
     */
    public function getActorToken(): string;

    /**
     * Группа касс
     *
     * @return string
     */
    public function getCashbox(): string;

    /**
     * Место расчетов (сайт)
     *
     * @return string
     */
    public function getLocation(): string;

    /**
     * Налогообложение
     *
     * @return integer
     */
    public function getTaxation(): int;

    /**
     * Нужно ли пробивать только второй чек
     *
     * @return boolean
     */
    public function getOnly2(): bool;

    /**
     * Получить произвольную настройку
     *
     * @param string $name название настройки
     * @throws SettingsException
     * @return string
     */
    public function get(string $name);
};
