<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\ConfigValues\Product;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for Tax Class
 * @createdBy kavya koona
 */
class AttributeGroup
{
    /**
     *
     * @var ClassModel $attributeSetCollection
     */
    public $attributeSetCollection;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var CollectionFactory
     */
    public $groupCollectionFactory;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * AttributeGroup constructor.
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollection
     * @param Config $eavConfig
     * @param CollectionFactory $groupCollectionFactory
     * @param Data $dataHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollection,
        Config $eavConfig,
        CollectionFactory $groupCollectionFactory,
        Data $dataHelper,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {

        $this->attributeSetCollection = $attributeSetCollection;
        $this->eavConfig = $eavConfig;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->dataHelper = $dataHelper;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * To option array function
     *
     * @return array
     */
    public function toOptionArray()
    {
        $storeScope = ScopeInterface::SCOPE_WEBSITE;
        $attributeSetId = $this->scopeConfig->getValue(
            "i95dev_messagequeue/I95DevConnect_settings/attribute_set",
            $storeScope,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );

        $groupCollection = $this->groupCollectionFactory->create()
                ->addFieldToFilter('attribute_set_id', $attributeSetId)
                ->setOrder('attribute_group_id', 'ASC')
                ->getData(); // product attribute group collection
        $attributeGroups = [];
        foreach ($groupCollection as $group) {
            $attributeGroups[] = [
                'value' => $group['attribute_group_id'],
                'label' => __($group['attribute_group_name'])
            ];
        }
        return $attributeGroups;
    }
}
