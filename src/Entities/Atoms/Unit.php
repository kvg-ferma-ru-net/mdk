<?php

namespace Innokassa\MDK\Entities\Atoms;

use Innokassa\MDK\Entities\AtomAbstract;
use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * Мера количества предмета расчета
 */
class Unit extends AtomAbstract
{
    /** ШТ. (для предметов расчета; реализуемых поштучно или единицами) */
    public const PIECES = 0;

    /** Граммы */
    public const GRAM = 10;

    /** Килограммы */
    public const KILOGRAM = 11;

    /** Тонны */
    public const TON = 12;

    /** Сантиметры */
    public const CENTIMETR = 20;

    /** Дециметры */
    public const DECIMETER = 21;

    /** Метры */
    public const METER = 22;

    /** Квадратные сантиметры */
    public const SQUARE_CENTIMETER = 30;

    /** Квадратные дециметры */
    public const SQUARE_DECIMETER = 31;

    /** Квадратные метры */
    public const SQUARE_METER = 32;

    /** Миллилитры */
    public const MILLILITER = 40;

    /** Литры */
    public const LITRE = 41;

    /** Кубические метры */
    public const CUBIC_METER = 42;

    /** Киловатт часы */
    public const KILOWATT_HOUR = 50;

    /** Гигакалории */
    public const GIGACALORIES = 51;

    /** Сутки */
    public const DAY = 70;

    /** Часы */
    public const HOUR = 71;

    /** Минуты */
    public const MINUTE = 72;

    /** Секунды */
    public const SECOND = 73;

    /** Килобайты */
    public const KILOBYTES = 80;

    /** Мегабайты */
    public const MEGABYTES = 81;

    /** Гигабайты */
    public const GIGABYTES = 82;

    /** Терабайты */
    public const TERABYTES = 83;

    /** Default */
    public const DEFAULT = 255;

    //######################################################################

    /**
     * @throws InvalidArgumentException
     * @param integer $code из констант
     */
    public function __construct(int $code)
    {
        switch ($code) {
            case static::PIECES:
                $this->name = 'Штуки';
                break;
            case static::GRAM:
                $this->name = 'Граммы';
                break;
            case static::KILOGRAM:
                $this->name = 'Килограммы';
                break;
            case static::TON:
                $this->name = 'Тонны';
                break;
            case static::CENTIMETR:
                $this->name = 'Сантиметры';
                break;
            case static::DECIMETER:
                $this->name = 'Дециметры';
                break;
            case static::METER:
                $this->name = 'Метры';
                break;
            case static::SQUARE_CENTIMETER:
                $this->name = 'Квадратные сантиметры';
                break;
            case static::SQUARE_DECIMETER:
                $this->name = 'Квадратные дециметры';
                break;
            case static::SQUARE_METER:
                $this->name = 'Квадратные метры';
                break;
            case static::MILLILITER:
                $this->name = 'Миллилитры';
                break;
            case static::LITRE:
                $this->name = 'Литры';
                break;
            case static::CUBIC_METER:
                $this->name = 'Кубические метры';
                break;
            case static::KILOWATT_HOUR:
                $this->name = 'Киловатт часы';
                break;
            case static::GIGACALORIES:
                $this->name = 'Гигакалории';
                break;
            case static::DAY:
                $this->name = 'Дни';
                break;
            case static::HOUR:
                $this->name = 'Часы';
                break;
            case static::MINUTE:
                $this->name = 'Минуты';
                break;
            case static::SECOND:
                $this->name = 'Секунды';
                break;
            case static::KILOBYTES:
                $this->name = 'Килобайты';
                break;
            case static::MEGABYTES:
                $this->name = 'Мегабайты';
                break;
            case static::GIGABYTES:
                $this->name = 'Гигабайты';
                break;
            case static::TERABYTES:
                $this->name = 'Терабайты';
                break;
            case static::DEFAULT:
                $this->name = 'Иные';
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

        $a[] = new self(self::PIECES);
        $a[] = new self(self::GRAM);
        $a[] = new self(self::KILOGRAM);
        $a[] = new self(self::TON);
        $a[] = new self(self::CENTIMETR);
        $a[] = new self(self::DECIMETER);
        $a[] = new self(self::METER);
        $a[] = new self(self::SQUARE_CENTIMETER);
        $a[] = new self(self::SQUARE_DECIMETER);
        $a[] = new self(self::SQUARE_METER);
        $a[] = new self(self::MILLILITER);
        $a[] = new self(self::LITRE);
        $a[] = new self(self::CUBIC_METER);
        $a[] = new self(self::KILOWATT_HOUR);
        $a[] = new self(self::GIGACALORIES);
        $a[] = new self(self::DAY);
        $a[] = new self(self::HOUR);
        $a[] = new self(self::MINUTE);
        $a[] = new self(self::SECOND);
        $a[] = new self(self::KILOBYTES);
        $a[] = new self(self::MEGABYTES);
        $a[] = new self(self::GIGABYTES);
        $a[] = new self(self::TERABYTES);
        $a[] = new self(self::DEFAULT);

        return $a;
    }
}
