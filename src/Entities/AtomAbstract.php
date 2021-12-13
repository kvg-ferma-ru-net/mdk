<?php

namespace Innokassa\MDK\Entities;

/**
 * Абстракция простейшей сущности чека "атом"
 */
abstract class AtomAbstract
{
    /**
     * Получить название кода сущности
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Получить код сущности
     *
     * @return integer
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Получить все возможные объекты текущего атома
     *
     * @return array
     */
    abstract static public function all(): array;

    //######################################################################
    // PROTECTED
    //######################################################################

    /**
     * Название кода сущности
     *
     * @var string
     */
    protected $name;

    /**
     * Код сущности
     *
     * @var int
     */
    protected $code;
};
