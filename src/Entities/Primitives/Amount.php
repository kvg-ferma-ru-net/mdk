<?php

namespace Innokassa\MDK\Entities\Primitives;

use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Данные об оплате
 */
class Amount
{
    /** Оплата наличными */
    public const CASH = 1;

    /** Оплата безналичными */
    public const CASHLESS = 2;

    /** Оплата предоплатой */
    public const PREPAYMENT = 3;

    /** Оплата постоплатой */
    public const POSTPAYMENT = 4;

    /** Оплата встречным представлением */
    public const BARTER = 5;

    //######################################################################

    /**
     * @throws InvalidArgumentException
     * @param integer|null $type из констант класса
     * @param float|null $sum сумма
     */
    public function __construct(int $type = null, float $sum = null)
    {
        if (is_numeric($type) && is_numeric($sum)) {
            $this->set($type, $sum);
        }
    }

    /**
     * Установить тип и сумму оплаты
     *
     * @throws InvalidArgumentException
     * @param integer $type из констант класса
     * @param float $sum сумма
     * @return self
     */
    public function set(int $type, float $sum): self
    {
        if ($sum < 0) {
            throw new InvalidArgumentException("invalid amount sum '$sum'");
        }

        switch ($type) {
            case Amount::CASH:
                $this->cash = $sum;
                break;
            case Amount::CASHLESS:
                $this->cashless = $sum;
                break;
            case Amount::PREPAYMENT:
                $this->prepayment = $sum;
                break;
            case Amount::POSTPAYMENT:
                $this->postpayment = $sum;
                break;
            case Amount::BARTER:
                $this->barter = $sum;
                break;
            default:
                throw new InvalidArgumentException("invalid amount type '$type'");
        }

        return $this;
    }

    /**
     * Получить сумму по типу расчета
     *
     * @throws InvalidArgumentException
     * @param integer $type из констант класса
     * @return float
     */
    public function get(int $type): ?float
    {
        switch ($type) {
            case Amount::CASH:
                return $this->cash;
            case Amount::CASHLESS:
                return $this->cashless;
            case Amount::PREPAYMENT:
                return $this->prepayment;
            case Amount::POSTPAYMENT:
                return $this->postpayment;
            case Amount::BARTER:
                return $this->barter;
            default:
                throw new InvalidArgumentException("invalid amount type '$type'");
        }
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $cash = null;
    private $cashless = null;
    private $prepayment = null;
    private $postpayment = null;
    private $barter = null;
}
