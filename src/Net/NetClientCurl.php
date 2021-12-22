<?php

namespace Innokassa\MDK\Net;

use Innokassa\MDK\Exceptions\NetConnectException;

/**
 * Реаизация NetClientInterface с использованием curl
 */
class NetClientCurl implements NetClientInterface
{
    public function __construct()
    {
        $this->curl = curl_init();
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }

    /**
     * @inheritDoc
     */
    public function write(int $code, $data): NetClientInterface
    {
        switch($code)
        {
            case static::PATH:
                curl_setopt($this->curl, CURLOPT_URL, $data);
                break;
            case static::TYPE:
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $data);
                break;
            case static::HEAD:
                curl_setopt($this->curl, CURLOPT_HTTPHEADER, $data);
                break;
            case static::BODY:
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                break;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function send(): NetClientInterface
    {
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($this->curl, CURLINFO_HEADER_OUT, 1);

        $this->response = curl_exec($this->curl);

        if(curl_errno($this->curl) != 0)
            throw new NetConnectException(curl_error($this->curl), curl_errno($this->curl));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function read(int $code)
    {
        switch($code)
        {
            case static::CODE:
                return curl_getinfo($this->curl, CURLINFO_RESPONSE_CODE);
            case static::BODY:
                return $this->response;
            case static::HEAD:
                return curl_getinfo($this->curl, CURLINFO_HEADER_OUT);
            default:
                return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function reset(): NetClientInterface
    {
        curl_reset($this->curl);
        $this->response = '';

        return $this;
    }

    //######################################################################
    // PRIVATE
    //######################################################################

    private $curl = null;
    private $response = '';
};
