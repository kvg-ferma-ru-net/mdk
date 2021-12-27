<?php

namespace Innokassa\MDK\Entities;

use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Entities\Primitives\Customer;
use Innokassa\MDK\Collections\ReceiptItemCollection;

/**
 * Адаптер чеков из заказов, реализуется на стороне конкретной интеграции
 */
interface ReceiptAdapterInterface
{
    /**
     * Получить коллекцию позиций заказа
     *
     * @throws InvalidArgumentException
     *
     * @param string $orderId
     * @param int $subType подтип создаваемого чека (ReceiptSubType::PRE || ReceiptSubType::FULL)
     * @return ReceiptItemCollection
     */
    public function getItems(string $orderId, int $subType): ReceiptItemCollection;

    /**
     * Получить данные об оплате
     *
     * @throws InvalidArgumentException
     *
     * @param string $orderId
     * @param int $subType подтип создаваемого чека (ReceiptSubType::PRE || ReceiptSubType::FULL)
     * @return Amount
     */
    public function getAmount(string $orderId, int $subType): Amount;

    /**
     * Получить данные покупателя
     *
     * @param string $orderId
     * @return Customer
     */
    public function getCustomer(string $orderId): Customer;

    /**
     * Получить данные для уведомления покупателя
     *
     * @param string $orderId
     * @return Notify
     */
    public function getNotify(string $orderId): Notify;
}
