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
     * Маловерятно, но может быть (очень врядли), новый чек может сгенерировать uuid, который уже есть в системе фискализации. Для этих целей сделано несколько попыток отправки чека каждый раз с разным uuid.
     * 
     * @throws TransferException
     *
     * @param Receipt $receipt
     * @return void
     */
    public function fiscalizeProc(Receipt $receipt)
    {
        for($i=0; $i<10; ++$i)
        {
            try
            {
                $receipt = $this->transfer->sendReceipt($receipt);
                break;
            }
            catch(TransferException $e)
            {
                // если чек с таким uuid уже есть - устанавливаем новый
                if($e->getCode() == 409)
                    $receipt->setUUID(new UUID());

                // если чек с ошибками - прокидываем исключение
                else if((new ReceiptStatus($e->getCode()))->getCode() == ReceiptStatus::ERROR)
                    throw $e;

                /* иначе ситуации:
                    - чек имет статус REPEAT или ASSUME
                    - связь с сервером не удалась
                */
                else
                    break;
            }
        }
    }

    //######################################################################
    // PROTECTED
    //######################################################################

    protected $transfer;
};
