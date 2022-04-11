<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Storage\ReceiptFilter;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Collections\ReceiptItemCollection;
use Innokassa\MDK\Services\FiscalizationBaseAbstract;
use Innokassa\MDK\Exceptions\Services\ManualException;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Базовая реализация ManualInterface
 */
class ManualBase extends FiscalizationBaseAbstract implements ManualInterface
{
    public function __construct(
        ReceiptStorageInterface $receiptStorage,
        TransferInterface $transfer,
        SettingsInterface $settings
    ) {
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
        Amount $amount = null
    ): Receipt {
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

        try {
            $this->fiscalizeProc($receipt);
        } catch (TransferException $e) {
            throw new ManualException($e->getMessage(), $e->getCode());
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
        Amount $amount = null
    ): Receipt {
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

        // подсчет поступлений денег по заказу
        $receiptsComing = $this->receiptStorage->getCollection(
            (new ReceiptFilter())
                ->setOrderId($orderId)
                ->setType(ReceiptType::COMING)
                ->setStatus(ReceiptStatus::COMPLETED)
        );
        $amountComing = 0;
        foreach ($receiptsComing as $rec) {
            $amount = $rec->getAmount();
            $amountComing += $amount->get(Amount::CASH) + $amount->get(Amount::CASHLESS);
        }

        // подсчет возвратов денег по заказу
        $receiptsRefund = $this->receiptStorage->getCollection(
            (new ReceiptFilter())
                ->setOrderId($orderId)
                ->setType(ReceiptType::REFUND_COMING)
                ->setStatus(ReceiptStatus::COMPLETED)
        );
        $amountRefund = 0;
        foreach ($receiptsRefund as $rec) {
            $amount = $rec->getAmount();
            $amountRefund += $amount->get(Amount::CASH) + $amount->get(Amount::CASHLESS);
        }

        // текущая сумма возврата
        $amount = $receipt->getAmount();
        $amountNewRefund = $amount->get(Amount::CASH) + $amount->get(Amount::CASHLESS);
        $amountNewRefund = intval(round($amountNewRefund, 2) * 100);

        // текущий баланс заказа
        $amountBalance = $amountComing - $amountRefund;
        $amountBalance = intval(round($amountBalance, 2) * 100);

        // если сумма нового возврата превышает остаток по заказу - нельзя пробить чек возврата
        if ($amountNewRefund > $amountBalance) {
            $amountNewRefundRub = round(($amountNewRefund / 100.0), 2);
            $amountBalanceRub = round(($amountBalance / 100.0), 2);
            throw new ManualException(
                "Cумма нового возврата '$amountNewRefundRub' превышает остаток по заказу '$amountBalanceRub'"
            );
        }

        try {
            $this->fiscalizeProc($receipt);
        } catch (TransferException $e) {
            throw new ManualException($e->getMessage(), $e->getCode());
        }

        $this->receiptStorage->save($receipt);

        return $receipt;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $receiptStorage;
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
}
