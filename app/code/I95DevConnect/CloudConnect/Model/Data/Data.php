<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model\Data;

use I95DevConnect\CloudConnect\Api\Data\DataInterface;
use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\Api\AttributeValueFactory;

/**
 * Class Data to implement Data Interface
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Data extends AbstractExtensibleObject implements DataInterface
{
    /**
     * @var string
     */
    public $TargetId;

    /**
     * @var string
     */
    public $SourceId;

    /**
     * @var string
     */
    public $MagentoId;

    /**
     * @var string
     */
    public $Reference;

    /**
     * @var string
     */
    public $InputData;

    /**
     * @var int
     */
    public $MessageId;

    /**
     * @var string
     */
    public $Message;

    /**
     * @var string
     */
    public $Status;

    /**
     * @inheritdoc
     */
    public function getTargetId()
    {
        return $this->_get(self::TARGET_ID);
    }

    /**
     * @inheritdoc
     */
    public function setTargetId($targetId)
    {
        return $this->_set(self::TARGET_ID, $targetId);
    }

    /**
     * @inheritdoc
     */
    public function getSourceId()
    {
        return $this->_get(self::SOURCE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSourceId($sourceId)
    {
        return $this->_set(self::SOURCE_ID, $sourceId);
    }

    /**
     * @inheritdoc
     */
    public function getMagentoId()
    {
        return $this->_get(self::MAGENTO_ID);
    }

    /**
     * @inheritdoc
     */
    public function setMagentoId($magentoId)
    {
        return $this->_set(self::MAGENTO_ID, $magentoId);
    }

    /**
     * @inheritdoc
     */
    public function getReference()
    {
        return $this->_get(self::REFERNCE);
    }

    /**
     * @inheritdoc
     */
    public function setReference($reference)
    {
        return $this->_set(self::REFERNCE, $reference);
    }

    /**
     * @inheritdoc
     */
    public function getInputData()
    {
        return $this->_get(self::INPUT_DATA);
    }

    /**
     * @inheritdoc
     */
    public function setInputData($inputData)
    {
        return $this->_set(self::INPUT_DATA, $inputData);
    }

    /**
     * @inheritdoc
     */
    public function getMessageId()
    {
        return $this->_get(self::MESSAGE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setMessageId($messageId)
    {
        return $this->_set(self::MESSAGE_ID, $messageId);
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->_get(self::MESSAGE);
    }

    /**
     * @inheritdoc
     */
    public function setMessage($message)
    {
        return $this->_set(self::MESSAGE, $message);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->_set(self::STATUS, $status);
    }

    /**
     * Set value for the given key
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    // @codingStandardsIgnoreStart
    public function _set($key, $value)
    {
        $this->$key = $value;
        return $this;
    }
    // @codingStandardsIgnoreEnd
}
