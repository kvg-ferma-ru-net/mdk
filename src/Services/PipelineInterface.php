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
     * @return bool
     */
    public function update(): bool;
}
