<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Services\PrinterInterface;
use Innokassa\MDK\Storage\ReceiptStorageInterface;

use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Exceptions\Services\PrinterException;

/**
 * Базовая реализация PrinterInterface
 */
class PrinterBase implements PrinterInterface
{
    public function __construct(ReceiptStorageInterface $receiptStorage, TransferInterface $transfer)
    {
        $this->receiptStorage = $receiptStorage;
        $this->transfer = $transfer;
    }

    /**
     * @inheritDoc
     */
    public function print(int $checkId): string
    {
        if(!($receipt = $this->receiptStorage->getOne($checkId)))
			throw new PrinterException("Не найден чек #{$checkId}");

		if($receipt->getStatus()->getCode() != 0)
			throw new PrinterException("Чек #{$checkId} еще не фискализирован, но поставлен в очередь");

        try{
            $render = $this->transfer->renderReceipt($receipt);
        }
        catch(TransferException $e){
            throw new PrinterException($e->getMessage(), $e->getCode());
        }

        return $render;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $receiptStorage = null;
    private $transfer = null;
};
