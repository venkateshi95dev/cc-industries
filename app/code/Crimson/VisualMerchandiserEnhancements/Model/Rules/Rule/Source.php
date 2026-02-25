<?php
/**
 * @namespace   Crimson
 * @module      VisualMerchandiserEnhancements
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        5/31/2019 2:58 PM
 * @brief       Adds support for multiselect attributes in visual merchandiser
 */

namespace Crimson\VisualMerchandiserEnhancements\Model\Rules\Rule;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Source
 * @package Crimson\VisualMerchandiserEnhancements\Model\Rules\Rule
 */
class Source extends \Magento\VisualMerchandiser\Model\Rules\Rule\Source
{
    /**
     * @param Collection $collection
     * @return void
     * @throws LocalizedException
     */
    public function applyToCollection($collection)
    {
        if ($this->_attribute->getFrontendInput() !== 'multiselect') {
            parent::applyToCollection($collection);
            return;
        }

        $options = $this->toMappedOptions(
            $this->_attribute->getSource()->getAllOptions(false, true)
        );

        $selectedValue = strtolower($this->_rule['value']);
        $ruleOperator = $this->_rule['operator'];
        $ruleValue = $selectedValue;

        if ('like' === $this->_rule['operator']) {
            $selectedValues = preg_split('/[\ \,]+/', $selectedValue);
            if (!is_array($selectedValues) || !$selectedValues) {
                return;
            }

            foreach ($selectedValues as $selectedValue) {
                if (!isset($options[$selectedValue])) {
                    continue;
                }

                $collection->addAttributeToFilter($this->_rule['attribute'], [
                    'finset' => $options[$selectedValue]
                ]);
            }
        } else {
            if (isset($options[$selectedValue])) {
                $ruleValue = $options[$selectedValue];
            }

            $collection->addAttributeToFilter($this->_rule['attribute'], [
                $ruleOperator => $ruleValue
            ]);
        }
    }
}
