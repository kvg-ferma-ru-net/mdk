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
    const HAND  = 0;

    /** Предоплата - покупатель оплатил товар (чек создан автоматически) */
    const PRE   = 1;

    /** Полный расчет - заказ передан покупателю (чек создан автоматически) */
    const FULL  = 2;

    //######################################################################

    /**
     * @throws InvalidArgumentException
     * @param integer $code из констант
     */
    public function __construct(int $code)
    {
        switch ($code) {
            case self::HAND:
                $this->name = 'Чек создан вручную';
                $this->code = $code;
                break;
            case self::PRE:
                $this->name = 'Предоплата';
                $this->code = $code;
                break;
            case self::FULL:
                $this->name = 'Полный расчет';
                $this->code = $code;
                break;
            default:
                throw new InvalidArgumentException("invalid receipt sub type '$code'");
        }
    }

    static public function all(): array
    {
        $a = [];

        $a[] = new self(self::HAND);
        $a[] = new self(self::PRE);
        $a[] = new self(self::FULL);

        return $a;
    }
};
