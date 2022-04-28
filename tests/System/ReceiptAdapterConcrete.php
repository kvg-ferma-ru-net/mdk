<?php

use Innokassa\MDK\Entities\Atoms\Vat;
use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Entities\Atoms\PaymentMethod;
use Innokassa\MDK\Entities\Primitives\Customer;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;
use Innokassa\MDK\Entities\ReceiptAdapterInterface;
use Innokassa\MDK\Collections\ReceiptItemCollection;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class ReceiptAdapterConcrete implements ReceiptAdapterInterface
{
    public function __construct(db $db, SettingsInterface $settings)
    {
        $this->db = $db;
        $this->settings = $settings;
    }

    public function getItems(string $orderId, int $subType): ReceiptItemCollection
    {
        $paymentMethod = $this->getPaymentMethod($subType);

        $a = json_decode($this->get($orderId)['items'], true);
        $items = new ReceiptItemCollection();
        foreach ($a as $value) {
            $items[] = (new ReceiptItem())
                ->setType($this->settings->getTypeDefaultItems())
                ->setName($value['name'])
                ->setPrice($value['price'])
                ->setQuantity($value['quantity'])
                ->setPaymentMethod($paymentMethod)
                ->setVat(new Vat($this->settings->getVatDefaultItems()));
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

    private $db = null;
    private $settings = null;

    //######################################################################

    private function get(string $orderId): array
    {
        $sql = "SELECT * FROM `orders` WHERE `id`=$orderId";
        return $this->db->query($sql, true)[0];
    }

    private function getPaymentMethod(int $subType): int
    {
        $paymentMethod = null;

        switch ($subType) {
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
}
