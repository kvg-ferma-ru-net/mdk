<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\Receipt;

/**
 * Интерфейс сервиса автоматической фискализации заказов
 */
interface AutomaticInterface
{
    /**
     * Автоматическая фискализация прихода по заказу
     *
     * @throws InvalidArgumentException
     * @throws TransferException
     * @throws StorageException
     * @throws AutomaticException
     * 
     * @param integer $orderId
     * @param integer|null $receiptSubType подтип чека для конкретизации, либо null для автоматического определения типа
     * @return Receipt
     */
    public function fiscalize(string $orderId, int $receiptSubType=null): Receipt;
};
