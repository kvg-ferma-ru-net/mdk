<?php

namespace Innokassa\MDK\Net;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Entities\ConverterAbstract;
use Innokassa\MDK\Entities\Primitives\Notify;

use Innokassa\MDK\Exceptions\ConverterException;

/**
 * Реализация интерфейса ConverterAbstract для Pangaea v2 
 * 
 * @link https://api.kassavoblake.com/v2/docs/pangaea_api.html
 */
class ConverterV2 extends ConverterAbstract
{
    /**
     * @inheritDoc
     */
    public function receiptToArray(Receipt $receipt): array
    {
        $a = [];

        if($receipt->getItems()->count() == 0)
            throw new ConverterException("uninitialize required field 'item'");

        if(!$receipt->getTaxation())
            throw new ConverterException("uninitialize required field 'taxation'");

        if(!$receipt->getAmount())
            throw new ConverterException("uninitialize required field 'amount'");

        if(!$receipt->getNotify())
            throw new ConverterException("uninitialize required field 'notify'");

        if(!$receipt->getLocation())
            throw new ConverterException("uninitialize required field 'location'");

        $a['type'] = $receipt->getType();
        $a['items'] = $this->itemsToArray($receipt->getItems());
        $a['taxation'] = $receipt->getTaxation();
        $a['amount'] = $this->amountToArray($receipt->getAmount());
        $a['notify'] = $this->notifyToArray($receipt->getNotify());
        $a['loc'] = [
            'billing_place' => $receipt->getLocation()
        ];

        if($receipt->getCustomer())
            $a['customer'] = $this->customerToArray($receipt->getCustomer());

        return $a;
    }

    /**
     * @inheritDoc
     */
    public function receiptFromArray(array $a): Receipt
    {
        throw new ConverterException('unsupported '.__CLASS__.'::'.__METHOD__);
    }

    //######################################################################

    public function notifyToArray(Notify $notify): array
    {
        $a = [];

        if($notify->getEmail())
            $a[] = [
                'type' => 'email',
                'value' => $notify->getEmail()
            ];

        if($notify->getPhone())
            $a[] = [
                'type' => 'phone',
                'value' => $notify->getPhone()
            ];

        if(!$a)
            throw new ConverterException('invalid notify array');

        return $a;
    }

    public function notifyFromArray(array $a): Notify
    {
        throw new ConverterException('unsuported '.__CLASS__.'::'.__METHOD__);
    }
};
