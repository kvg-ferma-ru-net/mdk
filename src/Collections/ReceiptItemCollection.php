<?php

namespace Innokassa\MDK\Collections;

/**
 * Коллекция позиций чека
 */
class ReceiptItemCollection extends BaseCollection
{
    /**
     * Получить общую сумму всех позиции
     *
     * @return float
     */
    public function getAmount(): float
    {
        $amount = 0.0;
        foreach ($this as $receiptItem) {
            $amount += $receiptItem->getAmount();
        }

        return $amount;
    }
}
