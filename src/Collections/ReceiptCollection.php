<?php

namespace Innokassa\MDK\Collections;

use Innokassa\MDK\Entities\Receipt;

/**
 * Коллекция чеков
 */
class ReceiptCollection extends BaseCollection
{
    public function offsetGet($offset): Receipt
    {
        return parent::offsetGet($offset);
    }

    public function current(): Receipt
    {
        return $this->objects[$this->position];
    }

    //######################################################################

    /**
     * Получить общую сумму всех позиции всех чеков
     *
     * @return float
     */
    public function getAmount(): float
    {
        $amount = 0.0;
        foreach ($this as $receipt) {
            $amount += $receipt->getItems()->getAmount();
        }

        return $amount;
    }

    /**
     * Добавить данные из другой коллекции
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
     * @param integer $typeReceipt тип чека из ReceiptType
     * @param integer|null $subType подтип чека из ReceiptSubType
     * @return Receipt|null
     */
    public function getByType(int $typeReceipt, int $subType = null): ?Receipt
    {
        foreach ($this->objects as $key => $receipt) {
            if (
                $receipt->getType() == $typeReceipt
                && ($subType === null || ($receipt->getSubType() == $subType))
            ) {
                return $this->objects[$key];
            }
        }

        return null;
    }
}
