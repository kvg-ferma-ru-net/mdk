<?php

namespace Innokassa\MDK\Entities\ReceiptId;

use Innokassa\MDK\Entities\Receipt;

/**
 * Интерфейс фабрики идентификаторов чеков
 */
interface ReceiptIdFactoryInterface
{
    /**
     * Сгенерировать идентификатор чека
     *
     * @param Receipt $receipt
     * @return string
     */
    public function build(Receipt $receipt): string;

    /**
     * Проверка на валидность идентификатора чека
     *
     * @param string $id
     * @return boolean
     */
    public function verify(string $id): bool;
}
