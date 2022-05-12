<?php

namespace Innokassa\MDK\Storage;

/**
 * Фильтр для выборки чеков из хранилища
 */
class ReceiptFilter
{
    public const OP_EQ     = '=';
    public const OP_NOTEQ  = '!=';
    public const OP_GT     = '>';
    public const OP_LT     = '<';

    //######################################################################

    /**
     * Установить id чека
     *
     * @param integer $id локальный id чека
     * @param string $op операция сравнения
     * @return self
     */
    public function setId(int $id, string $op = self::OP_EQ): self
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
     * @param string $op операция сравнения
     * @return self
     */
    public function setType(int $type, string $op = self::OP_EQ): self
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
     * @param string $op операция сравнения
     * @return self
     */
    public function setSubType(int $subType, string $op = self::OP_EQ): self
    {
        $this->subtype = [
            'value' => $subType,
            'op' => $op
        ];
        return $this;
    }

    /**
     * Установить статус чека
     *
     * @param integer|array $status из констант ReceiptStatus
     * @param string $op операция сравнения
     * @return self
     */
    public function setStatus($status, string $op = self::OP_EQ): self
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
     * @param string $op операция сравнения
     * @return self
     */
    public function setOrderId(string $orderId, string $op = self::OP_EQ): self
    {
        $this->orderId = [
            'value' => $orderId,
            'op' => $op
        ];
        return $this;
    }

    /**
     * Установить идентификатор сайта
     *
     * @param string $siteId
     * @param string $op операция сравнения
     * @return self
     */
    public function setSiteId(string $siteId, string $op = self::OP_EQ): self
    {
        $this->siteId = [
            'value' => $siteId,
            'op' => $op
        ];
        return $this;
    }

    //######################################################################

    /**
     * Преобразовать данные фильтра в ассоциативный массив WHERE условия,
     * где ключ название столбца, а значение - ассоциативный массив [value => value, op => op]
     *
     * @return array<string, array<string, int|string>>
     */
    public function toArray(): array
    {
        $fields = array_keys(get_object_vars($this));

        $a = [];
        foreach ($fields as $field) {
            if (!is_null($this->$field)) {
                $column = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $field));
                $a[$column] = $this->$field;
            }
        }

        return $a;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    /** @var array<string, int|string> */
    private $id = null;

    /** @var array<string, int|string> */
    private $type = null;

    /** @var array<string, int|string> */
    private $subtype = null;

    /** @var array<string, int|string> */
    private $status = null;

    /** @var array<string, int|string> */
    private $orderId = null;

    /** @var array<string, int|string> */
    private $siteId = null;
}
