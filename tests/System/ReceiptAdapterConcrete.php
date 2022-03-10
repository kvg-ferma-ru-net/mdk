<?php

use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Entities\Atoms\PaymentMethod;
use Innokassa\MDK\Entities\Primitives\Customer;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;
use Innokassa\MDK\Entities\ReceiptAdapterInterface;
use Innokassa\MDK\Collections\ReceiptItemCollection;

class ReceiptAdapterConcrete implements ReceiptAdapterInterface
{
    public function __construct(db $db)
    {
        $this->db = $db;
    }

    public function getItems(string $orderId, int $subType): ReceiptItemCollection
    {
        $paymentMethod = $this->getPaymentMethod($subType);
        
        $a = json_decode($this->get($orderId)['items'], true);
        $items = new ReceiptItemCollection();
        foreach($a as $value)
        {
            $items[] = (new ReceiptItem())
                ->setName($value['name'])
                ->setPrice($value['price'])
                ->setQuantity($value['quantity'])
                ->setPaymentMethod($paymentMethod);
        }

        return $items;
    }

    public function getTotal(string $orderId): float
    {
        $items = $this->getItems($orderId, ReceiptSubType::PRE);
        return $items->getAmount();
    }

    public function getCustomer(string $orderId): ?Customer
    {
        $a = $this->get($orderId);
        return new Customer($a['customer']);
    }

    public function getNotify(string $orderId): Notify
    {
        $a = $this->get($orderId);
        return new Notify($a['notify']);
    }

    //######################################################################

    private $db;

    //######################################################################

    private function get(string $orderId): array
    {
        $sql = "SELECT * FROM `orders` WHERE `id`=$orderId";
        return $this->db->query($sql, true)[0];
    }

    private function getPaymentMethod(int $subType): int
    {
        $paymentMethod = null;

        switch($subType)
        {
            case ReceiptSubType::PRE:
                $paymentMethod = PaymentMethod::PREPAYMENT_FULL;
                break;
            case ReceiptSubType::FULL:
                $paymentMethod = PaymentMethod::PAYMENT_FULL;
                break;
            default:
                throw new InvalidArgumentException("invalid subType '$subType'");
        }

        return $paymentMethod;
    }
};
