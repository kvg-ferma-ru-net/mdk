<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Services\PrinterInterface;
use Innokassa\MDK\Storage\ReceiptStorageInterface;

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
    public function getLinkVerify(int $idReceipt): string
    {
        if(!($receipt = $this->receiptStorage->getOne($idReceipt)))
			throw new PrinterException("Не найден чек #{$idReceipt}");

		if($receipt->getStatus()->getCode() != ReceiptStatus::COMPLETED)
			throw new PrinterException("Чек #{$idReceipt} еще не фискализирован, но поставлен в очередь");

        return $this->transfer->getReceiptLink($receipt);
    }

    /**
     * @inheritDoc
     */
    public function getLinkRaw(Receipt $receipt): string
    {
        return $this->transfer->getReceiptLink($receipt);
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $receiptStorage = null;
    private $transfer = null;
};
