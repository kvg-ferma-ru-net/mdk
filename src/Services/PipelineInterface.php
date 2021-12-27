<?php

namespace Innokassa\MDK\Services;

/**
 * Сервис очереди чеков на обновление
 */
interface PipelineInterface
{
    /**
     * Обновление статуса чеков, которые были приняты сервером (WAIT | ASSUME), но еще не пробились
     *
     * @return bool
     */
    public function updateAccepted(): bool;

    /**
     * Повторная отправка чеков, которые не были приняты сервером (отказа доступа или связи с сервером не было)
     *
     * @return bool
     */
    public function updateUnaccepted(): bool;
}
