<?php

namespace Innokassa\MDK\Logger;

/**
 * Интерфейс логирования
 */
interface LoggerInterface
{
    /**
     * Вывод информации в лог
     *
     * @param integer $level уровень сообщения из констант LogLevel
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log(int $level, string $message, array $context = []): void;
}
