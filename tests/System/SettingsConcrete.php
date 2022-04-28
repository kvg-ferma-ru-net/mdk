<?php

use Innokassa\MDK\Entities\Atoms\ReceiptItemType;
use Innokassa\MDK\Entities\Atoms\Vat;
use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Exceptions\SettingsException;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class SettingsConcrete implements SettingsInterface
{
    public function __construct(array $aSettings)
    {
        $this->aSettings = $aSettings;
    }

    public function getActorId($siteId = null): string
    {
        return $this->get('actor_id');
    }

    public function getActorToken($siteId = null): string
    {
        return $this->get('actor_token');
    }

    public function getCashbox($siteId = null): string
    {
        return intval($this->get('cashbox'));
    }

    public function getLocation($siteId = null): string
    {
        return $this->get('site');
    }

    public function getTaxation($siteId = null): int
    {
        return intval($this->get('taxation'));
    }

    public function getScheme($siteId = null): int
    {
        return $this->get('scheme');
    }

    public function getVatShipping($siteId = null): int
    {
        return $this->get('vat_shipping');
        //return Vat::CODE_WITHOUT;
    }

    public function getVatDefaultItems($siteId = null): int
    {
        return $this->get('vat_default_items');
    }

    public function getTypeDefaultItems($siteId = null): int
    {
        return $this->get('type_default_items');
    }

    public function getOrderStatusReceiptPre($siteId = null): string
    {
        return $this->get('order_status_pre');
    }

    public function getOrderStatusReceiptFull($siteId = null): string
    {
        return $this->get('order_status_full');
    }

    //######################################################################

    public function get(string $name, $siteId = null)
    {
        if (isset($this->aSettings[$name])) {
            return $this->aSettings[$name];
        }

        throw new SettingsException("Настройка '$name' не инициализирована");
    }

    //######################################################################

    protected $aSettings = null;
}
