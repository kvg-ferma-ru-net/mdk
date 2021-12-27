<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\UUID;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\TransferException;

/**
 * Базовый абстрактный класс фискализации
 */
abstract class FiscalizationBaseAbstract
{
    /**
     * Процедура отправки нового чека.
     * Маловерятно, но может быть, новый чек может сгенерировать uuid, который уже есть в системе фискализации.
     * Для этих целей сделано несколько попыток отправки чека каждый раз с разным uuid.
     *
     * @throws TransferException
     *
     * @param Receipt $receipt
     * @return void
     */
    public function fiscalizeProc(Receipt $receipt)
    {
        for ($i = 0; $i < 10; ++$i) {
            try {
                $receipt = $this->transfer->sendReceipt($receipt);
                break;
            } catch (TransferException $e) {
                if ($e->getCode() == 409) {
                    // если чек с таким uuid уже есть - устанавливаем новый
                    $receipt->setUUID(new UUID());
                } elseif ((new ReceiptStatus($e->getCode()))->getCode() == ReceiptStatus::ERROR) {
                    // если чек с ошибками - прокидываем исключение
                    throw $e;
                } else {
                    /* иначе ситуации:
                        - чек имет статус REPEAT или ASSUME
                        - связь с сервером не удалась
                    */
                    break;
                }
            }
        }
    }

    //######################################################################
    // PROTECTED
    //######################################################################

    protected $transfer;
}
