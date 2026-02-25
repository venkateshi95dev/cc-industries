<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Api;

/**
 * Reverse Response Interface.
 */
interface I95DevReverseResponseInterface
{
    /**
     * Get status
     *
     * @return bool
     */
    public function getResult();

    /**
     * Get message Id
     *
     * @return string
     */
    public function getMessageid();

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Get message
     *
     * @return string
     */
    public function getTargetid();

    /**
     * Get input data
     *
     * @return string
     */
    public function getInputdata();

    /**
     * Set result
     *
     * @param bool $result
     * @return $this
     */
    public function setResult($result);

    /**
     * Set message id
     *
     * @param string $messageId
     * @return $this
     */
    public function setMessageid($messageId);

    /**
     * Set message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * Set target id
     *
     * @param string $targetId
     * @return $this
     */
    public function setTargetid($targetId);

    /**
     * Set input data
     *
     * @param [] $inputData
     * @return $this
     */
    public function setInputdata($inputData);

    /**
     * Set target customer id
     *
     * @param string $targetcustomerid
     * @return $this
     */
    public function setTargetcustomerid($targetcustomerid);
}
