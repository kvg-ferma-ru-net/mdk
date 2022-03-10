<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Storage\ReceiptFilter;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Entities\ReceiptAdapterInterface;
use Innokassa\MDK\Services\FiscalizationBaseAbstract;
use Innokassa\MDK\Exceptions\Services\AutomaticException;

/**
 * Базовая реализация AutomaticInterface
 */
class AutomaticBase extends FiscalizationBaseAbstract implements AutomaticInterface
{
    public function __construct(
        SettingsInterface $settings,
        ReceiptStorageInterface $receiptStorage,
        TransferInterface $transfer,
        ReceiptAdapterInterface $receiptAdapter
    ) {
        $this->settings = $settings;
        $this->receiptStorage = $receiptStorage;
        $this->transfer = $transfer;
        $this->receiptAdapter = $receiptAdapter;
    }

    /**
     * @inheritDoc
     */
    public function fiscalize(string $orderId, int $receiptSubType = null): Receipt
    {
        $receiptsHand = $this->receiptStorage->getCollection(
            (new ReceiptFilter())
                ->setOrderId($orderId)
                ->setSubType(ReceiptSubType::HAND)
        );

        if ($receiptsHand->count() > 0) {
            throw new AutomaticException(
                'Заказ имеет чеки пробитые вручную, невозможно продолжить автоматическую фискализацию'
            );
        }

        $receipts = $this->receiptStorage->getCollection(
            (new ReceiptFilter())->setOrderId($orderId)
        );

        if ($receiptSubType === null) {
            $receiptSubType = (
                !$receipts->getByType(ReceiptType::COMING, ReceiptSubType::PRE) && !$this->settings->getOnly2()
                ? ReceiptSubType::PRE
                : ReceiptSubType::FULL
            );
        }

        if ($receipts->getByType(ReceiptType::REFUND_COMING, ReceiptSubType::FULL)) {
            throw new AutomaticException("В заказе уже есть чек возврата второго чека");
        }

        if ($receipts->getByType(ReceiptType::COMING, $receiptSubType)) {
            throw new AutomaticException("В заказе уже есть такой чек");
        }

        if ($receipts->getByType(ReceiptType::COMING, ReceiptSubType::FULL)) {
            throw new AutomaticException("В заказе уже есть второй чек");
        }

        $total = $this->receiptAdapter->getTotal($orderId);
        $amount = new Amount();

        // если пробиваем второй чек и был чек предоплаты
        if (
            $receiptSubType == ReceiptSubType::FULL
            && $receipts->getByType(ReceiptType::COMING, ReceiptSubType::PRE)
        ) {
            $amount->set(Amount::PREPAYMENT, $total);
        } else {
            $amount->set(Amount::CASHLESS, $total);
        }

        $receipt = new Receipt();
        $receipt->setOrderId($orderId);
        $receipt->setType(ReceiptType::COMING);
        $receipt->setSubType($receiptSubType);
        $receipt->setItems($this->receiptAdapter->getItems($orderId, $receiptSubType));
        $receipt->setCustomer($this->receiptAdapter->getCustomer($orderId));
        $receipt->setNotify($this->receiptAdapter->getNotify($orderId));
        $receipt->setAmount($amount);
        $receipt->setTaxation($this->settings->getTaxation());
        $receipt->setLocation($this->settings->getLocation());
        $receipt->setCashbox($this->settings->getCashbox());

        try {
            $this->fiscalizeProc($receipt);
        } catch (TransferException $e) {
            throw $e;
        }

        $this->receiptStorage->save($receipt);

        return $receipt;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $settings = null;
    private $receiptStorage = null;
    private $receiptAdapter = null;
}
