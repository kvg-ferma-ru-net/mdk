<?php

namespace Innokassa\MDK\Collections;

/**
 * Коллекция позиций заказа
 */
class ReceiptItemCollection extends BaseCollection
{
    /**
     * Получить общую сумму за все позиции
     *
     * @return float
     */
    public function getAmount(): float
    {
        $amount = 0.0;
        foreach($this as $receiptItem)
            $amount += $receiptItem->getAmount();

        return $amount;
    }
};
