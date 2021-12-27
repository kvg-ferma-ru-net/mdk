<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Collections\ReceiptItemCollection;

/**
 * Интерфейс сервиса ручной фискализации заказов
 */
interface ManualInterface
{
    /**
     * Фискализация прихода по заказу
     *
     * @throws InvalidArgumentException
     * @throws TransferException
     * @throws StorageException
     *
     * @param string $orderId
     * @param ReceiptItemCollection $items
     * @param Notify $notify
     * @param Amount|null $amount для конкретизации сумм расчета, либо null и вся сумма будет Amount::CASHLESS
     * @return Receipt
     */
    public function fiscalize(
        string $orderId,
        ReceiptItemCollection $items,
        Notify $notify,
        Amount $amount = null
    ): Receipt;

    /**
     * Фискализация возврата по заказу
     *
     * @throws InvalidArgumentException
     * @throws TransferException
     * @throws StorageException
     * @throws RefundException
     *
     * @param string $orderId
     * @param ReceiptItemCollection $items
     * @param Notify $notify
     * @param Amount|null $amount для конкретизации сумм расчета, либо null и вся сумма будет Amount::CASHLESS
     * @return Receipt
     */
    public function refund(
        string $orderId,
        ReceiptItemCollection $items,
        Notify $notify,
        Amount $amount = null
    ): Receipt;
}
