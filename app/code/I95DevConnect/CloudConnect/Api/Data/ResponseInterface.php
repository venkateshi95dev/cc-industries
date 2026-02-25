<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * get and set  response field
 */
interface ResponseInterface extends ExtensibleDataInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    public const IS_CONFIGURATION_UPDATED = 'IsConfigurationUpdated';
    public const IS_SHIPPING_UPDATED = 'IsShippingMappingUpdated';
    public const IS_PAYMENT_UPDATED = 'IsPaymentMappingUpdated';
    public const IS_PRODUCT_ATTRIBUTE_UPDATED = 'IsProductAttributeMappingUpdated'; // Added by Subhan
    public const RESULT_DATA = 'ResultData';
    public const MESSAGE = 'Message';
    public const STATUS = 'Status';
    public const IS_SUBSCRIPTION_ACTIVE = 'IsSubscriptionActive';
    public const RESULT = 'Result';
    public const SCHEDULER_ID = 'SchedulerId';

    /**
     * Set isConfigurationUpdated
     *
     * @param boolean $isConfigurationUpdated
     * @return $this
     * @api
     */
    public function setIsConfigurationUpdated($isConfigurationUpdated);

    /**
     * Get isConfigurationUpdated
     *
     * @return boolean|null
     * @api
     */
    public function getIsConfigurationUpdated();

    /**
     * Set isShippingMappingUpdated
     *
     * @param boolean $isShippingMappingUpdated
     * @return $this
     * @api
     */
    public function setIsShippingMappingUpdated($isShippingMappingUpdated);

    /**
     * Get isShippingMappingUpdated
     *
     * @return boolean|null
     * @api
     */
    public function getIsShippingMappingUpdated();

    /**
     * Set isPaymentMappingUpdated
     *
     * @param boolean $isPaymentMappingUpdated
     * @return $this
     * @api
     */
    public function setIsPaymentMappingUpdated($isPaymentMappingUpdated);

    /**
     * Get isPaymentMappingUpdated
     *
     * @return boolean|null
     * @api
     */
    public function getIsPaymentMappingUpdated();

    /**
     * Set cloud message
     *
     * @param string $message
     * @return $this
     * @api
     */
    public function setMessage($message);

    /**
     * Get cloud message
     *
     * @return string|null
     * @api
     */
    public function getMessage();

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     * @api
     */
    public function setStatus($status);

    /**
     * Get status
     *
     * @return boolean|null
     * @api
     */
    public function getStatus();

    /**
     * Get dataModel
     *
     * @return \I95DevConnect\CloudConnect\Api\Data\DataInterface[] | null
     */
    public function getResultData();

    /**
     * Set dataModel
     *
     * @param \I95DevConnect\CloudConnect\Api\Data\DataInterface[] $resultData
     * @return $this
     */
    public function setResultData($resultData);

    /**
     * Set IsSubscriptionActive
     *
     * @param string $isSubscriptionActive
     * @return $this
     * @api
     */
    public function setIsSubscriptionActive($isSubscriptionActive);

    /**
     * Get IsSubscriptionActive
     *
     * @return string|null
     * @api
     */
    public function getIsSubscriptionActive();

    /**
     * Set Result
     *
     * @param string $result
     * @return $this
     * @api
     */
    public function setResult($result);

    /**
     * Get Result
     *
     * @return string|null
     * @api
     */
    public function getResult();

    /**
     * Set SchedulerId
     *
     * @param int $schedulerId
     * @return $this
     * @api
     */
    public function setSchedulerId($schedulerId);

    /**
     * Get SchedulerId
     *
     * @return int|null
     * @api
     */
    public function getSchedulerId();

    /**
     * Set isProductAttributeMappingUpdated
     *
     * @param boolean $isProductAttributeMappingUpdated
     * @return $this
     * @api
     */
    public function setIsProductAttributeMappingUpdated($isProductAttributeMappingUpdated);

    /**
     * Get isProductAttributeMappingUpdated
     *
     * @return boolean|null
     * @api
     */
    public function getIsProductAttributeMappingUpdated();
}
