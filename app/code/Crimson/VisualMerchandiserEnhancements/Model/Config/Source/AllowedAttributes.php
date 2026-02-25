<?php
/**
 * @namespace   Crimson
 * @module      VisualMerchandiserEnhancements
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        5/31/2019 3:19 PM
 * @brief
 */

namespace Crimson\VisualMerchandiserEnhancements\Model\Config\Source;

/**
 * Class AllowedAttributes
 * @package Crimson\VisualMerchandiserEnhancements\Model\Config\Source
 */
class AllowedAttributes extends \Magento\VisualMerchandiser\Model\Config\Source\AllowedAttributes
{
    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $entityTypeId = $this->type->loadByCode(\Magento\Catalog\Model\Product::ENTITY)->getId();
        if ($entityTypeId) {
            $collection = $this->attribute->getCollection()
                ->removeAllFieldsFromSelect()
                ->addFieldToSelect('attribute_code', 'value')
                ->addFieldToSelect('frontend_label', 'label')
                ->addFieldToFilter('entity_type_id', ['eq' => $entityTypeId])
                /**
                 * Change icoast@crimsonagility.com on 5/31/2019 at 3:20 PM
                 * Description: Adds support for multiselect attributes
                 */
                /*->addFieldToFilter('frontend_input', ['neq' => 'multiselect'])*/;
            /**
             * End of customization
             */
            $attributes = $collection->toArray();
            if (isset($attributes['items'])) {
                $this->options = $attributes['items'];
            }
        }

        return $this->options;
    }
}
