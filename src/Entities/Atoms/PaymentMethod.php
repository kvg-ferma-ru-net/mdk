<?php

namespace Innokassa\MDK\Entities\Atoms;

use Innokassa\MDK\Entities\AtomAbstract;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Признак расчета
 */
class PaymentMethod extends AtomAbstract
{
    /** Полная предварительная оплата до момента передачи предмета расчета */
    public const PREPAYMENT_FULL = 1;

    /** Частичная предварительная оплата до момента передачи предмета расчета */
    public const PREPAYMENT_PART = 2;

    /** Аванс */
    public const ADVANCE = 3;

    /** Полная оплата, в том числе с учетом аванса (предварительной оплаты) в момент передачи предмета расчета */
    public const PAYMENT_FULL = 4;

    /** Частичный расчет в кредит - частичная оплата предмета расчета в момент его передачи с последующей оплатой в кредит */
    public const CREDIT_PART = 5;

    /** Передача в кредит - передача предмета расчета без его оплаты в момент его передачи с последующей оплатой в кредит */
    public const CREDIT_FULL = 6;

    /** Оплата кредита - оплата предмета расчета после его передачи с оплатой в кредит */
    public const CREDIT_PAY = 7;

    //######################################################################

    /**
     * @throws InvalidArgumentException
     * @param integer $code из констант
     */
    public function __construct(int $code)
    {
        switch ($code) {
            case static::PREPAYMENT_FULL:
                $this->name = 'Предоплата 100%';
                break;
            case static::PREPAYMENT_PART:
                $this->name = 'Частичная предоплата';
                break;
            case static::ADVANCE:
                $this->name = 'Аванс';
                break;
            case static::PAYMENT_FULL:
                $this->name = 'Полный расчет';
                break;
            case static::CREDIT_PART:
                $this->name = 'В частичный кредит';
                break;
            case static::CREDIT_FULL:
                $this->name = 'В кредит 100%';
                break;
            case static::CREDIT_PAY:
                $this->name = 'Оплата кредита';
                break;
            default:
                throw new InvalidArgumentException("invalid payment method '$code'");
        }

        $this->code = $code;
    }

    /**
     * @inheritDoc
     */
    public static function all(): array
    {
        $a = [];

        $a[] = new self(self::PREPAYMENT_FULL);
        $a[] = new self(self::PREPAYMENT_PART);
        $a[] = new self(self::ADVANCE);
        $a[] = new self(self::PAYMENT_FULL);
        $a[] = new self(self::CREDIT_PART);
        $a[] = new self(self::CREDIT_FULL);
        $a[] = new self(self::CREDIT_PAY);

        return $a;
    }
}
