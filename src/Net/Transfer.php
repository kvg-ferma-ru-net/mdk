<?php

namespace Innokassa\MDK\Net;

use Innokassa\MDK\Net\NetClientInterface;
use Innokassa\MDK\Entities\Receipt;
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
    public const API_URL = "https://api.kassavoblake.com/v2";

    //######################################################################

    public function __construct(
        NetClientInterface $client,
        ConverterAbstract $converter,
        string $actorId,
        string $actorToken,
        string $cashbox
    ) {
        $this->client = $client;
        $this->converter = $converter;

        $this->actorId = $actorId;
        $this->actorToken = $actorToken;
        $this->cashbox = $cashbox;
        $this->headers = [
            "Authorization: Basic " . base64_encode($this->actorId . ":" . $this->actorToken),
            "Content-type: application/json; charset=utf-8"
        ];
    }

    //######################################################################

    /**
     * @inheritDoc
     */
    public function getCashbox(): object
    {
        $this->client
            ->reset()
            ->write(NetClientInterface::PATH, self::API_URL . "/c_groups/" . $this->cashbox)
            ->write(NetClientInterface::HEAD, $this->headers);

        try {
            $this->client->send();
        } catch (NetConnectException $e) {
            throw new TransferException($e->getMessage(), $e->getCode());
        }

        $responseCode = $this->client->read(NetClientInterface::CODE);
        $responseBody = json_decode($this->client->read(NetClientInterface::BODY));

        if ($responseCode != 200) {
            throw new TransferException($responseBody, $responseCode);
        }

        return $responseBody;
    }

    /**
     * @inheritDoc
     */
    public function sendReceipt(Receipt $receipt, bool $needAgent = false): Receipt
    {
        try {
            $body = $this->converter->receiptToArray($receipt);
        } catch (ConverterException $e) {
            $receipt->setStatus(new ReceiptStatus(ReceiptStatus::ERROR));
            throw new TransferException('converter error: ' . $e->getMessage(), ReceiptStatus::ERROR);
        }

        $sEndPoint = ($needAgent ? 'online_store_agent' : 'online_store');
        $this->client
            ->reset()
            ->write(
                NetClientInterface::PATH,
                self::API_URL . "/c_groups/" . $this->cashbox . "/receipts/$sEndPoint/" . $receipt->getUUID()->get()
            )
            ->write(NetClientInterface::HEAD, $this->headers)
            ->write(NetClientInterface::TYPE, 'POST')
            ->write(NetClientInterface::BODY, json_encode($body, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP));

        try {
            $this->client->send();
        } catch (NetConnectException $e) {
            $receipt->setStatus(new ReceiptStatus(ReceiptStatus::PREPARED));
            throw new TransferException($e->getMessage(), $e->getCode());
        }

        $responseCode = $this->client->read(NetClientInterface::CODE);
        $responseBody = $this->client->read(NetClientInterface::BODY);

        $receipt->setStatus(new ReceiptStatus($responseCode));

        if ($responseCode != 201 && $responseCode != 202) {
            throw new TransferException($responseBody, $responseCode);
        }

        return $receipt;
    }

    //**********************************************************************

    /**
     * @inheritDoc
     */
    public function getReceipt(Receipt $receipt): Receipt
    {
        $this->client
            ->reset()
            ->write(
                NetClientInterface::PATH,
                self::API_URL . "/c_groups/" . $this->cashbox . "/receipts/" . $receipt->getUUID()->get()
            )
            ->write(NetClientInterface::HEAD, $this->headers);

        try {
            $this->client->send();
        } catch (NetConnectException $e) {
            $receipt->setStatus(new ReceiptStatus(ReceiptStatus::PREPARED));
            throw new TransferException($e->getMessage(), $e->getCode());
        }

        $responseCode = $this->client->read(NetClientInterface::CODE);
        $responseBody = $this->client->read(NetClientInterface::BODY);

        $receipt->setStatus(new ReceiptStatus($responseCode));

        if ($responseCode != 200 && $responseCode != 202) {
            throw new TransferException($responseBody, $responseCode);
        }

        return $receipt;
    }

    //**********************************************************************

    /**
     * @inheritDoc
     */
    public function getReceiptLink(Receipt $receipt): string
    {
        return self::API_URL . "/receipt/show/" . $receipt->getUUID()->get();
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $client = null;
    private $converter = null;
    private $actorId = '';
    private $actorToken = '';
    private $cashbox = '';
}
