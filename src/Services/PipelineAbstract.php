<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Settings\SettingsConn;
use Innokassa\MDK\Storage\ReceiptFilter;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\SettingsException;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Collections\ReceiptCollection;
use Innokassa\MDK\Storage\ReceiptStorageInterface;

/**
 * Абстрактный класс с базовой реализацей PipelineInterface.
 */
abstract class PipelineAbstract implements PipelineInterface
{
    /** Количество выбираемых элементов из БД */
    public const COUNT_SELECT = 50;

    //######################################################################

    /**
     * @inheritDoc
     *
     * Только один инстанс класса будет работать одновременно (используется файловая блокировка)
     */
    public function update(string $file): bool
    {
        $fp = fopen($file, "w+");
        if (!$fp || !flock($fp, LOCK_EX | LOCK_NB)) {
            return false;
        }

        $idLast = 0;
        do {
            $receipts = $this->receiptStorage->getCollection(
                (new ReceiptFilter())
                    ->setId($idLast, ReceiptFilter::OP_GT)
                    ->setStatus(ReceiptStatus::COMPLETED, ReceiptFilter::OP_NOTEQ)
                    ->setAvailable(true),
                self::COUNT_SELECT
            );
            $idLast = $this->processing($receipts);
        } while ($receipts->count() == self::COUNT_SELECT && $idLast > 0);

        unlink($file);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function monitoring(string $file, string $columnStratTime = 'start_time'): bool
    {
        $countCompleted = $this->receiptStorage->count(
            (new ReceiptFilter())->setStatus(ReceiptStatus::COMPLETED)
        );
        $countPrepared = $this->receiptStorage->count(
            (new ReceiptFilter())->setStatus(ReceiptStatus::PREPARED)
        );
        $countAccepted = $this->receiptStorage->count(
            (new ReceiptFilter())->setStatus(ReceiptStatus::ACCEPTED)
        );
        $countUnauth = $this->receiptStorage->count(
            (new ReceiptFilter())->setStatus(ReceiptStatus::UNAUTH)
        );
        $countAssume = $this->receiptStorage->count(
            (new ReceiptFilter())->setStatus(ReceiptStatus::ASSUME)
        );
        $countError = $this->receiptStorage->count(
            (new ReceiptFilter())->setStatus(ReceiptStatus::ERROR)
        );
        $countExpired = $this->receiptStorage->count(
            (new ReceiptFilter())->setStatus(ReceiptStatus::EXPIRED)
        );


        $maxTimeCompleted = $this->receiptStorage->max(
            (new ReceiptFilter())->setStatus(ReceiptStatus::COMPLETED),
            $columnStratTime
        );
        $timeCompleted = 0;
        if ($maxTimeCompleted !== null) {
            $timeCompleted = time() - intval(strtotime($maxTimeCompleted));
        }

        $minTimePrepared = $this->receiptStorage->min(
            (new ReceiptFilter())->setStatus(ReceiptStatus::PREPARED),
            $columnStratTime
        );
        $durationPrepared = 0;
        if ($minTimePrepared !== null) {
            $durationPrepared = time() - intval(strtotime($minTimePrepared));
        }

        $minTimeAccepted = $this->receiptStorage->min(
            (new ReceiptFilter())->setStatus(ReceiptStatus::ACCEPTED),
            $columnStratTime
        );
        $durationAccepted = 0;
        if ($minTimeAccepted !== null) {
            $durationAccepted = time() - intval(strtotime($minTimeAccepted));
        }

        $minTimeUnauth = $this->receiptStorage->min(
            (new ReceiptFilter())->setStatus(ReceiptStatus::UNAUTH),
            $columnStratTime
        );
        $durationUnauth = 0;
        if ($minTimeUnauth !== null) {
            $durationUnauth = time() - intval(strtotime($minTimeUnauth));
        }

        $minTimeAssume = $this->receiptStorage->min(
            (new ReceiptFilter())->setStatus(ReceiptStatus::ASSUME),
            $columnStratTime
        );
        $durationAssume = 0;
        if ($minTimeAssume !== null) {
            $durationAssume = time() - intval(strtotime($minTimeAssume));
        }

        $minTimeError = $this->receiptStorage->min(
            (new ReceiptFilter())->setStatus(ReceiptStatus::ERROR),
            $columnStratTime
        );
        $durationError = 0;
        if ($minTimeError !== null) {
            $durationError = time() - intval(strtotime($minTimeError));
        }

        $minTimeExpired = $this->receiptStorage->min(
            (new ReceiptFilter())->setStatus(ReceiptStatus::EXPIRED),
            $columnStratTime
        );
        $durationExpired = 0;
        if ($minTimeExpired) {
            $durationExpired = time() - intval(strtotime($minTimeExpired));
        }

        $content = [
            '# TYPE file_time counter',
            '# TYPE completed_count counter',
            '# TYPE completed_time gauge',
            '# TYPE prepared_count gauge',
            '# TYPE prepared_duration gauge',
            '# TYPE accepted_count gauge',
            '# TYPE accepted_duration gauge',
            '# TYPE unauth_count gauge',
            '# TYPE unauth_duration gauge',
            '# TYPE assume_count gauge',
            '# TYPE assume_duration gauge',
            '# TYPE error_count gauge',
            '# TYPE error_duration gauge',
            '# TYPE expired_count gauge',
            '# TYPE expired_duration gauge',
        ];

        $content[] = sprintf('file_time %d', time());
        $content[] = sprintf('completed_count %d', $countCompleted);
        $content[] = sprintf('completed_time %d', $timeCompleted);
        $content[] = sprintf('prepared_count %d', $countPrepared);
        $content[] = sprintf('prepared_duration %d', $durationPrepared);
        $content[] = sprintf('accepted_count %d', $countAccepted);
        $content[] = sprintf('accepted_duration %d', $durationAccepted);
        $content[] = sprintf('unauth_count %d', $countUnauth);
        $content[] = sprintf('unauth_duration %d', $durationUnauth);
        $content[] = sprintf('assume_count %d', $countAssume);
        $content[] = sprintf('assume_duration %d', $durationAssume);
        $content[] = sprintf('error_count %d', $countError);
        $content[] = sprintf('error_duration %d', $durationError);
        $content[] = sprintf('expired_count %d', $countExpired);
        $content[] = sprintf('expired_duration %d', $durationExpired);
        $content[] = '';

        $result = boolval(file_put_contents($file, implode("\n", $content)));

        return $result;
    }

    //######################################################################
    // PROTECTED
    //######################################################################

    /** @var ReceiptStorageInterface */
    protected $receiptStorage = null;

    /** @var TransferInterface */
    protected $transfer = null;

    //######################################################################

    /**
     * Обработка коллекции принятых чеков
     *
     * @param ReceiptCollection $receipts
     * @return integer наибольший идентификатор чека из коллекции
     */
    protected function processing(ReceiptCollection $receipts): int
    {
        // ошибочные статусы
        static $errStatuses = [ReceiptStatus::ASSUME];
        $countError = 0;

        $idLast = 0;
        foreach ($receipts as $receipt) {
            $idLast = ($receipt->getId() > $idLast ? $receipt->getId() : $idLast);

            // если чек не был принят сервером и время фискализации истекло
            if (!$receipt->getAccepted() && $receipt->isExpired()) {
                $receipt->setStatus(new ReceiptStatus(ReceiptStatus::EXPIRED));
                $this->receiptStorage->save($receipt);
                continue;
            }

            try {
                $receipt = $this->transfer->sendReceipt(
                    $this->extrudeConn($receipt),
                    $receipt
                );
            } catch (TransferException $e) {
            } catch (SettingsException $e) {
                // TODO
            } finally {
                $receiptStatus = $receipt->getStatus();
                if (!$receiptStatus || array_search($receiptStatus->getCode(), $errStatuses) !== false) {
                    $countError++;
                }
                // в любом случае сохраняем чек
                $this->receiptStorage->save($receipt);
            }
        }

        return ($countError == $receipts->count() ? 0 : $idLast);
    }

    /**
     * Извлечь настройки соединения на основании данных чека
     *
     * @throws SettingsException
     *
     * @param Receipt $receipt
     * @return SettingsConn
     */
    abstract protected function extrudeConn(Receipt $receipt): SettingsConn;
}
