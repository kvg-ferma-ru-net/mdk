<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\SettingsException;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Exceptions\Services\PrinterException;

/**
 * Базовая реализация ConnectorInterface
 */
class ConnectorBase implements ConnectorInterface
{
    public function __construct(ReceiptStorageInterface $receiptStorage, TransferInterface $transfer)
    {
        $this->transfer = $transfer;
        $this->receiptStorage = $receiptStorage;
    }

    /**
     * @inheritDoc
     */
    public function testSettings(SettingsInterface $settings): bool
    {
        try{
            $response = $this->transfer->getCashBox();
        }
        catch(TransferException $e){
            if($e->getCode() >= 500)
                throw new SettingsException('Сервер временно недоступен, попробуйте позже', $e->getCode());
            else
                throw new SettingsException('Неверные авторизационные данные', $e->getCode());
        }

        if(!($response->taxation & $settings->getTaxation()))
        {
            $taxations = Taxation::all();
            $included = [];
            foreach($taxations as $taxation)
            {
                if($response->taxation & $taxation->getCode())
                    $included[] = $taxation->getName();
            }

            $sListTaxations = implode(", ", $included);
            $error = "Указанный налог не может быть применен, доступные налогообложения: $sListTaxations";
            throw new SettingsException($error);
        }
        else if(array_search($settings->getLocation(), $response->billing_place_list) === false)
        {
            $sListPlaces = implode(", ", $response->billing_place_list);
            $error = "Указанное место расчетов не может быть использовано, доступные: $sListPlaces";
            throw new SettingsException($error);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getReceiptLink(int $checkId): string
    {
        if(!($receipt = $this->receiptStorage->getOne($checkId)))
			throw new PrinterException("Не найден чек #{$checkId}");

		if($receipt->getStatus()->getCode() != ReceiptStatus::COMPLETED)
			throw new PrinterException("Чек #{$checkId} еще не фискализирован, но поставлен в очередь");

        return $this->transfer->getReceiptLink($receipt);
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $transfer = null;
    private $receiptStorage = null;
};
