<?php

namespace Innokassa\MDK\Logger;

class LogLevel
{
    public const INFO = 1;

    public const ERROR = 2;

    //######################################################################

    /**
     * Получить имя уровня логирования
     *
     * @param integer $level
     * @return string
     */
    public static function getName(int $level): string
    {
        switch ($level) {
            case self::INFO:
                return "INFO";
            case self::ERROR:
                return "ERROR";
            default:
                return (string) $level;
        }
    }
}
