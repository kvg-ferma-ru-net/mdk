<?php

namespace Innokassa\MDK\Net;

use Innokassa\MDK\Entities\Receipt;
use Innokassa\MDK\Settings\SettingsConn;
use Innokassa\MDK\Net\NetClientInterface;
use Innokassa\MDK\Entities\ConverterAbstract;
use Innokassa\MDK\Entities\Atoms\ReceiptStatus;
use Innokassa\MDK\Exceptions\TransferException;
use Innokassa\MDK\Exceptions\ConverterException;
use Innokassa\MDK\Exceptions\NetConnectException;

/**
 * Реализация TransferInterface
 */
class Transfer implements TransferInterface
{
    /**
     * URL адрес API
     */
    public const API_URL = "https://api.innokassa.ru/v2";

    //######################################################################

    public function __construct(
        NetClientInterface $client,
        ConverterAbstract $converter
    ) {
        $this->client = $client;
        $this->converter = $converter;
    }

    //######################################################################

    /**
     * @inheritDoc
     */
    public function getCashbox(SettingsConn $settingsConn): \stdClass
    {
        $headers = [
            "Authorization: Basic " . base64_encode($settingsConn->getActorId() . ":" . $settingsConn->getActorToken()),
            "Content-type: application/json; charset=utf-8"
        ];

        try {
            $url = self::API_URL . "/c_groups/" . $settingsConn->getCashbox();
            $this->client
                ->reset()
                ->write(NetClientInterface::PATH, $url)
                ->write(NetClientInterface::HEAD, $headers);

            try {
                $this->client->send();
            } catch (NetConnectException $e) {
                throw $e;
            }

            $responseCode = $this->client->read(NetClientInterface::CODE);
            $responseBody = $this->client->read(NetClientInterface::BODY);

            if ($responseCode != 200) {
                throw new TransferException($responseBody, $responseCode);
            }

            $responseBody = json_decode($responseBody);
        } catch (TransferException | NetConnectException $e) {
            throw $e;
        }

        return $responseBody;
    }

    /**
     * @inheritDoc
     */
    public function sendReceipt(SettingsConn $settingsConn, Receipt $receipt): ReceiptStatus
    {
        $receiptStatus = new ReceiptStatus(ReceiptStatus::PREPARED);
        $headers = [
            "Authorization: Basic " . base64_encode($settingsConn->getActorId() . ":" . $settingsConn->getActorToken()),
            "Content-type: application/json; charset=utf-8"
        ];

        try {
            try {
                $body = $this->converter->receiptToArray($receipt);
            } catch (ConverterException $e) {
                throw new TransferException('converter error: ' . $e->getMessage(), ReceiptStatus::ERROR);
            }

            $url = self::API_URL . "/c_groups/" . $settingsConn->getCashbox() . "/receipts/" . $receipt->getReceiptId();
            $sBody = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
            $this->client
                ->reset()
                ->write(NetClientInterface::PATH, $url)
                ->write(NetClientInterface::HEAD, $headers)
                ->write(NetClientInterface::TYPE, 'POST')
                ->write(NetClientInterface::BODY, $sBody);

            $responseCode = $this->client->read(NetClientInterface::CODE);

            try {
                $this->client->send();
            } catch (NetConnectException $e) {
                throw $e;
            }

            $responseCode = $this->client->read(NetClientInterface::CODE);
            $responseBody = $this->client->read(NetClientInterface::BODY);
            $receiptStatus = new ReceiptStatus($responseCode);

            if (!($responseCode == 201 || $responseCode == 202)) {
                throw new TransferException($responseBody, $responseCode);
            }
        } catch (TransferException | NetConnectException $e) {
            throw $e;
        }

        return $receiptStatus;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    /** @var NetClientInterface */
    private $client = null;

    /** @var ConverterAbstract */
    private $converter = null;
}
