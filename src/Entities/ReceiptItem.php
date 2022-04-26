<?php

namespace Innokassa\MDK\Entities;

use Innokassa\MDK\Entities\Atoms\Vat;
use Innokassa\MDK\Entities\Atoms\PaymentMethod;
use Innokassa\MDK\Entities\Atoms\ReceiptItemType;
use Innokassa\MDK\Entities\Atoms\Unit;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Сущность "позиция заказа" - одного предмета расчета
 */
class ReceiptItem
{
    public function __construct()
    {
        $this->type = new ReceiptItemType(ReceiptItemType::PRODUCT);
        $this->vat = new Vat(Vat::CODE_WITHOUT);
        $this->paymentMethod = new PaymentMethod(PaymentMethod::PAYMENT_FULL);
        $this->unit = new Unit(Unit::DEFAULT);
    }

    //######################################################################

    /**
     * Установить идентификатор товара
     *
     * @param string $itemId
     * @return self
     */
    public function setItemId(string $itemId): self
    {
        $this->itemId = $itemId;
        return $this;
    }

    /**
     * Получить идентификатор товара
     *
     * @return string
     */
    public function getItemId(): string
    {
        return $this->itemId;
    }

    //######################################################################

    /**
     * Установить тип позиции
     *
     * @throws InvalidArgumentException
     *
     * @param int $type из констант ReceiptItemType
     * @return self
     */
    public function setType(int $type): self
    {
        $this->type = new ReceiptItemType($type);
        return $this;
    }

    /**
     * Получить тип позиции
     *
     * @return int|null
     */
    public function getType(): ?int
    {
        return ($this->type ? $this->type->getCode() : null);
    }

    //**********************************************************************

    /**
     * Установить название позиции
     *
     * @throws InvalidArgumentException
     *
     * @param string $name название позиции длиной [1, 128]
     * @return self
     */
    public function setName(string $name): self
    {
        $name = trim($name);
        if ($name == '') {
            throw new InvalidArgumentException("invalid receipt item name '$name'");
        }

        if (mb_strlen($name) > 128) {
            throw new InvalidArgumentException("invalid receipt item name '$name' max length 128");
        }

        $this->name = $name;
        return $this;
    }

    /**
     * Получить название позиции
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    //**********************************************************************

    /**
     * Установить цену позиции за единицу предмета расчета
     *
     * @throws InvalidArgumentException
     *
     * @param float $price >0
     * @return self
     */
    public function setPrice(float $price): self
    {
        if ($price <= 0) {
            throw new InvalidArgumentException("invalid receipt item price '$price'");
        }

        $this->price = $price;
        return $this;
    }

    /**
     * Получить цену позиции за единицу предмета расчета
     *
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    //**********************************************************************

    /**
     * Установить количество предметов расчета, с учетом скидок, наценок и НДС
     *
     * @throws InvalidArgumentException
     *
     * @param float $quantity >0
     * @return self
     */
    public function setQuantity(float $quantity): self
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("invalid receipt item quantity '$quantity'");
        }

        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Получить количество предметов расчета
     *
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    //**********************************************************************

    /**
     * Установить стоимость предмета расчета с учетом скидок и наценок.
     * Устанавливать необходимо после установки price и quantity.
     * Не является обязательным, так как может быть автоматически сформировано из price * quantity.
     * Однако рекомендуется для проверки правильных вычислений скидок/наценок/НДС
     * в случае если система не считает их автоматически
     *
     * @throws InvalidArgumentException если amount != (price * quantity)
     *
     * @param float $amount >0
     * @return self
     */
    public function setAmount(float $amount): self
    {
        if ($amount != ($this->price * $this->quantity)) {
            throw new InvalidArgumentException(
                "receipt item amount ($amount) deffers from price($this->price) * quantity($this->quantity)"
            );
        }

        $this->amount = $amount;
        return $this;
    }

    /**
     * Получить стоимость предмета расчета с учетом скидок и наценок
     *
     * @return float
     */
    public function getAmount(): float
    {
        return ($this->amount > 0.0 ? $this->amount : ($this->price * $this->quantity));
    }

    //**********************************************************************

    /**
     * Установить НДС.
     * По умолчанию Vat::CODE_WITHOUT
     *
     * @param Vat $vat
     * @return self
     */
    public function setVat(Vat $vat): self
    {
        $this->vat = $vat;
        return $this;
    }

    /**
     * Получить НДС
     *
     * @return Vat
     */
    public function getVat(): Vat
    {
        return $this->vat;
    }

    //**********************************************************************

    /**
     * Установить признак способа расчета
     *
     * @param int $paymentMethod
     * @return self
     */
    public function setPaymentMethod(int $paymentMethod): self
    {
        $this->paymentMethod = new PaymentMethod($paymentMethod);
        return $this;
    }

    /**
     * Получить признак способа расчета
     *
     * @return int
     */
    public function getPaymentMethod(): ?int
    {
        return ($this->paymentMethod ? $this->paymentMethod->getCode() : null);
    }

    /**
     * Установить меру количества предмета расчета
     *
     * @param int $unit
     * @return self
     */
    public function setUnit(int $unit): self
    {
        $this->unit = new Unit($unit);
        return $this;
    }

    /**
     * Получить меру количества предмета расчета
     *
     * @return int
     */
    public function getUnit(): int
    {
        return ($this->unit ? $this->unit->getCode() : null);
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $type = null;
    private $name = '';
    private $price = 0.0;
    private $quantity = 1.0;
    private $amount = 0.0;
    private $vat = null;
    private $paymentMethod = null;
    private $unit = null;

    /** @var string */
    private $itemId = '';
}
