<?php

namespace Innokassa\MDK\Net;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Exceptions\TransferException;

/**
 * Трансфер для взаимодействия с сервером фискализации Pangaea API v2
 * @link https://api.innokassa.ru/v2/doc
 */
interface TransferInterface
{
    /**
     * Получение информации о группе касс
     *
     * @link https://api.innokassa.ru/v2/doc#c_groups__c_group_id_
     *
     * @throws TransferException
     *
     * @return object
     */
    public function getCashbox(): object;

    /**
     * Отправка чека на фискализацию
     *
     * @link https://api.innokassa.ru/v2/doc#c_groups__c_group_id__receipts_online_store_agent__receipt_id__post
     *
     * @throws TransferException
     *
     * @param Receipt $receipt
     * @param bool $needAgent нужен ли агентский запрос, касса должна быть агентской, а в чеке агентские данные
     * @return Receipt
     */
    public function sendReceipt(Receipt $receipt, bool $needAgent = false): Receipt;

    /**
     * Получение информации о чеке
     *
     * @link https://api.innokassa.ru/v2/doc#c_groups__c_group_id__receipts__receipt_id__get
     *
     * @throws TransferException
     *
     * @param Receipt $receipt
     * @return Receipt
     */
    public function getReceipt(Receipt $receipt): Receipt;
}
