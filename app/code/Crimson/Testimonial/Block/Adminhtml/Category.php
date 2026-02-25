<?php

namespace Crimson\Testimonial\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Category extends Container
{


    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_category';
        $this->_blockGroup = 'Crimson_Testimonial';
        $this->_headerText = __('Manage Category');

        parent::_construct();

        if ($this->_isAllowedAction('Crimson_Testimonial::save')) {
            $this->buttonList->update('add', 'label', __('Add New Category'));
        } else {
            $this->buttonList->remove('add');
        }
    }


    /**
     * Check permission for passed action
     *
     * @param  string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);

    }
}
