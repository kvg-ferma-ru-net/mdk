<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Storage\ReceiptFilter;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Collections\ReceiptCollection;

use Innokassa\MDK\Exceptions\TransferException;

/**
 * Базовая реализация PipelineInterface.
 * Только один инстанс каждого публичного метода будет работать (используется файловая блокировка)
 */
class PipelineBase implements PipelineInterface
{
    /** Лок файл для обработки очереди принятых чеков */
    const LOCK_FILE_ACCEPTED = __DIR__.'/../../.updateAccepted';

    /** Лок файл для обработки непринятых чеков */
    const LOCK_FILE_UNACCEPTED = __DIR__.'/../../.updateUnaccepted';

    /** Максимальное количество последовательных вызовов, при превышении которого обработка прервется */
    const MAX_COUNT_ERR = 10;

    /** Количество выбираемых элементов из БД */
    const COUNT_SELECT = 50;

    //######################################################################

    public function __construct(ReceiptStorageInterface $receiptStorage, TransferInterface $transfer)
    {
        $this->receiptStorage = $receiptStorage;
        $this->transfer = $transfer;
    }

    /**
     * @inheritDoc
     */
    public function updateAccepted(): bool
    {
        $fp = fopen(self::LOCK_FILE_ACCEPTED, "w+");
        if (!flock($fp, LOCK_EX | LOCK_NB))
            return false;

        $this->runCycle([ReceiptStatus::WAIT, ReceiptStatus::ASSUME], 'processingAccepted');

        return true;
    }

    /**
     * @inheritDoc
     */
    public function updateUnaccepted(): bool
    {
        $fp = fopen(self::LOCK_FILE_UNACCEPTED, "w+");
        if (!flock($fp, LOCK_EX | LOCK_NB))
            return false;
        
        $this->runCycle([ReceiptStatus::PREPARED, ReceiptStatus::REPEAT], 'processingUnaccepted');

        return true;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $receiptStorage = null;
    private $transfer = null;
    private $countError = 0;

    //######################################################################

    /**
     * Запуск цикла обработки чеков
     *
     * @param array $statuses массив статусов, чеки которых надо обработать
     * @param string $method
     * @return void
     */
    private function runCycle(array $statuses, string $method)
    {
        foreach($statuses as $status)
        {
            $idLast = 0;
            do
            {
                $receipts = $this->receiptStorage->getCollection(
                    (new ReceiptFilter())
                        ->setId($idLast, ReceiptFilter::OP_GT)
                        ->setStatus($status),
                    self::COUNT_SELECT
                );
                $idLast = $this->$method($receipts);
            }
            while($receipts->count() == self::COUNT_SELECT && $idLast > 0);

            if($receipts->count() > 0 && $idLast == 0)
                break;
        }
    }

    /**
     * Обработка коллекции принятых чеков
     *
     * @param ReceiptCollection $receipts
     * @return integer наибольший идентификатор чека из коллекции
     */
    private function processingAccepted(ReceiptCollection $receipts): int
    {
        $idLast = 0;
        foreach($receipts as $receipt)
		{
            $idLast = ($receipt->getId() > $idLast ? $receipt->getId() : $idLast);

            try
            {
                $receipt = $this->transfer->getReceipt($receipt);
            }
            catch(TransferException $e)
            {
                /* 
                    если чека с таким uuid нет на сервере (ASSSUME), 
                    тогда заканчиваем работу цикла - чек становится REPEAT
                */
                if($e->getCode() == 404)
                    continue;
            }
            finally
            {
                // в любом случае сохраняем чек
                $this->receiptStorage->save($receipt);
            }

            // если нельзя больше продолжать проверять статусы чеков - останавливаем цикл
            if(!$this->canContinue($receipt->getStatus()->getCode()))
                return 0;
		}

        return $idLast;
    }

    /**
     * Обработка коллекции непринятых чеков
     *
     * @param ReceiptCollection $receipts
     * @return integer наибольший идентификатор чека из коллекции
     */
    private function processingUnaccepted(ReceiptCollection $receipts): int
    {
        $idLast = 0;
        foreach($receipts as $receipt)
		{
            $idLast = ($receipt->getId() > $idLast ? $receipt->getId() : $idLast);

            try
            {
                $receipt = $this->transfer->sendReceipt($receipt);
            }
            catch(TransferException $e)
            {
                // если чек с таким id уже был принят сервером фискализации - узнаем его статус
                if($e->getCode() == 409)
                {
                    try
                    {
                        $receipt = $this->transfer->getReceipt($receipt);
                    }
                    catch(TransferException $e)
                    {}
                }

                // иные коды ответов не обрабатываем
            }
            finally
            {
                // в любом случае сохраняем чек
                $this->receiptStorage->save($receipt);
            }

            // если нельзя больше продолжать проверять статусы чеков - останавливаем цикл
            if(!$this->canContinue($receipt->getStatus()->getCode()))
                return 0;
		}

        return $idLast;
    }

    //######################################################################

    /**
     * Можно ли продолжать обработку
     *
     * @param integer $receiptStatus
     * @return boolean
     */
    private function canContinue(int $receiptStatus): bool
    {
        // ошибочные статусы
        static $errStatuses = [ReceiptStatus::REPEAT, ReceiptStatus::ASSUME];

        /* 
            проверка количества последовательных неудачных ответов
            если их будет >= 10 то надо прервать цикл, иначе неудачные ответы это норма
        */
        if(
            $this->countError >= 0
            && array_search($receiptStatus, $errStatuses) !== false
        )
            ++$this->countError;
        else
            $this->countError = -1;

        return ($this->countError < self::MAX_COUNT_ERR);
    }
};
