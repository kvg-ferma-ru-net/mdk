<?php

namespace Innokassa\MDK\Entities;

use Innokassa\MDK\Entities\UUID;
use Innokassa\MDK\Entities\ReceiptItem;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Entities\Atoms\ReceiptSubType;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Entities\Atoms\ReceiptType;
use Innokassa\MDK\Entities\Primitives\Amount;
use Innokassa\MDK\Entities\Primitives\Notify;
use Innokassa\MDK\Entities\Primitives\Customer;
use Innokassa\MDK\Collections\ReceiptItemCollection;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Сущность "чек"
 */
class Receipt
{
    public function __construct()
    {
        $this->uuid = new UUID();

        $this->items = new ReceiptItemCollection();
        $this->type = new ReceiptType(ReceiptType::COMING);
        $this->subType = new ReceiptSubType(ReceiptSubType::HAND);
        $this->status = new ReceiptStatus(ReceiptStatus::PREPARED);
    }

    //######################################################################

    /**
     * Установить локальный идентификатор чека
     *
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Получить локальный идентификатор чека
     *
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    //**********************************************************************

    /**
     * Установить новый UUID
     *
     * @param UUID $uuid
     * @return self
     */
    public function setUUID(UUID $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * Получить текущий UUID
     *
     * @return UUID
     */
    public function getUUID(): UUID
    {
        return $this->uuid;
    }

    //**********************************************************************

    /**
     * Установить группу касс
     *
     * @param string $cashbox
     * @return self
     */
    public function setCashbox(string $cashbox): self
    {
        $this->cashbox = $cashbox;
        return $this;
    }

    /**
     * Получить группу касс
     *
     * @return string
     */
    public function getCashbox(): string
    {
        return $this->cashbox;
    }

    //**********************************************************************

    /**
     * Установить идентификатор сайта, если интеграция должна поддерживать мультисайтовость
     *
     * @param string $siteId
     * @return self
     */
    public function setSiteId(string $siteId): self
    {
        $this->siteId = $siteId;
        return $this;
    }

    /**
     * Получить идентификатор сайта
     *
     * @return string
     */
    public function getSiteId(): string
    {
        return $this->siteId;
    }

    //**********************************************************************

    /**
     * Установить идентификатор заказа
     *
     * @param string $orderId
     * @return self
     */
    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * Получить идентификатор заказа
     *
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    //**********************************************************************

    /**
     * Установить статус чека
     *
     * @param ReceiptStatus $receiptStatus
     * @return self
     */
    public function setStatus(ReceiptStatus $receiptStatus): self
    {
        $this->status = $receiptStatus;
        return $this;
    }

    /**
     * Получить статус чека
     *
     * @return ReceiptStatus|null
     */
    public function getStatus(): ?ReceiptStatus
    {
        return $this->status;
    }

    //**********************************************************************

    /**
     * Установить тип чека
     *
     * @throws InvalidArgumentException
     *
     * @param int $type
     * @return self
     */
    public function setType(int $type): self
    {
        $this->type = new ReceiptType($type);
        return $this;
    }

    /**
     * Получить тип чека
     *
     * @return int|null
     */
    public function getType(): ?int
    {
        return ($this->type ? $this->type->getCode() : null);
    }

    //**********************************************************************

    /**
     * Установить подтип чека
     *
     * @throws InvalidArgumentException
     *
     * @param int $subType
     * @return self
     */
    public function setSubType(int $subType): self
    {
        $this->subType = new ReceiptSubType($subType);
        return $this;
    }

    /**
     * Получить подтип чека
     *
     * @return int|null
     */
    public function getSubType(): ?int
    {
        return ($this->subType ? $this->subType->getCode() : null);
    }

    //**********************************************************************

    /**
     * Установить налогообложение
     *
     * @throws InvalidArgumentException
     *
     * @param int $taxation
     * @return self
     */
    public function setTaxation(int $taxation): self
    {
        $this->taxation = new Taxation($taxation);
        return $this;
    }

    /**
     * Получить налогообложение
     *
     * @return int|null
     */
    public function getTaxation(): ?int
    {
        return ($this->taxation ? $this->taxation->getCode() : null);
    }

    //**********************************************************************

    /**
     * Установить расчет по чеку
     *
     * @param Amount $amount
     * @return self
     */
    public function setAmount(Amount $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Получить расчет по чеку
     *
     * @return Amount|null
     */
    public function getAmount(): ?Amount
    {
        return $this->amount;
    }

    //**********************************************************************

    /**
     * Установить данные для уведомления покупателя
     *
     * @param Notify $notify
     * @return self
     */
    public function setNotify(Notify $notify): self
    {
        $this->notify = $notify;
        return $this;
    }

    /**
     * Получить данные для уведомления покупателя
     *
     * @return Notify|null
     */
    public function getNotify(): ?Notify
    {
        return $this->notify;
    }

    //**********************************************************************

    /**
     * Установить данные покупателя
     *
     * @param Customer|null $customer
     * @return self
     */
    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * Получить данные покупателя
     *
     * @return Customer|null
     */
    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    //**********************************************************************

    /**
     * Добавить позицию
     *
     * @param ReceiptItem $receiptItem
     * @return self
     */
    public function addItem(ReceiptItem $receiptItem): self
    {
        $this->items[] = $receiptItem;
        return $this;
    }

    /**
     * Установить коллекцию позиций
     *
     * @param ReceiptItemCollection $receipts
     * @return self
     */
    public function setItems(ReceiptItemCollection $receipts): self
    {
        $this->items = $receipts;
        return $this;
    }

    /**
     * Получить коллекцию позиций
     *
     * @return ReceiptItemCollection
     */
    public function getItems(): ReceiptItemCollection
    {
        return $this->items;
    }

    //**********************************************************************

    /**
     * Установить место расчетов (адрес сайта)
     *
     * @throws InvalidArgumentException
     *
     * @param string $location
     * @return self
     */
    public function setLocation(string $location): self
    {
        if (!preg_match('/\w+\.\w+/u', $location)) {
            throw new InvalidArgumentException("invalid location '$location'");
        }

        $this->location = $location;
        return $this;
    }

    /**
     * Получить адрес сайта
     *
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    //**********************************************************************
    // данные запроса

    private $type = null;
    private $taxation = null;
    private $amount = null;
    private $notify = null;
    private $customer = null;
    private $items = null;
    private $location = '';
    private $uuid = null;

    //**********************************************************************
    // идентификационные данные

    private $id = 0;
    private $subType = null;
    private $cashbox = '';
    private $siteId = '0';
    private $orderId = '';

    //**********************************************************************
    // статусные данные

    private $status = null;
}
