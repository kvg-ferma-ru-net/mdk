<?php

namespace Innokassa\MDK\Net;

use Innokassa\MDK\Entities\Receipt;

/**
 * Трансфер чеков для Pangaea API 2 
 * @link https://api.kassavoblake.com/v2/docs/pangaea_api.html
 */
interface TransferInterface
{
    /** 
     * Получение информации о группе касс
     * 
     * @link https://api.kassavoblake.com/v2/docs/pangaea_api.html#/c_groups/{c_group_id}
     * 
     * @throws TransferException
     * 
     * @return object
     */
	public function getCashbox(): object;

    /**
     * Отправка чека на фискализацию
     * 
     * @link https://api.kassavoblake.com/v2/docs/pangaea_api.html#c_groups__c_group_id__receipts_online_store__receipt_id__post
     *
     * @throws TransferException
     * 
     * @param Receipt $receipt
     * @return Receipt
     */
    public function sendReceipt(Receipt $receipt, $needAgent=false): Receipt;

    /**
     * Получение информации о чеке
     * 
     * @link https://api.kassavoblake.com/v2/docs/pangaea_api.html#/c_groups/{c_group_id}/receipts/{receipt_id}
     * 
     * @throws TransferException
     *
     * @param Receipt $receipt
     * @return Receipt
     */
    public function getReceipt(Receipt $receipt): Receipt;

    /**
     * Получить ссылку на рендер чека (без проверки статуса чека)
     *
     * @param Receipt $receipt
     * @return string
     */
    public function getReceiptLink(Receipt $receipt): string;
};
