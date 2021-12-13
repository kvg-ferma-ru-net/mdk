<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Collections\ReceiptItemCollection;

use Innokassa\MDK\Services\FiscalizeProcAbstract;
use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Settings\SettingsInterface;

use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Storage\ReceiptFilter;

use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Exceptions\Services\ManualException;

/**
 * Базовая реализация ManualInterface
 */
class ManualBase extends FiscalizeProcAbstract implements ManualInterface
{
    public function __construct(
        ReceiptStorageInterface $receiptStorage, 
        TransferInterface $transfer,
        SettingsInterface $settings
    )
    {
        $this->receiptStorage = $receiptStorage;
        $this->transfer = $transfer;
        $this->settings = $settings;
    }

    /**
     * @inheritDoc
     */
    public function fiscalize(
        string $orderId, 
        ReceiptItemCollection $items, 
        Notify $notify, 
        Amount $amount=null
    ): Receipt
    {
        $receipt = new Receipt();
        $receipt->setType(ReceiptType::COMING)
                ->setSubType(ReceiptSubType::HAND)
                ->setItems($items)
                ->setNotify($notify)
                ->setAmount(
                    ($amount ? $amount : new Amount(Amount::CASHLESS, $items->getAmount()))
                )
                ->setOrderId($orderId);
        $receipt = $this->supplementReceipt($receipt);

        /*for($i=0; $i<10; ++$i)
        {
            try{
                $receipt = $this->transfer->sendReceipt($receipt);
                break;
            }
            catch(TransferException $e){
                if((new ReceiptStatus($e->getCode()))->getCode() == ReceiptStatus::ERROR)
                    throw new ManualException($e->getMessage());
                if($e->getCode() == 409)
                    $receipt->setUUID(new UUID());
            }
        }*/

        try{
            $this->proc($this->transfer, $receipt);
        }
        catch(TransferException $e){
            throw new ManualException($e->getMessage());
        }
        
        $this->receiptStorage->save($receipt);

        return $receipt;
    }

    /**
     * @inheritDoc
     */
    public function refund(
        string $orderId, 
        ReceiptItemCollection $items, 
        Notify $notify, 
        Amount $amount=null
    ): Receipt
    {
        $receipt = new Receipt();
        $receipt->setType(ReceiptType::REFUND_COMING)
                ->setSubType(ReceiptSubType::HAND)
                ->setItems($items)
                ->setNotify($notify)
                ->setAmount(
                    ($amount ? $amount : new Amount(Amount::CASHLESS, $items->getAmount()))
                )
                ->setOrderId($orderId);
        $receipt = $this->supplementReceipt($receipt);

        $receiptsComing = $this->receiptStorage->getCollection(
            (new ReceiptFilter())
                ->setOrderId($orderId)
                ->setType(ReceiptType::COMING)
        );
        $receiptsRefund = $this->receiptStorage->getCollection(
            (new ReceiptFilter())
                ->setOrderId($orderId)
                ->setType(ReceiptType::REFUND_COMING)
        );

        $amountNewRefund = $receipt->getItems()->getAmount();
        $amountBalance = $receiptsComing->getAmount() - $receiptsRefund->getAmount();

        // если сумма нового возврата превышает остаток по заказу - нельзя пробить чек возврата
        if($amountNewRefund > $amountBalance)
            throw new ManualException("Cумма нового возврата '$amountNewRefund' превышает остаток по заказу '$amountBalance'");

        /*try{
            $receipt = $this->transfer->sendReceipt($receipt);
        }
        catch(TransferException $e){
            if((new ReceiptStatus($e->getCode()))->getCode() == ReceiptStatus::ERROR)
                throw new ManualException($e->getMessage());
        }*/

        try{
            $this->proc($this->transfer, $receipt);
        }
        catch(TransferException $e){
            throw new ManualException($e->getMessage());
        }
        
        $this->receiptStorage->save($receipt);

        return $receipt;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $receiptStorage;
    private $transfer;
    private $settings;

    //######################################################################

    /**
     * Дополнение чека данными из настроек
     * 
     * @throws InvalidArgumentException
     *
     * @param Receipt $receipt
     * @return Receipt
     */
    private function supplementReceipt(Receipt $receipt): Receipt
    {
        $receipt->setTaxation($this->settings->getTaxation());
        $receipt->setLocation($this->settings->getLocation());
        $receipt->setCashbox($this->settings->getCashbox());

        return $receipt;
    }
};
