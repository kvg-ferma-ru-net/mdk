<?php

namespace Innokassa\MDK\Services;

/**
 * Сервис очереди чеков на обновление
 */
interface PipelineInterface
{
    /**
     * Обновление статуса чеков, которые были приняты сервером (ReceiptStatus::WAIT | ReceiptStatus::ASSUME), но еще не пробились
     *
     * @return void
     */
    public function updateAccepted();

    /**
     * Повторная отправка на фискализацию чеков, которые не были приняты сервером по причинам отказа доступа
     *
     * @return void
     */
    public function updateUnaccepted();
};
