<?php

namespace Crimson\CorvetteCentralCustomFees\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Ranges
 */
class CanadaTaxMapping extends AbstractFieldArray
{

    private $selectRenderer;
    private $selectCodeRenderer;
    private $selectStateRenderer;
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('province',
            [
                'label' => __('Province'),
                'renderer' => $this->getSelectStateRenderer(),
                'class' => 'required-entry'
            ]);
        $this->addColumn('jurisdiction_code',
            [
                'label' => __('Jurisdiction Code'),
                'renderer' => $this->getSelectCodeRenderer(),
                'class' => 'required-entry'
            ]);
        $this->addColumn('value', ['label' => __('Percentage'), 'class' => 'required-entry']);

        $this->addColumn('apply_to', [
            'label' => __('Apply to customer groups'),
            'renderer' => $this->getSelectRenderer(),
            'class' => 'required-entry'
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    private function getSelectRenderer()
    {
        if (!$this->selectRenderer) {
            $this->selectRenderer = $this->getLayout()->createBlock(FieldArrayColumn\Select::class, '', [
                'data' => [
                    'options' => [
                        ['value' => 'all', 'label' => __('All')],
                        ['value' => 'dealer', 'label' => __('Dealer')],
                        ['value' => 'retail', 'label' => __('Retail')],
                    ],
                ]
            ]);
        }
        return $this->selectRenderer;
    }
    private function getSelectCodeRenderer()
    {
        if (!$this->selectCodeRenderer) {
            $this->selectCodeRenderer = $this->getLayout()->createBlock(FieldArrayColumn\Select::class, '', [
                'data' => [
                    'options' => [
                        ['value' => 'PST', 'label' => __('PST')],
                        ['value' => 'HST', 'label' => __('HST')],
                        ['value' => 'GST', 'label' => __('GST')],
                    ],
                ]
            ]);
        }
        return $this->selectCodeRenderer;
    }
    private function getSelectStateRenderer()
    {
        if (!$this->selectStateRenderer) {
            $this->selectStateRenderer = $this->getLayout()->createBlock(FieldArrayColumn\Select::class, '', [
                'data' => [
                    'options' => [
                            ['value' => 'AB', 'label' => __('Alberta')],
                            ['value' => 'BC', 'label' => __('British Columbia')],
                            ['value' => 'MB', 'label' => __('Manitoba')],
                            ['value' => 'NB', 'label' => __('New Brunswick')],
                            ['value' => 'NL', 'label' => __('Newfoundland and Labrador')],
                            ['value' => 'NS', 'label' => __('Nova Scotia')],
                            ['value' => 'NT', 'label' => __('Northwest Territories')],
                            ['value' => 'NU', 'label' => __('Nunavut')],
                            ['value' => 'ON', 'label' => __('Ontario')],
                            ['value' => 'PE', 'label' => __('Prince Edward Island')],
                            ['value' => 'QC', 'label' => __('Quebec')],
                            ['value' => 'SK', 'label' => __('Saskatchewan')],
                            ['value' => 'YT', 'label' => __('Yukon')],
                    ],
                ]
            ]);
        }
        return $this->selectStateRenderer;
    }
}

