<?php
/**
 * @namespace   Crimson
 * @module      Catalog
 * @author      Ryan Simmons
 * @email       rsimmons@crimsonagility.com
 * @date        6/20/2019 9:49 AM
 * @brief       Sorts attributes so they will be displayed in the correct order (the same order as they are in
 *              in their respective attribute sets in the backend) on configurable PDPs
 */

namespace Crimson\Catalog\Model\Service;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;

/**
 * Class SortSuperAttributes
 * @package Crimson\Catalog\Model\Service
 */
class SortSuperAttributes
{
    /** @var CollectionFactory  */
    protected $_attributeCollectionFactory;

    public function __construct(
        CollectionFactory $attributeCollectionFactory
    ) {
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @param int                                                                      $attributeSetId
     * @param Attribute[] $attributes
     *
     * @return Attribute[]
     */
    public function sort(int $attributeSetId, $attributes): array
    {
        $attributesByCode = [];
        foreach($attributes as $attribute) {
            $attributesByCode[$attribute->getProductAttribute()->getAttributeCode()] = $attribute;
        }

        $sortedAttributes = [];

        $sortedAttributeCodes = $this->_sortAttributesByAttributeSet($attributeSetId, array_keys($attributesByCode));

        if(!$sortedAttributeCodes) {
            return $attributesByCode;
        }

        foreach($sortedAttributeCodes as $attributeCode) {
            if(isset($attributesByCode[$attributeCode])) {
                $sortedAttributes[$attributeCode] = $attributesByCode[$attributeCode];
            }
        }

        foreach($attributesByCode as $attributeCode => $attribute) {
            if(!isset($sortedAttributes[$attributeCode])) {
                $sortedAttributes[$attributeCode] = $attribute;
            }
        }

        return $sortedAttributes;
    }

    /**
     * @param int      $attributeSetId
     * @param string[] $attributeCodes
     *
     * @return string[]
     */
    protected function _sortAttributesByAttributeSet(int $attributeSetId, array $attributeCodes): array
    {
        /** @var Collection $attributeCollection */
        $attributeCollection = $this->_attributeCollectionFactory->create();
        $attributeCollection->getSelect()->joinLeft(
            ['eea' => $attributeCollection->getTable('eav_entity_attribute')],
            'main_table.attribute_id = eea.attribute_id',
            null
        )->joinLeft(
            ['eag' => $attributeCollection->getTable('eav_attribute_group')],
            'eea.attribute_group_id = eag.attribute_group_id',
            null
        )->where(
            'eea.attribute_set_id = ?', $attributeSetId
        )->where(
            'main_table.attribute_code IN (?)', $attributeCodes
        )->order([
            'eag.sort_order ASC',
            'eea.sort_order ASC',
            'eea.attribute_id ASC',
            'main_table.attribute_id ASC'
        ]);

        return array_map(function($item) {
            /** @var \Magento\Eav\Model\Entity\Attribute $item */
            return $item->getAttributeCode();
        }, $attributeCollection->getItems());
    }
}
