<?php

namespace Innokassa\MDK\Entities\Atoms;

use Innokassa\MDK\Entities\AtomAbstract;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * НДС
 */
class Vat extends AtomAbstract
{
    /** 20% */
    public const CODE_20       = 1;

    /** 10% */
    public const CODE_10       = 2;

    /** 20/120 */
    public const CODE_120      = 3;

    /** 10/110 */
    public const CODE_110      = 4;

    /** 0% */
    public const CODE_0        = 5;

    /** Без НДС */
    public const CODE_WITHOUT  = 6;

    //######################################################################

    /**
     * @throws InvalidArgumentException
     * @param string|int $value значение НДС (20% == 20, пустая строка - без НДС) или код НДС для API из констант
     */
    public function __construct($value)
    {
        $value = trim($value);
        $value = str_replace("%", "", $value);

        switch ($value) {
            case '20':
                $this->code = self::CODE_20;
                $this->name = $value;
                break;
            case '10':
                $this->code = self::CODE_10;
                $this->name = $value;
                break;
            case '20/120':
                $this->code = self::CODE_120;
                $this->name = $value;
                break;
            case '10/110':
                $this->code = self::CODE_110;
                $this->name = $value;
                break;
            case '0':
                $this->code = self::CODE_0;
                $this->name = $value;
                break;
            case '':
                $this->code = self::CODE_WITHOUT;
                $this->name = 'Не облагается';
                break;
            default:
                $value = intval($value);
                $a = [
                    self::CODE_20 => '20',
                    self::CODE_10 => '10',
                    self::CODE_120 => '20/120',
                    self::CODE_110 => '10/110',
                    self::CODE_0 => '0',
                    self::CODE_WITHOUT => 'Не облагается'
                ];
                if (!isset($a[$value])) {
                    throw new InvalidArgumentException("invalid vat value '$value'");
                } else {
                    $this->code = $value;
                    $this->name = $a[$value];
                }
        }
    }

    /**
     * @inheritDoc
     */
    public static function all(): array
    {
        $a = [];
        $a[] = new self(self::CODE_20);
        $a[] = new self(self::CODE_10);
        $a[] = new self(self::CODE_120);
        $a[] = new self(self::CODE_110);
        $a[] = new self(self::CODE_0);
        $a[] = new self(self::CODE_WITHOUT);

        return $a;
    }
}
