<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public const CUSTOMER = 0;
    public const CUSTOMERDISCOUNTGROUP = 1;
    public const ALLCUSTOMERS = 2;
    public const CAMPAIGN = 3;
    public const ITEM = 0;
    public const ITEMDISCOUNTGROUP = 1;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    // @codingStandardsIgnoreStart
    public static function getCdgType($cnst)
    {
        return constant('self::'. $cnst);
    }
    // @codingStandardsIgnoreEnd

    /**
     * Get Discount groups module configuration
     *
     * @return mixed*/
    public function isDiscountGroupsEnabled()
    {
        return $this->scopeConfig
            ->getValue(
                'i95devconnect_discountgroups/discountgroups_enabled_settings/enable_dg',
                ScopeInterface::SCOPE_WEBSITE,
                $this->storeManager->getStore()->getWebsiteId()
            );
    }

    public function isDiscountGroupsEnabledInAnyWebsite()
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            $isEnabled = $this->scopeConfig->isSetFlag(
                'i95devconnect_discountgroups/discountgroups_enabled_settings/enable_dg',
                ScopeInterface::SCOPE_WEBSITE,
                $website->getId()
            );
    
            if ($isEnabled) {
                return true; // enabled in at least one website
            }
        }
    
        return false; // not enabled in any website
    }   
    
    
}
