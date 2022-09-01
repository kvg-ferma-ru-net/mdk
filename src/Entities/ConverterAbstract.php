<?php

namespace Innokassa\MDK\Entities;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Entities\Atoms\Vat;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Entities\Primitives\Customer;
use Innokassa\MDK\Collections\ReceiptItemCollection;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;
use Innokassa\MDK\Exceptions\ConverterException;

/**
 * Интерфейс конвертера сущностей в массив и обратно
 */
abstract class ConverterAbstract
{
    //######################################################################
    // Receipt

    /**
     * Receipt => array
     *
     * @throws ConverterException
     *
     * @param Receipt $receipt
     * @return array<string, mixed>
     */
    abstract public function receiptToArray(Receipt $receipt): array;

    /**
     * array => Receipt
     *
     * @throws ConverterException
     *
     * @param array<string, mixed> $a
     * @return Receipt
     */
    abstract public function receiptFromArray(array $a): Receipt;

    //######################################################################
    // ReceiptItemCollection

    /**
     * ReceiptItemCollection => array
     *
     * @throws ConverterException
     *
     * @param ReceiptItemCollection $items
     * @return array<mixed>
     */
    public function itemsToArray(ReceiptItemCollection $items): array
    {
        $a = [];

        foreach ($items as $item) {
            $a[] = $this->itemToArray($item);
        }

        return $a;
    }

    /**
     * array => ReceiptItemCollection
     *
     * @throws ConverterException
     *
     * @param array<mixed> $a
     * @return ReceiptItemCollection
     */
    public function itemsFromArray(array $a): ReceiptItemCollection
    {
        $items = new ReceiptITemCollection();

        foreach ($a as $item) {
            $items[] = $this->itemFromArray($item);
        }

        return $items;
    }

    //######################################################################
    // ReceiptItem

    /**
     * ReceiptItem => array
     *
     * @throws ConverterException
     *
     * @param ReceiptItem $item
     * @return array<string, mixed>
     */
    public function itemToArray(ReceiptItem $item): array
    {
        if (!$item->getPrice()) {
            throw new ConverterException("uninitialized price item");
        }

        if (!$item->getName()) {
            throw new ConverterException("uninitialized name item");
        }

        $a = [
            "item_id" => $item->getItemId(),
            "type" => $item->getType(),
            "name" => $item->getName(),
            "price" => $item->getPrice(),
            "quantity" => $item->getQuantity(),
            "amount" => ($item->getAmount() > 0.0 ? $item->getAmount() : $item->getPrice() * $item->getQuantity()),
            "payment_method" => $item->getPaymentMethod(),
            "vat" => $item->getVat()->getCode(),
            "unit" => $item->getUnit(),
        ];

        return $a;
    }

    /**
     * array => ReceiptItem
     *
     * @throws ConverterException
     *
     * @param array<string, mixed> $a
     * @return ReceiptItem
     */
    public function itemFromArray(array $a): ReceiptItem
    {
        if (!$a) {
            throw new ConverterException('empty array for create receipt item');
        }

        $requiredFields = ['type', 'name', 'price', 'payment_method'];

        if ($diff = array_diff($requiredFields, array_keys($a))) {
            throw new ConverterException(
                'not complete array for create receipt item, lacks fields [' . implode(', ', $diff) . ']'
            );
        }

        try {
            $item = new ReceiptItem();
            $item
                ->setType($a['type'])
                ->setName($a['name'])
                ->setPrice($a['price'])
                ->setPaymentMethod($a['payment_method']);

            if (isset($a['item_id'])) {
                $item->setItemId($a['item_id']);
            }

            if (isset($a['quantity'])) {
                $item->setQuantity($a['quantity']);
            }

            if (isset($a['amount'])) {
                $item->setAmount($a['amount']);
            }

            if (isset($a['vat'])) {
                $item->setVat(new Vat($a['vat']));
            }

            if (isset($a['unit'])) {
                $item->setUnit($a['unit']);
            }
        } catch (InvalidArgumentException $e) {
            throw new ConverterException($e->getMessage());
        }

        return $item;
    }

    //######################################################################
    // Amount

    /**
     * Amount => array
     *
     * @throws ConverterException
     *
     * @param Amount $amount
     * @return array<string, mixed>
     */
    public function amountToArray(Amount $amount): array
    {
        $a = [];
        if ($amount->getCash() > 0.0) {
            $a['cash'] = $amount->getCash();
        }
        if ($amount->getCashless() > 0.0) {
            $a['cashless'] = $amount->getCashless();
        }
        if ($amount->getPrepayment() > 0.0) {
            $a['prepayment'] = $amount->getPrepayment();
        }
        if ($amount->getPostpayment() > 0.0) {
            $a['postpayment'] = $amount->getPostpayment();
        }
        if ($amount->getBarter() > 0.0) {
            $a['barter'] = $amount->getBarter();
        }

        if (!$a) {
            throw new ConverterException('invalid amount => array');
        }

        return $a;
    }

    /**
     * array => Amount
     *
     * @throws ConverterException
     *
     * @param array<string, mixed> $a
     * @return Amount
     */
    public function amountFromArray(array $a): Amount
    {
        if (!$a) {
            throw new ConverterException('invalid array => amount');
        }

        $amount = new Amount();

        try {
            if (isset($a['cash'])) {
                $amount->setCash($a['cash']);
            }

            if (isset($a['cashless'])) {
                $amount->setCashless($a['cashless']);
            }

            if (isset($a['prepayment'])) {
                $amount->setPrepayment($a['prepayment']);
            }

            if (isset($a['postpayment'])) {
                $amount->setPostpayment($a['postpayment']);
            }

            if (isset($a['barter'])) {
                $amount->setBarter($a['barter']);
            }
        } catch (InvalidArgumentException $e) {
            throw new ConverterException('invalid array => amount: ' . $e->getMessage());
        }

        return $amount;
    }

    //######################################################################
    // Notify

    /**
     * Notify => array
     *
     * @throws ConverterException
     *
     * @param Notify $notify
     * @return array<string, mixed>
     */
    public function notifyToArray(Notify $notify): array
    {
        $a = [];

        if ($notify->getEmail()) {
            $a['email'] = $notify->getEmail();
        }

        if ($notify->getPhone()) {
            $a['phone'] =  $notify->getPhone();
        }

        if (!$a) {
            throw new ConverterException('invalid notify array');
        }

        return $a;
    }

    /**
     * array => Notify
     *
     * @throws ConverterException
     *
     * @param array<string, mixed> $a
     * @return Notify
     */
    public function notifyFromArray(array $a): Notify
    {
        $notify = new Notify();

        try {
            if (isset($a['email'])) {
                $notify->setEmail($a['email']);
            }

            if (isset($a['phone'])) {
                $notify->setPhone($a['phone']);
            }
        } catch (InvalidArgumentException $e) {
            throw new ConverterException($e->getMessage());
        }

        if (!isset($a['email']) && !isset($a['phone'])) {
            throw new ConverterException("invalid notify data fromArray");
        }

        return $notify;
    }

    //######################################################################
    // Customer

    /**
     * Customer => array
     *
     * @throws ConverterException
     *
     * @param Customer $customer
     * @return array<string, mixed>
     */
    public function customerToArray(Customer $customer): array
    {
        $a = [];

        if ($customer->getName()) {
            $a['name'] = $customer->getName();
        }

        if ($customer->getTin()) {
            $a['tin'] = $customer->getTin();
        }

        if (!$a) {
            throw new ConverterException('invalid customer => array: empty data');
        }

        return $a;
    }

    /**
     * array => Customer
     *
     * @throws ConverterException
     *
     * @param array<string, mixed> $a
     * @return Customer
     */
    public function customerFromArray(array $a): Customer
    {
        if (!isset($a['name']) && !isset($a['tin'])) {
            throw new ConverterException('invalid array => customer');
        }

        try {
            $customer = new Customer();

            if (isset($a['name'])) {
                $customer->setName($a['name']);
            }

            if (isset($a['tin'])) {
                $customer->setTin($a['tin']);
            }
        } catch (InvalidArgumentException $e) {
            throw new ConverterException($e->getMessage());
        }

        return $customer;
    }
}
