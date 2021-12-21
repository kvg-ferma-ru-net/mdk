<?php

namespace Innokassa\MDK\Storage;

/**
 * Фильтр для выборки чеков из хранилища
 */
class ReceiptFilter
{
    const OP_EQ = '=';
    const OP_NOTEQ = '!=';
    const OP_GT = '>';
    const OP_LT = '<';

    /**
     * Установить id чека
     *
     * @param integer $id локальный id чека
     * @return self
     */
    public function setId(int $id, string $op=self::OP_EQ): self
    {
        $this->id = [
            'value' => $id,
            'op' => $op
        ];
        return $this;
    }

    /**
     * Установить тип чека
     *
     * @param integer $type из констант ReceiptType
     * @return self
     */
    public function setType(int $type, string $op=self::OP_EQ): self
    {
        $this->type = [
            'value' => $type,
            'op' => $op
        ];
        return $this;
    }

    /**
     * Установить подтип чека
     *
     * @param integer $subType из констант ReceiptSubType
     * @return self
     */
    public function setSubType(int $subType, string $op=self::OP_EQ): self
    {
        $this->subType = [
            'value' => $subType,
            'op' => $op
        ];
        return $this;
    }

    /**
     * Установить статус чека
     *
     * @param integer $status из констант ReceiptStatus
     * @return self
     */
    public function setStatus(int $status, string $op=self::OP_EQ): self
    {
        $this->status = [
            'value' => $status,
            'op' => $op
        ];
        return $this;
    }

    /**
     * Установить идентификатор заказа
     *
     * @param string $orderId
     * @return self
     */
    public function setOrderId(string $orderId, string $op=self::OP_EQ): self
    {
        $this->orderId = [
            'value' => $orderId,
            'op' => $op
        ];
        return $this;
    }

    //######################################################################

    /**
     * Преобразовать данные фильтра в ассоциативный массив WHERE условия, где ключ название столбца, а значение - ассоциативный массив [value => value, op => op]
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

    private $id = null;
    private $type = null;
    private $subType = null;
    private $status = null;
    private $orderId = null;
};
