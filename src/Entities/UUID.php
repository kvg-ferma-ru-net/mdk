<?php

namespace Innokassa\MDK\Entities;

use Innokassa\MDK\Exceptions\Base\InvalidArgumentException;

/**
 * UUID v4 без - (идентификатор чека)
 * 
 * @see https://ru.wikipedia.org/wiki/UUID#%D0%92%D0%B5%D1%80%D1%81%D0%B8%D1%8F_4_(%D1%81%D0%BB%D1%83%D1%87%D0%B0%D0%B9%D0%BD%D1%8B%D0%B9)
 */
class UUID
{
    public function __construct(string $uuid=null)
    {
        if($uuid !== null)
        {
            if(!preg_match("/([a-fA-F0-9]){32}/", $uuid))
                throw new InvalidArgumentException("invalid uuid '$uuid'");

            $this->uuid = $uuid;
        }
        else
        {
            $this->uuid = sprintf(
                '%04x%04x%04x%04x%04x%04x%04x%04x',
                rand(0, 0xffff), rand(0, 0xffff),
                rand(0, 0xffff),
                rand(0, 0x0fff) | 0x4000,
                rand(0, 0x3fff) | 0x8000,
                rand(0, 0xffff), rand(0, 0xffff), rand(0, 0xffff)
            );
        }
    }

    /**
     * Получить строковое представление
     *
     * @return string
     */
    public function get(): string
    {
        return $this->uuid;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $uuid = '';
};
