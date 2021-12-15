<?php

namespace Innokassa\MDK;

use Innokassa\MDK\Services\ManualInterface;
use Innokassa\MDK\Services\PrinterInterface;
use Innokassa\MDK\Services\PipelineInterface;
use Innokassa\MDK\Services\AutomaticInterface;
use Innokassa\MDK\Services\ConnectorInterface;

use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Storage\ReceiptStorageInterface;
use Innokassa\MDK\Entities\ReceiptAdapterInterface;

/**
 * Клиент основанный на сервисах для использования API Pangaea v2.
 */
class Client
{
    public function __construct(
        SettingsInterface $settings,
        ReceiptAdapterInterface $adapter,
        ReceiptStorageInterface $storage,

        AutomaticInterface $atomatic,
        ManualInterface $manual,
        PipelineInterface $pipeline,
        ConnectorInterface $connector
    )
    {
        $this->settings = $settings;
        $this->adapter = $adapter;
        $this->storage = $storage;

        $this->atomatic = $atomatic;
        $this->manual = $manual;
        $this->pipeline = $pipeline;
        $this->connector = $connector;
    }

    //######################################################################

    /**
     * Получить сервис автоматической фискализации
     *
     * @return AutomaticInterface
     */
    public function serviceAutomatic(): AutomaticInterface
    {
        return $this->atomatic;
    }

    /**
     * Получить сервис ручной фискализации
     *
     * @return ManualInterface
     */
    public function serviceManual(): ManualInterface
    {
        return $this->manual;
    }

    /**
     * Получить сервис обработки очереди чеков
     *
     * @return PipelineInterface
     */
    public function servicePipeline(): PipelineInterface
    {
        return $this->pipeline;
    }

    /**
     * Получить сервис тестирования соединения с сервермо фискализации
     *
     * @return ConnectorInterface
     */
    public function serviceConnector(): ConnectorInterface
    {
        return $this->connector;
    }

    //######################################################################

    public function componentSettings(): SettingsInterface
    {
        return $this->settings;
    }

    public function componentAdapter(): ReceiptAdapterInterface
    {
        return $this->adapter;
    }

    public function componentStorage(): ReceiptStorageInterface
    {
        return $this->storage;
    }

    //######################################################################
    // private 
    //######################################################################

    private $atomatic;
    private $manual;
    private $pipeline;
    private $connector;
};
