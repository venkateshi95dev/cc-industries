<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model;

use I95DevConnect\MessageQueue\Api\I95DevReverseResponseInterface;

/**
 * I95DevReverseResponse model
 */
class I95DevReverseResponse implements I95DevReverseResponseInterface
{
    /**
     * @var object
     */
    public $result;

    /**
     * @var int
     */
    public $messageId;

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $targetId;

    /**
     * @var string
     */
    public $inputData;

    /**
     * @var string
     */
    public $targetcustomerid;

    /**
     * Get result
     *
     * @return bool|object
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get Message id
     *
     * @return int|string
     */
    public function getMessageid()
    {
        return $this->messageId;
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
     * Get Target id
     *
     * @return string
     */
    public function getTargetid()
    {
        return $this->targetId;
    }

    /**
     * Set Result
     *
     * @param bool $result
     * @return $this|I95DevReverseResponse
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Set Message id
     *
     * @param string $messageId
     * @return $this|I95DevReverseResponse
     */
    public function setMessageid($messageId)
    {
        $this->messageId = $messageId;
        return $this;
    }

    /**
     * Set Message
     *
     * @param string $message
     * @return $this|I95DevReverseResponse
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set Target id
     *
     * @param string $targetId
     * @return $this|I95DevReverseResponse
     */
    public function setTargetid($targetId)
    {
        $this->targetId = $targetId;
        return $this;
    }

    /**
     * Get Input Data
     *
     * @return string
     */
    public function getInputdata()
    {
        return $this->inputData;
    }

    /**
     * Set input data
     *
     * @param string $inputData
     * @return $this|I95DevReverseResponse
     */
    public function setInputdata($inputData)
    {
        $this->inputData = $inputData;
        return $this;
    }

    /**
     * Get target customer id
     *
     * @return string
     */
    public function getTargetcustomerid()
    {
        return $this->targetcustomerid;
    }

    /**
     * Set target customer id
     *
     * @param string $targetcustomerid
     * @return $this|I95DevReverseResponse
     */
    public function setTargetcustomerid($targetcustomerid)
    {
        $this->targetcustomerid = $targetcustomerid;
        return $this;
    }
}
