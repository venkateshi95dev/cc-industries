<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/14/2019 4:52 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Block\Adminhtml\Form\Field;

use Crimson\MachCatalogRequest\Model\Source\AvailableCatalogs as AvailableCatalogsSource;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

/**
 * Class AvailableCatalogs
 * @package Crimson\MachCatalogRequest\Block\Adminhtml\Form\Field
 */
class AvailableCatalogs extends AbstractFieldArray
{
    private $_arrayRowsCache = null;

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            AvailableCatalogsSource::CATALOG_VALUE,
            [
                'label' => __('Catalog Value'),
            ]
        );
        $this->addColumn(
            AvailableCatalogsSource::CATALOG_LABEL,
            [
                'label' => __('Catalog Label'),
            ]
        );
        $this->addColumn(
            AvailableCatalogsSource::CATALOG_IMAGE_URL,
            [
                'label' => __('Catalog Image URL'),
                'class' => 'validate-url'
            ]
        );
        $this->addColumn(
            AvailableCatalogsSource::SORT_ORDER,
            [
                'label' => __('Sort Order'),
                'class' => 'validate-digits validate-length minimum-length-1 maximum-length-4'
            ]
        );
        $this->_addAfter = false;
    }

    /**
     * Obtain existing data from form element
     *
     * Each row will be instance of \Magento\Framework\DataObject
     *
     * @return array
     */
    public function getArrayRows(): ?array
    {
        if ($this->_arrayRowsCache !== null) {
            return $this->_arrayRowsCache;
        }

        $this->_arrayRowsCache = parent::getArrayRows();

        uasort($this->_arrayRowsCache, function ($option1, $option2) {
            return ($option1[AvailableCatalogsSource::SORT_ORDER] ?? 0) <=> ($option2[AvailableCatalogsSource::SORT_ORDER] ?? 0);
        });

        return $this->_arrayRowsCache;
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->calcOptionHash(
            [
                $row->getData(AvailableCatalogsSource::CATALOG_VALUE),
                $row->getData(AvailableCatalogsSource::CATALOG_LABEL),
                $row->getData(AvailableCatalogsSource::CATALOG_IMAGE_URL),
                $row->getData(AvailableCatalogsSource::SORT_ORDER),
            ]
        )] = 'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }


    /**
     * Calculate CRC32 hash for option value
     *
     * @param string|array $optionValue Value of the option
     *
     * @return string
     */
    public function calcOptionHash($optionValue): string
    {
        if (is_array($optionValue)) {
            $optionValue = serialize($optionValue);
        }

        return sprintf('%u', crc32($this->getElement()->getName() . $this->getElement()->getId() . $optionValue));
    }
}
