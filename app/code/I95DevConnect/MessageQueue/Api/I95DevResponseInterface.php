<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Api;

/**
 * Response Interface.
 */
interface I95DevResponseInterface
{
    /**
     * Get result
     *
     * @return bool
     */
    public function getResult();

    /**
     * Get result data
     *
     * @return I95DevConnect\MessageQueue\Api\I95DevReverseResponseInterface[] | null
     */
    public function getResultdata();

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Get code
     *
     * @return int
     */
    public function getCode();

    /**
     * Set result
     *
     * @param bool $result
     * @return $this
     */
    public function setResult($result);

    /**
     * Set result data
     *
     * @param [] $resultData
     * @return $this
     */
    public function setResultdata($resultData);

    /**
     * Set message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * Set code
     *
     * @param int $code
     * @return $this
     */
    public function setCode($code);
}
