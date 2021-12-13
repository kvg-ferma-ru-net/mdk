<?php

namespace Innokassa\MDK\Services;

/**
 * Сервис печати чеков (показа верстки или ссылки)
 */
interface PrinterInterface
{
    /**
     * Рендер чека
     *
     * @throws PrinterException
     * 
     * @param integer $checkId
     * @return string
     */
    public function print(int $checkId): string;
};
