<?php

namespace Innokassa\MDK\Entities\Atoms;

use Innokassa\MDK\Entities\AtomAbstract;

use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Тип чека
 */
class Taxation extends AtomAbstract
{
    /** ОРН */
    const ORN   = 1;

    /** УСН доход */
    const USN   = 2;

    /** УСН доход - расход */
    const USNDR = 4;

    /** ЕСН */
    const ESN   = 16;

    /** ПСН */
    const PSN   = 32;

    //######################################################################

    /**
     * @throws InvalidArgumentException
     * @param integer $code из констант
     */
    public function __construct(int $code)
    {
        switch ($code) {
            case self::ORN:
                $this->name = 'ОРН';
                break;
            case self::USN:
                $this->name = 'УСН доход';
                break;
            case self::USNDR:
                $this->name = 'УСН доход - расход';
                break;
            case self::ESN:
                $this->name = 'ЕСН';
                break;
            case self::PSN:
                $this->name = 'ПСН';
                break;
            default:
                throw new InvalidArgumentException("invalid receipt taxation '$code'");
        }
        
        $this->code = $code;
    }

    static public function all(): array
    {
        $a = [];
        $a[] = new self(self::ORN);
        $a[] = new self(self::USN);
        $a[] = new self(self::USNDR);
        $a[] = new self(self::ESN);
        $a[] = new self(self::PSN);
        
        return $a;
    }
};
