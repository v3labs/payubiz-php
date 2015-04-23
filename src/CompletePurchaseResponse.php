<?php

namespace V3labs\PayUbiz;

class CompletePurchaseResponse
{
    const STATUS_COMPLETED = 'Completed';
    const STATUS_PENDING   = 'Pending';
    const STATUS_FAILED    = 'Failed';
    const STATUS_TAMPERED  = 'Tampered';

    /** @var PayUbiz */
    private $client;

    /** @var array */
    private $params;

    public function __construct(PayUbiz $client, array $params)
    {
        $this->client = $client;
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        if ($this->checksumIsValid()) {
            switch (strtolower($this->getTransactionStatus())) {
                case 'success':
                    return self::STATUS_COMPLETED;
                    break;
                case 'pending':
                    return self::STATUS_PENDING;
                    break;
                case 'failure':
                default:
                    return self::STATUS_FAILED;
            }
        }

        return self::STATUS_TAMPERED;
    }

    /**
     * @return string|null
     */
    public function getTransactionId()
    {
        return array_key_exists('mihpayid', $this->params) ? (string)$this->params['mihpayid'] : null;
    }

    /**
     * @return string|null
     */
    public function getTransactionStatus()
    {
        return array_key_exists('status', $this->params) ? (string)$this->params['status'] : null;
    }

    /**
     * @return string|null
     */
    public function getChecksum()
    {
        return array_key_exists('hash', $this->params) ? (string)$this->params['hash'] : null;
    }

    /**
     * @return bool
     */
    public function checksumIsValid()
    {
        return false;
    }
}