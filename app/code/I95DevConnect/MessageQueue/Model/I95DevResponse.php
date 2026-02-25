<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model;

use I95DevConnect\MessageQueue\Api\I95DevConnect\MessageQueue\Api\I95DevReverseResponseInterface;
use I95DevConnect\MessageQueue\Api\I95DevResponseInterface;

/**
 * Data object for response
 */
class I95DevResponse implements I95DevResponseInterface
{
    /**
     * @var string
     */
    public $status;

    /**
     * @var array
     */
    public $resultData;

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $result;

    /**
     * @var string
     */
    public $code;

    /**
     * Get status
     *
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get Result
     *
     * @return bool|string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get Result Data
     *
     * @return array|I95DevReverseResponseInterface[]
     */
    public function getResultdata()
    {
        return $this->resultData;
    }

    /**
     * Get Message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get Code
     *
     * @return int|string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set status
     *
     * @param bool $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Set Result
     *
     * @param bool $result
     * @return $this|I95DevResponse
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Set result data
     *
     * @param string $resultData
     * @return $this|I95DevResponse
     */
    public function setResultdata($resultData)
    {
        $this->resultData = $resultData;
        return $this;
    }

    /**
     * Set Message
     *
     * @param string $message
     * @return $this|I95DevResponse
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set code
     *
     * @param int $code
     * @return $this|I95DevResponse
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
}
