<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Storage\ReceiptFilter;
use Innokassa\MDK\Settings\SettingsAbstract;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Collections\ReceiptCollection;
use Innokassa\MDK\Storage\ReceiptStorageInterface;

/**
 * Базовая реализация PipelineInterface.
 * Только один инстанс класса будет работать одновременно (используется файловая блокировка)
 */
class PipelineBase implements PipelineInterface
{
    /** Лок файл для обработки очереди принятых чеков */
    public const LOCK_FILE = __DIR__ . '/../../.pipeline';

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

    /**
     * @inheritDoc
     */
    public function update(): bool
    {
        $fp = fopen(self::LOCK_FILE, "w+");
        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            return false;
        }

        $idLast = 0;
        do {
            $receipts = $this->receiptStorage->getCollection(
                (new ReceiptFilter())
                    ->setId($idLast, ReceiptFilter::OP_GT)
                    ->setStatus([
                        ReceiptStatus::PREPARED,
                        ReceiptStatus::WAIT,
                        ReceiptStatus::ASSUME,
                        ReceiptStatus::REPEAT
                    ]),
                self::COUNT_SELECT
            );
            $idLast = $this->processing($receipts);
        } while ($receipts->count() == self::COUNT_SELECT && $idLast > 0);

        unlink(self::LOCK_FILE);

        return true;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    /** @var ReceiptStorageInterface */
    private $receiptStorage = null;

    /** @var TransferInterface */
    private $transfer = null;

    /** @var SettingsAbstract */
    private $settings = null;

    //######################################################################

    /**
     * Обработка коллекции принятых чеков
     *
     * @param ReceiptCollection $receipts
     * @return integer наибольший идентификатор чека из коллекции
     */
    private function processing(ReceiptCollection $receipts): int
    {
        // ошибочные статусы
        static $errStatuses = [ReceiptStatus::REPEAT, ReceiptStatus::ASSUME];
        $countError = 0;

        $idLast = 0;
        foreach ($receipts as $receipt) {
            $idLast = ($receipt->getId() > $idLast ? $receipt->getId() : $idLast);

            // если время фискализации истекло
            if (
                $receipt->getStatus()->getCode() == ReceiptStatus::REPEAT
                && $receipt->isExpired()
            ) {
                $receipt->setStatus(new ReceiptStatus(ReceiptStatus::EXPIRED));
                $this->receiptStorage->save($receipt);
                continue;
            }

            try {
                $receipt = $this->transfer->sendReceipt(
                    $this->settings->extrudeConn($receipt->getSiteId()),
                    $receipt
                );
            } catch (TransferException $e) {
            } finally {
                if (array_search($receipt->getStatus()->getCode(), $errStatuses) !== false) {
                    $countError++;
                }
                // в любом случае сохраняем чек
                $this->receiptStorage->save($receipt);
            }
        }

        return ($countError == $receipts->count() ? 0 : $idLast);
    }
}
