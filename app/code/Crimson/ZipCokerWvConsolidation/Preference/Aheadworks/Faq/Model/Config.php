<?php

namespace Crimson\ZipCokerWvConsolidation\Preference\Aheadworks\Faq\Model;

use Magento\Customer\Model\Session;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * FAQ config model
 */
class Config extends \Aheadworks\Faq\Model\Config
{

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $session
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Session $session,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($scopeConfig,$session,$storeManager);
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * Get customer groups with disabled FAQ
     *
     * @return array
     */
    public function getGroupsWithDisabledFaq()
    {
        $settingsValue = $this->scopeConfig->getValue(
            self::XML_PATH_GROUPS_WITH_DISABLED_FAQ,
            ScopeInterface::SCOPE_STORE
        );

        return explode(',', $settingsValue?$settingsValue:'');
    }
}
