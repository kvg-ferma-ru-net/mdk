<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Settings\SettingsConn;
use Innokassa\MDK\Settings\SettingsAbstract;
use Innokassa\MDK\Exceptions\SettingsException;
use Innokassa\MDK\Storage\ReceiptStorageInterface;

/**
 * Реализация PipelineInterface для модуля CMS
 */
class PipelineForModule extends PipelineAbstract
{
    /** Количество выбираемых элементов из БД */
    public const COUNT_SELECT = 50;

    //######################################################################

    /**
     * @param SettingsAbstract $settings
     * @param ReceiptStorageInterface $receiptStorage
     * @param TransferInterface $transfer
     */
    public function __construct(
        SettingsAbstract $settings,
        ReceiptStorageInterface $receiptStorage,
        TransferInterface $transfer
    ) {
        $this->receiptStorage = $receiptStorage;
        $this->transfer = $transfer;
        $this->settings = $settings;
    }

    //######################################################################
    // PROTECTED
    //######################################################################

    /** @var SettingsAbstract */
    protected $settings = null;

    //######################################################################

    /**
     * Извлечь настройки соединения на основании данных чека
     *
     * @throws SettingsException
     *
     * @param Receipt $receipt
     * @return SettingsConn
     */
    protected function extrudeConn(Receipt $receipt): SettingsConn
    {
        return $this->settings->extrudeConn($receipt->getSiteId());
    }
}
