<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * get and set context field
 */
interface ContextInterface extends ExtensibleDataInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    public const CLIENT_ID = 'ClientId';
    public const SUBSCRIPTION_KEY = 'SubscriptionKey';
    public const INSTANCE_TYPE = 'InstanceType';
    public const ENDPOINT_CODE = 'EndpointCode';
    public const SCHEDULER_TYPE = 'SchedulerType';
    public const REQUEST_TYPE = 'RequestType';
    public const SCHEDULER_ID = 'SchedulerId';

    /**
     * Get clientId
     *
     * @return string|null
     * @api
     */
    public function getClientId();

    /**
     * Set clientId
     *
     * @param string $clientId
     * @return $this
     * @api
     */
    public function setClientId($clientId);

    /**
     * Set subscriptionKey
     *
     * @param string $subscriptionKey
     * @return $this
     * @api
     */
    public function setSubscriptionKey($subscriptionKey);

    /**
     * Get subscriptionKey
     *
     * @return string|null
     * @api
     */
    public function getSubscriptionKey();

    /**
     * Set endpointCode
     *
     * @param string $endpointCode
     * @return $this
     * @api
     */
    public function setEndpointCode($endpointCode);

    /**
     * Get endpointCode
     *
     * @return string|null
     * @api
     */
    public function getEndpointCode();

    /**
     * Set instanceType
     *
     * @param string $instanceType
     * @return $this
     * @api
     */
    public function setInstanceType($instanceType);

    /**
     * Get instanceType
     *
     * @return string|null
     * @api
     */
    public function getInstanceType();

    /**
     * Get schedulerType
     *
     * @return string|null
     * @api
     */
    public function getSchedulerType();

    /**
     * Set schedulerType
     *
     * @param string $schedulerType
     * @return $this
     * @api
     */
    public function setSchedulerType($schedulerType);

    /**
     * Set requestType
     *
     * @param string $requestType
     * @return $this
     * @api
     */
    public function setRequestType($requestType);

    /**
     * Get requestType
     *
     * @return string|null
     * @api
     */
    public function getRequestType();

    /**
     * Set schedulerId
     *
     * @param string|null $schedulerId
     * @return $this
     * @api
     */
    public function setSchedulerId($schedulerId);

    /**
     * Get schedulerId
     *
     * @return string|null
     * @api
     */
    public function getSchedulerId();
}
