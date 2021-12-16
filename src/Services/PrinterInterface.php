<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\Receipt;

/**
 * Сервис печати чеков
 */
interface PrinterInterface
{
    /**
     * Получить ссылку на рендер чека с проверкой на корректность данных
     *
     * @throws PrinterException
     * 
     * @param integer $idReceipt
     * @return string
     */
    public function getLinkVerify(int $idReceipt): string;

    /**
     * Получить ссылку на рендер чека без проверок на корректность
     *
     * @param Receipt $receipt
     * @return string
     */
    public function getLinkRaw(Receipt $receipt): string;
};
