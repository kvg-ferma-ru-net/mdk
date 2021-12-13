<?php

namespace Innokassa\MDK\Storage;

/**
 * Фильтр для выборки чеков из хранилища
 */
class ReceiptFilter
{
    /**
     * Установить тип чека
     *
     * @param integer $type из констант ReceiptType
     * @return self
     */
    public function setType(int $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Установить подтип чека
     *
     * @param integer $subType из констант ReceiptSubType
     * @return self
     */
    public function setSubType(int $subType): self
    {
        $this->subType = $subType;
        return $this;
    }

    /**
     * Установить статус чека
     *
     * @param integer $status из констант ReceiptStatus
     * @return self
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Установить идентификатор заказа
     *
     * @param string $orderId
     * @return self
     */
    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    //######################################################################

    /**
     * Преобразовать данные фильтра в ассоциативный массив WHERE условия, где ключ название столбца, а значение - искомое
     *
     * @return array
     */
    public function toArray(): array
    {
        $fields = array_keys(get_object_vars($this));

        $a = [];
        foreach($fields as $field)
        {
            if(!is_null($this->$field))
                $a[$field] = $this->$field;
        }

        return $a;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $type = null;
    private $subType = null;
    private $status = null;
    private $orderId = null;
};
