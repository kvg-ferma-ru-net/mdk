<?php

namespace Innokassa\MDK\Entities;

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
    /** время в течении которого можно пробить чек, после первой попытки */
    public const ALLOWED_ATTEMPT_TIME = 60 * 60 * 24;

    //######################################################################

    public function __construct()
    {
        $this->items = new ReceiptItemCollection();
        $this->type = new ReceiptType(ReceiptType::COMING);
        $this->status = new ReceiptStatus(ReceiptStatus::PREPARED);
        $this->startTime = date('Y-m-d H:i:s');
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
     * Установить id чека
     *
     * @param string $receiptId
     * @return self
     */
    public function setReceiptId(string $receiptId): self
    {
        $this->receiptId = $receiptId;
        return $this;
    }

    /**
     * Получить текущий id чека
     *
     * @return string
     */
    public function getReceiptId(): string
    {
        return $this->receiptId;
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

        // если чек был принят сервером - помечаем
        if (
            $receiptStatus->getCode() == ReceiptStatus::ACCEPTED
            || $receiptStatus->getCode() == ReceiptStatus::COMPLETED
        ) {
            $this->setAccepted(true);
        }

        // если чек еще не был принят сервером и авторизация не прошла или статусы чека провальные - чек недействителен
        if (
            ($this->status->getCode() == ReceiptStatus::UNAUTH && !$this->getAccepted())
            || $this->status->getCode() == ReceiptStatus::ERROR
            || $this->status->getCode() == ReceiptStatus::EXPIRED
        ) {
            $this->setAvailable(false);
        } else {
            $this->setAvailable(true);
        }

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
     * Установить статус принятия сервером
     *
     * @param bool $accepted
     * @return self
     */
    public function setAccepted(bool $accepted): self
    {
        $this->accepted = $accepted;
        return $this;
    }

    /**
     * Получить статус принятия сервером
     *
     * @return bool
     */
    public function getAccepted(): bool
    {
        return $this->accepted;
    }

    //**********************************************************************

    /**
     * Установить действительность чека
     *
     * @param bool $available
     * @return self
     */
    public function setAvailable(bool $available): self
    {
        $this->available = $available;
        return $this;
    }

    /**
     * Получить действительность чека
     *
     * @return bool
     */
    public function getAvailable(): bool
    {
        return $this->available;
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

    /**
     * Установить дату и время первой попытки фискализации
     *
     * @param string $startTime
     * @return self
     */
    public function setStartTime(string $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    /**
     * Получить дату и время первой попытки фискализации
     *
     * @return string
     */
    public function getStartTime(): string
    {
        return $this->startTime;
    }

    /**
     * Истекло ли время ожидания фискализации
     *
     * @return boolean
     */
    public function isExpired(): bool
    {
        return (time() - strtotime($this->startTime) > self::ALLOWED_ATTEMPT_TIME);
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
    private $receiptId = '';

    //**********************************************************************
    // идентификационные данные

    private $id = 0;
    private $subType = null;
    private $cashbox = '';

    /** @var string */
    private $siteId = '';

    private $orderId = '';

    //**********************************************************************
    // статусные данные

    private $status = null;
    private $accepted = false;
    private $available = false;

    //**********************************************************************
    // прочее

    /** @var string */
    private $startTime = '';
}
