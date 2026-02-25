<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\Data;

use I95DevConnect\MessageQueue\Api\Data\I95DevErpDataInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * I95dev ERP Data Model
 */
class I95DevErpData extends AbstractModel implements I95DevErpDataInterface
{
    /**
     * Get Data Id
     *
     * @return void
     */
    public function getDataId()
    {
        $this->getData(self::DATA_ID);
    }

    /**
     * Set Data Id
     *
     * @param int $dataId
     * @return $this
     */
    public function setDataId($dataId)
    {
        return $this->setData(self::DATA_ID, $dataId);
    }

    /**
     * Get Message Queue Id
     *
     * @return int|null
     */
    public function getMsgId()
    {
        return $this->getData(self::MSG_ID);
    }

    /**
     * Set Message Queue Id
     *
     * @param int $msgId
     * @return $this
     */
    public function setMsgId($msgId)
    {
        return $this->setData(self::MSG_ID, $msgId);
    }

    /**
     * Get data string
     *
     * @return string
     */
    public function getDataString()
    {
        return $this->getData(self::DATA_STRING);
    }

    /**
     * Get data string
     *
     * @param string $dataString
     * @return $this
     */
    public function setDataString($dataString)
    {
        return $this->setData(self::DATA_STRING, $dataString);
    }
}
