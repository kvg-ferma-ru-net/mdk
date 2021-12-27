<?php

namespace Innokassa\MDK\Entities\Atoms;

use Innokassa\MDK\Entities\AtomAbstract;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Подтип чека
 */
class ReceiptSubType extends AtomAbstract
{
    /** Чек создан вручную */
    public const HAND  = 0;

    /** Предоплата - покупатель оплатил товар (чек создан автоматически) */
    public const PRE   = 1;

    /** Полный расчет - заказ передан покупателю (чек создан автоматически) */
    public const FULL  = 2;

    //######################################################################

    /**
     * @throws InvalidArgumentException
     * @param integer $code из констант
     */
    public function __construct(int $code)
    {
        switch ($code) {
            case self::HAND:
                $this->name = 'Ручной';
                $this->code = $code;
                break;
            case self::PRE:
                $this->name = 'Предоплата';
                $this->code = $code;
                break;
            case self::FULL:
                $this->name = 'Расчет';
                $this->code = $code;
                break;
            default:
                throw new InvalidArgumentException("invalid receipt sub type '$code'");
        }
    }

    /**
     * @inheritDoc
     */
    public static function all(): array
    {
        $a = [];

        $a[] = new self(self::HAND);
        $a[] = new self(self::PRE);
        $a[] = new self(self::FULL);

        return $a;
    }
}
