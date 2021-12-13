<?php

namespace Innokassa\MDK\Collections;

use Innokassa\MDK\Entities\Receipt;

/**
 * Коллекция чеков
 */
class ReceiptCollection extends BaseCollection
{
    /**
     * Получить общую сумму за все позиции всех чеков
     *
     * @return float
     */
    public function getAmount(): float
    {
        $amount = 0.0;
        foreach($this as $receipt)
            $amount += $receipt->getItems()->getAmount();

        return $amount;
    }

    /**
     * Объединить коллекции
     *
     * @param ReceiptCollection $collection
     * @return self
     */
    public function merge(ReceiptCollection $collection): self
    {
        $this->objects = array_merge($this->objects, $collection->objects);
        return $this;
    }

    /**
     * Получить чек по типу
     *
     * @param string $typeReceipt тип чека из Receipt::TYPE_
     * @return Receipt
     */
    public function getByType(string $typeReceipt, int $subType=null): ?Receipt
    {
        foreach($this->objects as $key => $receipt)
        {
            if(
                $receipt->getType() == $typeReceipt
                && ($subType === null || ($receipt->getSubType() == $subType))
            )
                return $this->objects[$key];
        }

        return null;
    }
};
