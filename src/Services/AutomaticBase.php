<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Entities\UUID;
use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Storage\ReceiptFilter;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Entities\ReceiptAdapterInterface;
use Innokassa\MDK\Exceptions\Services\AutomaticException;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Базовая реализация AutomaticInterface
 */
class AutomaticBase implements AutomaticInterface
{
    /**
     * @param SettingsInterface $settings
     * @param ReceiptStorageInterface $receiptStorage
     * @param TransferInterface $transfer
     * @param ReceiptAdapterInterface $receiptAdapter
     */
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

        if ($receipts->getByType(ReceiptType::COMING, $receiptSubType)) {
            throw new AutomaticException("В заказе уже есть такой чек");
        }

        if ($receipts->getByType(ReceiptType::COMING, ReceiptSubType::FULL)) {
            throw new AutomaticException("В заказе уже есть второй чек");
        }

        try {
            $total = $this->receiptAdapter->getTotal($orderId);
            $items = $this->receiptAdapter->getItems($orderId, $receiptSubType);
            $customer = $this->receiptAdapter->getCustomer($orderId);
            $notify = $this->receiptAdapter->getNotify($orderId);
        } catch (InvalidArgumentException $e) {
            throw $e;
        }

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
        $receipt->setItems($items);
        $receipt->setCustomer($customer);
        $receipt->setNotify($notify);
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
    // PRIVATE
    //######################################################################

    /** @var SettingsInterface */
    private $settings = null;

    /** @var ReceiptStorageInterface */
    private $receiptStorage = null;

    /** @var ReceiptAdapterInterface */
    private $receiptAdapter = null;

    /** @var TransferInterface */
    private $transfer = null;
}
