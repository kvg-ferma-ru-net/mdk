<?php

namespace Innokassa\MDK\Net;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Settings\SettingsConn;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
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
     * @param SettingsConn $settingsConn
     * @return \stdClass
     */
    public function getCashbox(SettingsConn $settingsConn): \stdClass;

    /**
     * Отправка чека на фискализацию
     *
     * @link https://api.innokassa.ru/v2/doc#c_groups__c_group_id__receipts_online_store_agent__receipt_id__post
     *
     * @throws TransferException
     *
     * @param SettingsConn $settingsConn
     * @param Receipt $receipt
     * @return ReceiptStatus
     */
    public function sendReceipt(SettingsConn $settingsConn, Receipt $receipt): ReceiptStatus;
}
