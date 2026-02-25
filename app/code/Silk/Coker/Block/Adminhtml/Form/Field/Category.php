<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\Coker\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

/**
 * Class Category
 */
class Category extends AbstractFieldArray
{
    /**
     * @var Categories
     */
    protected $categoryRenderer = null;


    /**
     * Returns renderer for category element
     *
     * @return Categories
     */
    protected function getCategoryRenderer()
    {
        if (!$this->categoryRenderer) {
            $this->categoryRenderer = $this->getLayout()->createBlock(
                Categories::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->categoryRenderer;
    }

    /**
     * Prepare to render
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'category_id',
            [
                'label'     => __('Category'),
                'renderer'  => $this->getCategoryRenderer(),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Rule');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $category = $row->getCategoryId();
        $options = [];
        if ($category) {
            $options['option_' . $this->getCategoryRenderer()->calcOptionHash($category)]
                = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}
