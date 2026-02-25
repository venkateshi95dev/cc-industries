<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Helper;

use I95DevConnect\MessageQueue\Model\EntityFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Helper class to get i95Dev configurations
 */
class ConfigHelper extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    /**
     * @var array
     */
    public $reverseSkipEntities;
    /**
     * @var array
     */
    public $forwardSkipEntities;

    /**
     * @var EntityFactory
     */
    public $entityTypeModel;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * ConfigHelper constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param EntityFactory $entityTypeModel
     * @param array $reverseSkipEntities
     * @param array $forwardSkipEntities
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EntityFactory $entityTypeModel,
        $reverseSkipEntities,
        $forwardSkipEntities,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->reverseSkipEntities = $reverseSkipEntities;
        $this->forwardSkipEntities = $forwardSkipEntities;
        $this->storeManager = $storeManager;
        $this->entityTypeModel = $entityTypeModel;
    }

    public const XML_PATH_GENERIC_CONNECT_ERP_CRM = 'i95dev_adapter_configurations/enabled_disabled/crmerp';

    /**
     * Get erpcrm config value
     *
     * @return string
     */
    public function getSelctErpCrmConfig()
    {
        $storeScope = ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue(
            self::XML_PATH_GENERIC_CONNECT_ERP_CRM,
            $storeScope,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
    }

    /**
     * List of entities that needs to be skipped for forward sync
     *
     * @return array
     */
    public function getForwardSkipEntities()
    {
        $entities = $this->entityTypeModel->create()->getCollection()->addFieldToSelect('entity_code');
        $entities->addFieldToFilter('support_for_outbound', 0);
        if (!$entities->getSize() > 0) {
            $this->forwardSkipEntities = [];
        } else {
            $this->forwardSkipEntities = [];
            foreach ($entities as $skipEntities) {
                $this->forwardSkipEntities[] = $skipEntities->getEntityCode();
            }
        }
        return $this->forwardSkipEntities;
    }

    /**
     * List of entities that needs to be skipped for reverse sync
     *
     * @return array
     */
    public function getReverseSkipEntities()
    {
        $entities = $this->entityTypeModel->create()->getCollection()->addFieldToSelect('entity_code');
        $entities->addFieldToFilter('support_for_inbound', 0);
        if (!$entities->getSize() > 0) {
            $this->reverseSkipEntities = [];
        } else {
            foreach ($entities as $skipEntities) {
                $this->reverseSkipEntities[] = $skipEntities->getEntityCode();
            }
        }
        return $this->reverseSkipEntities;
    }
}
