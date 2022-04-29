<?php

namespace Innokassa\MDK\Services;

use Innokassa\MDK\Net\TransferInterface;
use Innokassa\MDK\Entities\Atoms\Taxation;
use Innokassa\MDK\Settings\SettingsAbstract;
use Innokassa\MDK\Exceptions\SettingsException;
use Innokassa\MDK\Exceptions\TransferException;

/**
 * Базовая реализация ConnectorInterface
 */
class ConnectorBase implements ConnectorInterface
{
    /**
     * @param TransferInterface $transfer
     */
    public function __construct(TransferInterface $transfer)
    {
        $this->transfer = $transfer;
    }

    /**
     * @inheritDoc
     */
    public function testSettings(SettingsAbstract $settings, string $siteId = ''): bool
    {
        try {
            $response = $this->transfer->getCashBox($settings->extrudeConn($siteId));
        } catch (TransferException $e) {
            if ($e->getCode() >= 500) {
                throw new SettingsException('Сервер временно недоступен, попробуйте позже', $e->getCode());
            } else {
                throw new SettingsException('Неверные авторизационные данные', $e->getCode());
            }
        }

        if (!($response->taxation & $settings->getTaxation($siteId))) {
            $taxations = Taxation::all();
            $included = [];
            foreach ($taxations as $taxation) {
                if ($response->taxation & $taxation->getCode()) {
                    $included[] = $taxation->getName();
                }
            }

            $sListTaxations = implode(", ", $included);
            $error = "Указанный налог не может быть применен, доступные налогообложения: $sListTaxations";
            throw new SettingsException($error);
        } elseif (array_search($settings->getLocation($siteId), $response->billing_place_list) === false) {
            $sListPlaces = implode(", ", $response->billing_place_list);
            $error = "Указанное место расчетов не может быть использовано, доступные: $sListPlaces";
            throw new SettingsException($error);
        }

        return true;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    /** @var TransferInterface */
    private $transfer = null;
}
