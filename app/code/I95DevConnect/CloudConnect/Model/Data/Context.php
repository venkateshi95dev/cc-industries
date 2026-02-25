<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model\Data;

use I95DevConnect\CloudConnect\Api\Data\ContextInterface;
use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Framework\Api\AttributeValueFactory;

/**
 * Class Context to implement Context Interface
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Context extends AbstractExtensibleObject implements ContextInterface
{
    /**
     * @var string
     */
    public $ClientId;

    /**
     * @var string
     */
    public $SubscriptionKey;

    /**
     * @var string
     */
    public $InstanceType;

    /**
     * @var string
     */
    public $EndpointCode;

    /**
     * @var string
     */
    public $SchedulerType;

    /**
     * @var string
     */
    public $RequestType;

    /**
     * @var string
     */
    public $SchedulerId = 0;

    /**
     * @inheritdoc
     */
    public function getClientId()
    {
        return $this->_get(self::CLIENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setClientId($clientId)
    {
        return $this->_set(self::CLIENT_ID, $clientId);
    }

    /**
     * @inheritdoc
     */
    public function getSubscriptionKey()
    {
        return $this->_get(self::SUBSCRIPTION_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setSubscriptionKey($subscriptionKey)
    {
        return $this->_set(self::SUBSCRIPTION_KEY, $subscriptionKey);
    }

    /**
     * @inheritdoc
     */
    public function getEndpointCode()
    {
        return $this->_get(self::ENDPOINT_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setEndpointCode($endpointCode)
    {
        return $this->_set(self::ENDPOINT_CODE, $endpointCode);
    }

    /**
     * @inheritdoc
     */
    public function getInstanceType()
    {
        return $this->_get(self::INSTANCE_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setInstanceType($instanceType)
    {
        return $this->_set(self::INSTANCE_TYPE, $instanceType);
    }

    /**
     * @inheritdoc
     */
    public function getSchedulerType()
    {
        return $this->_get(self::SCHEDULER_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setSchedulerType($schedulerType)
    {
        return $this->_set(self::SCHEDULER_TYPE, $schedulerType);
    }

    /**
     * @inheritdoc
     */
    public function setRequestType($requestType)
    {
        return $this->_set(self::REQUEST_TYPE, $requestType);
    }

    /**
     * @inheritdoc
     */
    public function getRequestType()
    {
        return $this->_get(self::REQUEST_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setSchedulerId($schedulerId)
    {
        return $this->_set(self::SCHEDULER_ID, $schedulerId);
    }

    /**
     * @inheritdoc
     */
    public function getSchedulerId()
    {
        return $this->_get(self::SCHEDULER_ID);
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
