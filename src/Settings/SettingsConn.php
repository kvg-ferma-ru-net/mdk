<?php

namespace Innokassa\MDK\Settings;

/**
 * Настройки соединения/авторизации с группой касс на сервере
 */
class SettingsConn
{
    /**
     * @param string $actorId
     * @param string $actorToken
     * @param string $cashbox
     */
    public function __construct(string $actorId, string $actorToken, string $cashbox)
    {
        $this->actorId = $actorId;
        $this->actorToken = $actorToken;
        $this->cashbox = $cashbox;
    }

    /**
     * Получить идентификатор актора
     *
     * @return string
     */
    public function getActorId(): string
    {
        return $this->actorId;
    }

    /**
     * Получить токен актора
     *
     * @return string
     */
    public function getActorToken(): string
    {
        return $this->actorToken;
    }

    /**
     * Получить группу касс
     *
     * @return string
     */
    public function getCashbox(): string
    {
        return $this->cashbox;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    /** @var string */
    private $actorId = '';

    /** @var string */
    private $actorToken = '';

    /** @var string */
    private $cashbox = '';
}
