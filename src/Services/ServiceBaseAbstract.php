<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\UUID;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\TransferException;

/**
 * Абстрактный сервис фискализации
 */
abstract class ServiceBaseAbstract
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
            try{
                $receipt = $this->transfer->sendReceipt($receipt);
                break;
            }
            catch(TransferException $e){
                if($e->getCode() == 409)
                    $receipt->setUUID(new UUID());
                else if((new ReceiptStatus($e->getCode()))->getCode() == ReceiptStatus::ERROR)
                    throw $e;
            }
        }
    }

    //######################################################################
    // PROTECTED
    //######################################################################

    protected $transfer;
};
