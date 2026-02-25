<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model\Data;

use I95DevConnect\CloudConnect\Api\Data\ResponseInterface;
use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\Api\AttributeValueFactory;

/**
 * Class Response to implement Response Interface
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Response extends AbstractExtensibleObject implements ResponseInterface
{
    /**
     * @var boolean
     */
    public $IsConfigurationUpdated;

    /**
     * @var boolean
     */
    public $IsShippingMappingUpdated;

    /**
     * @var boolean
     */
    public $IsPaymentMappingUpdated;

    /**
     * @var boolean
     */
    public $IsProductAttributeMappingUpdated;

    /**
     * @var string
     */
    public $ResultData;

    /**
     * @var string
     */
    public $Message;

    /**
     * @var string
     */
    public $Status;

    /**
     * @var boolean
     */
    public $IsSubscriptionActive;

    /**
     * @var boolean
     */
    public $Result;

    /**
     * @var string
     */
    public $SchedulerId;

    /**
     * @inheritdoc
     */
    public function getIsConfigurationUpdated()
    {
        return $this->_get(self::IS_CONFIGURATION_UPDATED);
    }

    /**
     * @inheritdoc
     */
    public function setIsConfigurationUpdated($isConfigurationUpdated)
    {
        return $this->_set(self::IS_CONFIGURATION_UPDATED, $isConfigurationUpdated);
    }

    /**
     * @inheritdoc
     */
    public function getIsPaymentMappingUpdated()
    {
        return $this->_get(self::IS_PAYMENT_UPDATED);
    }

    /**
     * @inheritdoc
     */
    public function setIsPaymentMappingUpdated($isPaymentMappingUpdated)
    {
        return $this->_set(self::IS_PAYMENT_UPDATED, $isPaymentMappingUpdated);
    }

    /**
     * @inheritdoc
     */
    public function getIsShippingMappingUpdated()
    {
        return $this->_get(self::IS_SHIPPING_UPDATED);
    }

    /**
     * @inheritdoc
     */
    public function setIsShippingMappingUpdated($isShippingMappingUpdated)
    {
        return $this->_set(self::IS_SHIPPING_UPDATED, $isShippingMappingUpdated);
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
     * @inheritdoc
     */
    public function getResultData()
    {
        return $this->_get(self::RESULT_DATA);
    }

    /**
     * @inheritdoc
     */
    public function setResultData($resultData)
    {
        return $this->_set(self::RESULT_DATA, $resultData);
    }

    /**
     * @inheritdoc
     */
    public function getIsSubscriptionActive()
    {
        return $this->_get(self::IS_SUBSCRIPTION_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setIsSubscriptionActive($isSubscriptionActive)
    {
        return $this->_set(self::IS_SUBSCRIPTION_ACTIVE, $isSubscriptionActive);
    }

    /**
     * @inheritdoc
     */
    public function getResult()
    {
        return $this->_get(self::RESULT);
    }

    /**
     * @inheritdoc
     */
    public function setResult($result)
    {
        return $this->_set(self::RESULT, $result);
    }

    /**
     * @inheritdoc
     */
    public function getSchedulerId()
    {
        return $this->_get(self::SCHEDULER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSchedulerId($schedulerId)
    {
        return $this->_set(self::SCHEDULER_ID, $schedulerId);
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

    public function setIsProductAttributeMappingUpdated($isProductAttributeMappingUpdated)
    {
        return $this->_set(self::IS_PRODUCT_ATTRIBUTE_UPDATED, $isProductAttributeMappingUpdated);
    }

    public function getIsProductAttributeMappingUpdated()
    {
        return $this->_get(self::IS_PRODUCT_ATTRIBUTE_UPDATED);
    }

    // @codingStandardsIgnoreEnd
}
