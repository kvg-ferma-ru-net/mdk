<?php

namespace Innokassa\MDK\Services;

/**
 * Сервис очереди чеков на обновление
 */
interface PipelineInterface
{
    /**
     * Обновление статуса чеков, которые не пробились сразу
     *
     * @param string $file путь до файла блокировки
     *
     * @return bool
     */
    public function update(string $file): bool;

    /**
     * Сборка и публикация данных мониторинга
     *
     * @param string $file путь до файла мониторинга (для записи)
     * @param string $columnStratTime название столбца с временем старта чека
     * @return bool
     */
    public function monitoring(string $file, string $columnStratTime = 'start_time'): bool;
}
