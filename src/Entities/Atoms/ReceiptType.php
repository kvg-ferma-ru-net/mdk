<?php

namespace Innokassa\MDK\Entities\Atoms;

use Innokassa\MDK\Entities\AtomAbstract;

use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Тип чека
 */
class ReceiptType extends AtomAbstract
{
    /** Приход */
    const COMING            = 1;

    /** Возврат прихода */
    const REFUND_COMING     = 2;

    /** Расход */
    const EXPENCE           = 3;

    /** Возврат расхода */
    const REFUND_EXPENSE    = 4;

    //######################################################################

    /**
     * @throws InvalidArgumentException
     * @param integer $code из констант
     */
    public function __construct(int $code)
    {
        switch ($code) {
            case self::COMING:
                $this->name = 'Приход';
                $this->code = $code;
                break;
            case self::REFUND_COMING:
                $this->name = 'Возврат прихода';
                $this->code = $code;
                break;
            case self::EXPENCE:
                $this->name = 'Расход';
                $this->code = $code;
                break;
            case self::REFUND_EXPENSE:
                $this->name = 'Возврат расхода';
                $this->code = $code;
                break;
            default:
                throw new InvalidArgumentException("invalid receipt type '$code'");
        }
    }

    /**
     * @inheritDoc
     */
    static public function all(): array
    {
        $a = [];

        $a[] = new self(self::COMING);
        $a[] = new self(self::REFUND_COMING);
        $a[] = new self(self::EXPENCE);
        $a[] = new self(self::REFUND_EXPENSE);

        return $a;
    }
};
