<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PaymentMapping
 */

namespace I95DevConnect\PaymentMapping\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Helper class for payment mapping for common functionality
 */
class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * Constructor for DI
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get payment mapping status
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            'i95dev_adapter_configurations/i95dev_payment_mapping/enabled',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
    }

    /**
     * Get cloud connector status
     *
     * @return bool
     */
    public function isCloudEnabled()
    {
        return $this->scopeConfig->getValue(
            'i95dev_adapter_configurations/enabled_disabled/enabled',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
    }
}
