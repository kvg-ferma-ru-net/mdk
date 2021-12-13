<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\UUID;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\TransferException;

/**
 * Абстрактный класс процедур фискализации
 */
abstract class FiscalizeProcAbstract
{
    /**
     * Процедура отправки нового чека.
     * Маловерятно, но может быть (но очень врядли), новый чек может сгенерировать uuid которые уже есть в системе фискализации. Для этих целей сделано несколько попыток отправки чека каждый раз с разным uuid.
     * 
     * @throws TransferException
     *
     * @param TransferInterface $transfer
     * @param Receipt $receipt
     * @return void
     */
    public function proc(TransferInterface $transfer, Receipt $receipt)
    {
        for($i=0; $i<10; ++$i)
        {
            try{
                $receipt = $transfer->sendReceipt($receipt);
                break;
            }
            catch(TransferException $e){
                if((new ReceiptStatus($e->getCode()))->getCode() == ReceiptStatus::ERROR)
                    throw $e;
                if($e->getCode() == 409)
                    $receipt->setUUID(new UUID());
            }
        }
    }
};
