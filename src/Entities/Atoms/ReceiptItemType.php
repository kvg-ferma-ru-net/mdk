<?php

namespace Innokassa\MDK\Entities\Atoms;

use Innokassa\MDK\Entities\AtomAbstract;

use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Тип позиции чека
 */
class ReceiptItemType extends AtomAbstract
{
    /** Товар */
    const PRODUCT = 1;

    /** Акцизный товар */
    const EXCISE = 2;

    /** Работа */
    const WORK = 3;

    /** Услуга */
    const SERVICE = 4;

    /** Ставка азартной игры */
    const GAMING_BET = 5;

    /** Выигрыш азартной игры */
    const GAMING_WIN = 6;

    /** Лотерейный билет */
    const LOTTERY_TICKET = 7;

    /** Выигрыш лотереи */
    const LOTTERY_WIN = 8;

    /** РИД */
    const RIA = 9;

    /** Платеж */
    const PAYMENT = 10;

    /** Вознаграждение агента */
    const AGENT_COMMISSION = 11;

    /** Составной предмет расчета */
    const COMPOSITE = 12;

    /** Иной предмет расчета */
    const OTHER = 13;

    /** Имущественное право */
    const PROPERTY_RIGHT = 14;

    /** Внереализационный доход */
    const OUTSTANDING_INCOME = 15;

    /** Страховой взнос */
    const INSURANCE_FEE = 16;

    /** Торговый сбор */
    const TRADING_FEES = 17;

    /** Курортный сбор */
    const RESORT_FEE = 18;

    /** Залог */
    const PLEDGE = 19;

    //######################################################################

    /**
     * @throws InvalidArgumentException
     * @param integer $code из констант
     */
    public function __construct(int $code)
    {
        switch($code)
        {
            case self::PRODUCT:
                $this->name = 'Товар';
                break;
            case self::EXCISE:
                $this->name = 'Акциз';
                break;
            case self::WORK:
                $this->name = 'Работа';
                break;
            case self::SERVICE:
                $this->name = 'Услуга';
                break;
            case self::GAMING_BET:
                $this->name = 'Ставка азартной игры';
                break;
            case self::GAMING_WIN:
                $this->name = 'Выигрыш азартной игры';
                break;
            case self::LOTTERY_TICKET:
                $this->name = 'Лотерейный билет';
                break;
            case self::LOTTERY_WIN:
                $this->name = 'Выигрыш лотереи';
                break;
            case self::RIA:
                $this->name = 'РИД';
                break;
            case self::PAYMENT:
                $this->name = 'Платеж';
                break;
            case self::AGENT_COMMISSION:
                $this->name = 'Вознаграждение агента';
                break;
            case self::COMPOSITE:
                $this->name = 'Составной предмет расчета';
                break;
            case self::OTHER:
                $this->name = 'Иной предмет расчета';
                break;
            case self::PROPERTY_RIGHT:
                $this->name = 'Имущественное право';
                break;
            case self::OUTSTANDING_INCOME:
                $this->name = 'Внереализационный доход';
                break;
            case self::INSURANCE_FEE:
                $this->name = 'Страховой взнос';
                break;
            case self::TRADING_FEES:
                $this->name = 'Торговый сбор';
                break;
            case self::RESORT_FEE:
                $this->name = 'Курортный сбор';
                break;
            case self::PLEDGE:
                $this->name = 'Залог';
                break;
            default:
                throw new InvalidArgumentException("invalid receipt item type '$code'");
        }

        $this->code = $code;
    }

    /**
     * @inheritDoc
     */
    static public function all(): array
    {
        $a = [];

        $a[] = new self(self::PRODUCT);
        $a[] = new self(self::EXCISE);
        $a[] = new self(self::WORK);
        $a[] = new self(self::SERVICE);
        $a[] = new self(self::GAMING_BET);
        $a[] = new self(self::GAMING_WIN);
        $a[] = new self(self::LOTTERY_TICKET);
        $a[] = new self(self::LOTTERY_WIN);
        $a[] = new self(self::RIA);
        $a[] = new self(self::PAYMENT);
        $a[] = new self(self::AGENT_COMMISSION);
        $a[] = new self(self::COMPOSITE);
        $a[] = new self(self::OTHER);
        $a[] = new self(self::PROPERTY_RIGHT);
        $a[] = new self(self::OUTSTANDING_INCOME);
        $a[] = new self(self::INSURANCE_FEE);
        $a[] = new self(self::TRADING_FEES);
        $a[] = new self(self::RESORT_FEE);
        $a[] = new self(self::PLEDGE);

        return $a;
    }
};
