<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Storage\ReceiptFilter;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Storage\ReceiptStorageInterface;

use Innokassa\MDK\Exceptions\TransferException;

/**
 * Базовая реализация PipelineInterface
 */
class PipelineBase implements PipelineInterface
{
    public function __construct(ReceiptStorageInterface $receiptStorage, TransferInterface $transfer)
    {
        $this->receiptStorage = $receiptStorage;
        $this->transfer = $transfer;
    }

    /**
     * @inheritDoc
     */
    public function updateAccepted()
    {
        usleep(rand(0, 750000));
        
        $receiptsAwait = $this->receiptStorage->getCollection(
            (new ReceiptFilter())->setStatus(ReceiptStatus::WAIT)
        );
        $receiptsAssume = $this->receiptStorage->getCollection(
            (new ReceiptFilter())->setStatus(ReceiptStatus::ASSUME)
        );
        $receipts = $receiptsAwait->merge($receiptsAssume);

		foreach($receipts as $receipt)
		{
            try{
                $receipt = $this->transfer->getReceipt($receipt);
            }
            catch(TransferException $e){}

			$this->receiptStorage->save($receipt);

            /* 
                если чек необходимо повторно отправить или сервер ответил 500-ыми ошибками -
                прерываем цикл, возможно проблемы в настройках или на сервере, нет смысла слать все чеки
            */
            if(
                $receipt->getStatus()->getCode() == ReceiptStatus::REPEAT
                || $receipt->getStatus()->getCode() == ReceiptStatus::ASSUME
            )
                break;
		}
    }

    /**
     * @inheritDoc
     */
    public function updateUnaccepted()
    {
        usleep(rand(0, 750000));

		$receipts = $this->receiptStorage->getCollection(
            (new ReceiptFilter())->setStatus(ReceiptStatus::REPEAT)
        );

		foreach($receipts as $receipt)
		{
            try{
                $receipt = $this->transfer->sendReceipt($receipt);
            }
            catch(TransferException $e){

                // если чек с таким id уже был принят сервером фискализации - узнаем его статус
                if($e->getCode() == 409)
                {
                    try{
                        $receipt = $this->transfer->getReceipt($receipt);
                    }
                    catch(TransferException $e){}
                }

                // иные коды ответов не обрабатываем
            }

            $this->receiptStorage->save($receipt);

            /* 
                если чек необходимо повторно отправить или сервер ответил 500-ыми ошибками -
                прерываем цикл, возможно проблемы в настройках или на сервере, нет смысла слать все чеки
            */
            if(
                $receipt->getStatus()->getCode() == ReceiptStatus::REPEAT 
                || $receipt->getStatus()->getCode() == ReceiptStatus::ASSUME
            )
                break;
		}
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $receiptStorage = null;
    private $transfer = null;
};
