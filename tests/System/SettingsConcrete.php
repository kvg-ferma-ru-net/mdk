<?php

use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Exceptions\SettingsException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class SettingsConcrete implements SettingsInterface
{
    public function __construct(array $aSettings)
    {
        $this->aSettings = $aSettings;
    }

    public function getActorId(): string
    {
        return $this->get('actor_id');
    }

    public function getActorToken(): string
    {
        return $this->get('actor_token');
    }

    public function getCashbox(): string
    {
        return intval($this->get('cashbox'));
    }

    public function getLocation(): string
    {
        return $this->get('site');
    }

    public function getTaxation(): int
    {
        return intval($this->get('taxation'));
    }

    public function getOnly2(): bool
    {
        return boolval($this->get('only2'));
    }

    public function get(string $name)
    {
        if (isset($this->aSettings[$name])) {
            return $this->aSettings[$name];
        }

        throw new SettingsException("Настройка '$name' не инициализирована");
    }

    //######################################################################

    protected $aSettings = null;
}
