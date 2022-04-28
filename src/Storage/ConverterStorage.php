<?php

namespace Innokassa\MDK\Storage;

use Innokassa\MDK\Entities\UUID;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Entities\ConverterAbstract;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\ConverterException;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Базовая реализация интерфейса для хранилища (БД)
 */
class ConverterStorage extends ConverterAbstract
{
    /**
     * @inheritDoc
     */
    public function receiptToArray(Receipt $receipt): array
    {
        $a = [];

        if ($receipt->getItems()->count() == 0) {
            throw new ConverterException("uninitialize required field 'item'");
        }

        if (!$receipt->getTaxation()) {
            throw new ConverterException("uninitialize required field 'taxation'");
        }

        if (!$receipt->getAmount()) {
            throw new ConverterException("uninitialize required field 'amount'");
        }

        if (!$receipt->getNotify()) {
            throw new ConverterException("uninitialize required field 'notify'");
        }

        if (!$receipt->getLocation()) {
            throw new ConverterException("uninitialize required field 'location'");
        }

        $a['id'] = $receipt->getId();
        $a['uuid'] = $receipt->getUUID()->get();
        $a['cashbox'] = $receipt->getCashbox();
        $a['site_id'] = $receipt->getSiteId();
        $a['order_id'] = $receipt->getOrderId();
        $a['status'] = $receipt->getStatus()->GetCode();
        $a['type'] = $receipt->getType();
        $a['subtype'] = $receipt->getSubType();
        $a['items'] = $this->itemsToArray($receipt->getItems());
        $a['taxation'] = $receipt->getTaxation();
        $a['amount'] = $this->amountToArray($receipt->getAmount());
        $a['notify'] = $this->notifyToArray($receipt->getNotify());
        $a['location'] = $receipt->getLocation();

        if ($receipt->getCustomer()) {
            $a['customer'] = $this->customerToArray($receipt->getCustomer());
        } else {
            $a['customer'] = null;
        }

        return $a;
    }

    /**
     * @inheritDoc
     */
    public function receiptFromArray(array $a): Receipt
    {
        if (!$a) {
            throw new ConverterException('empty array for create receipt');
        }

        $fields = [
            'id',
            'subtype',
            'cashbox',
            'order_id',
            'site_id',
            'uuid',
            'status',
            'type',
            'items',
            'taxation',
            'amount',
            'customer',
            'notify',
            'location',
        ];

        if ($diff = array_diff($fields, array_keys($a))) {
            throw new ConverterException(
                'not complete array for create receipt, lacks fields [' . implode(', ', $diff) . ']'
            );
        }

        try {
            $receipt = new Receipt();
            $receipt
                ->setId($a['id'])
                ->setUUID(new UUID($a['uuid']))
                ->setCashbox($a['cashbox'])
                ->setOrderId($a['order_id'])
                ->setSiteId($a['site_id'])
                ->setStatus(new ReceiptStatus($a['status']))
                ->setType($a['type'])
                ->setItems($this->itemsFromArray($a['items']))
                ->setTaxation($a['taxation'])
                ->setAmount($this->amountFromArray($a['amount']))
                ->setNotify($this->notifyFromArray($a['notify']))
                ->setLocation($a['location']);

            if (isset($a['subtype']) && $a['subtype'] !== null) {
                $receipt->setSubType($a['subtype']);
            }

            if (isset($a['customer'])) {
                $receipt->setCustomer($this->customerFromArray($a['customer']));
            }
        } catch (InvalidArgumentException $e) {
            throw new ConverterException($e->getMessage());
        }

        return $receipt;
    }
}
