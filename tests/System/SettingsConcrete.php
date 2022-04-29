<?php

use Innokassa\MDK\Settings\SettingsAbstract;
use Innokassa\MDK\Exceptions\SettingsException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class SettingsConcrete extends SettingsAbstract
{
    public function __construct(array $aSettings)
    {
        $this->aSettings = $aSettings;
    }

    public function getActorId(string $siteId = null): string
    {
        return $this->get('actor_id');
    }

    public function getActorToken(string $siteId = null): string
    {
        return $this->get('actor_token');
    }

    public function getCashbox(string $siteId = null): string
    {
        return intval($this->get('cashbox'));
    }

    public function getLocation(string $siteId = null): string
    {
        return $this->get('site');
    }

    public function getTaxation(string $siteId = null): int
    {
        return intval($this->get('taxation'));
    }

    public function getScheme(string $siteId = null): int
    {
        return $this->get('scheme');
    }

    public function getVatShipping(string $siteId = null): int
    {
        return $this->get('vat_shipping');
    }

    public function getVatDefaultItems(string $siteId = null): int
    {
        return $this->get('vat_default_items');
    }

    public function getTypeDefaultItems(string $siteId = null): int
    {
        return $this->get('type_default_items');
    }

    public function getOrderStatusReceiptPre(string $siteId = null): string
    {
        return $this->get('order_status_pre');
    }

    public function getOrderStatusReceiptFull(string $siteId = null): string
    {
        return $this->get('order_status_full');
    }

    //######################################################################

    public function get(string $name, string $siteId = null)
    {
        if (isset($this->aSettings[$name])) {
            return $this->aSettings[$name];
        }

        throw new SettingsException("Настройка '$name' не инициализирована");
    }

    //######################################################################

    protected $aSettings = null;
}
