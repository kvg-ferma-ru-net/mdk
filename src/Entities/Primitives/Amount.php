<?php

namespace Innokassa\MDK\Entities\Primitives;

use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Данные об оплате
 */
class Amount
{
    public function __construct()
    {
    }

    //**********************************************************************

    /**
     * Установить расчет наличными
     *
     * @throws InvalidArgumentException
     *
     * @param float $cash
     * @return self
     */
    public function setCash(float $cash): self
    {
        if ($cash < 0) {
            throw new InvalidArgumentException("invalid amount cash '$cash'");
        }

        $this->cash = $cash;
        return $this;
    }

    /**
     * Получить расчет наличными
     *
     * @return float
     */
    public function getCash(): float
    {
        return $this->cash;
    }

    //**********************************************************************

    /**
     * Установить расчет безналичными
     *
     * @throws InvalidArgumentException
     *
     * @param float $cashless
     * @return self
     */
    public function setCashless(float $cashless): self
    {
        if ($cashless < 0) {
            throw new InvalidArgumentException("invalid amount cashless '$cashless'");
        }

        $this->cashless = $cashless;
        return $this;
    }

    /**
     * Получить расчет безначлиными
     *
     * @return float
     */
    public function getCashless(): float
    {
        return $this->cashless;
    }

    //**********************************************************************

    /**
     * Установить расчет предоплатой
     *
     * @throws InvalidArgumentException
     *
     * @param float $prepayment
     * @return self
     */
    public function setPrepayment(float $prepayment): self
    {
        if ($prepayment < 0) {
            throw new InvalidArgumentException("invalid amount prepayment '$prepayment'");
        }

        $this->prepayment = $prepayment;
        return $this;
    }

    /**
     * Получить расчет предоплатой
     *
     * @return float
     */
    public function getPrepayment(): float
    {
        return $this->prepayment;
    }

    //**********************************************************************

    /**
     * Установить расчет постоплатой
     *
     * @throws InvalidArgumentException
     *
     * @param float $postpayment
     * @return self
     */
    public function setPostpayment(float $postpayment): self
    {
        if ($postpayment < 0) {
            throw new InvalidArgumentException("invalid amount postpayment '$postpayment'");
        }

        $this->postpayment = $postpayment;
        return $this;
    }

    /**
     * Получить расчет постоплатой
     *
     * @return float
     */
    public function getPostpayment(): float
    {
        return $this->postpayment;
    }

    //**********************************************************************

    /**
     * Установить расчет встречным представлением
     *
     * @throws InvalidArgumentException
     *
     * @param float $barter
     * @return self
     */
    public function setBarter(float $barter): self
    {
        if ($barter < 0) {
            throw new InvalidArgumentException("invalid amount barter '$barter'");
        }

        $this->barter = $barter;
        return $this;
    }

    /**
     * Получить расчет встречным представлением
     *
     * @return float
     */
    public function getBarter(): float
    {
        return $this->barter;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    /** @var float */
    private $cash = 0.0;

    /** @var float */
    private $cashless = 0.0;

    /** @var float */
    private $prepayment = 0.0;

    /** @var float */
    private $postpayment = 0.0;

    /** @var float */
    private $barter = 0.0;
}
