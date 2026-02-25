<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Configurable product class Data
 */
class Data extends AbstractHelper
{
    /**
     * ScopeConfig for system Configuration
     *
     * @var string
     */
    public $scopeConfig;

    /**
     * @param Context              $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Check configurable product is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            'configurableproducts/i95dev_enabled_settings/is_enabled',
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
