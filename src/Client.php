<?php

namespace Innokassa\MDK;

use Innokassa\MDK\Logger\LoggerInterface;
use Innokassa\MDK\Services\ManualInterface;
use Innokassa\MDK\Services\PrinterInterface;
use Innokassa\MDK\Services\PipelineInterface;
use Innokassa\MDK\Settings\SettingsInterface;
use Innokassa\MDK\Services\AutomaticInterface;
use Innokassa\MDK\Services\ConnectorInterface;
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
        PrinterInterface $printer,
        ConnectorInterface $connector,
        LoggerInterface $logger
    ) {
        $this->settings = $settings;
        $this->adapter = $adapter;
        $this->storage = $storage;

        $this->atomatic = $atomatic;
        $this->manual = $manual;
        $this->pipeline = $pipeline;
        $this->printer = $printer;
        $this->connector = $connector;
        $this->logger = $logger;
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
     * Получить сервис печати чеков
     *
     * @return PrinterInterface
     */
    public function servicePrinter(): PrinterInterface
    {
        return $this->printer;
    }

    /**
     * Получить сервис тестирования соединения с сервером фискализации
     *
     * @return ConnectorInterface
     */
    public function serviceConnector(): ConnectorInterface
    {
        return $this->connector;
    }

    //######################################################################

    /**
     * Получить компонент настроек
     *
     * @return SettingsInterface
     */
    public function componentSettings(): SettingsInterface
    {
        return $this->settings;
    }

    /**
     * Получить компонент адаптера чеков
     *
     * @return ReceiptAdapterInterface
     */
    public function componentAdapter(): ReceiptAdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Получить компонент хранилища чеков
     *
     * @return ReceiptStorageInterface
     */
    public function componentStorage(): ReceiptStorageInterface
    {
        return $this->storage;
    }

    /**
     * Получить компонент логирования
     *
     * @return LoggerInterface
     */
    public function componentLogger(): LoggerInterface
    {
        return $this->logger;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $atomatic;
    private $manual;
    private $pipeline;
    private $printer;
    private $connector;
    private $logger;
}
