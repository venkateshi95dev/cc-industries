<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Api\Data;

/**
 * Represents Data Object for a ERP MessageQueue Data
 */
interface I95DevErpDataInterface
{
    public const DATA_ID = 'data_id';
    public const MSG_ID = 'msg_id';
    public const DATA_STRING = 'data_string';

    /**
     * Get data id
     *
     * @return int|null
     */
    public function getDataId();

    /**
     * Set data id
     *
     * @param int $dataId
     * @return $this
     */
    public function setDataId($dataId);

    /**
     * Get message id
     *
     * @return int|null
     */
    public function getMsgId();

    /**
     * Set message id
     *
     * @param int $msgId
     * @return $this
     */
    public function setMsgId($msgId);

    /**
     * Get data string
     *
     * @return string
     */
    public function getDataString();

    /**
     * Get data string
     *
     * @param string $dataString
     * @return $this
     */
    public function setDataString($dataString);
}
