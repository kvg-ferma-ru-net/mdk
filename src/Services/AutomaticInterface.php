<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Exceptions\StorageException;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Exceptions\Services\AutomaticException;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

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
     * @param string $orderId
     * @param string $siteId
     * @param integer|null $receiptSubType подтип чека для конкретизации, либо null для автоматического определения типа
     * @return Receipt
     */
    public function fiscalize(string $orderId, string $siteId = '', int $receiptSubType = null): Receipt;
}
