<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\ConfigValues\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for Tax Class
 * @createdBy kavya koona
 */
class AttributeSets
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
     *
     * @param CollectionFactory $attributeSetCollection
     * @param Config $eavConfig
     */
    public function __construct(
        CollectionFactory $attributeSetCollection,
        Config $eavConfig
    ) {
        $this->attributeSetCollection = $attributeSetCollection;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Returns attribute sets option array
     *
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray()
    {
        $entityTypeId = $this->eavConfig
                ->getEntityType(ProductAttributeInterface::ENTITY_TYPE_CODE)
                ->getEntityTypeId();
        $attributeSetdata = $this->attributeSetCollection->create()
            ->addFieldToFilter(
                Set::KEY_ENTITY_TYPE_ID,
                $entityTypeId
            )->setOrder('attribute_set_id', 'ASC')->getData();
        $attributeSets = [];
        foreach ($attributeSetdata as $val) {
            $attributeSets[] = ['value' => $val['attribute_set_id'], 'label' => __($val['attribute_set_name'])];
        }

        return $attributeSets;
    }
}
