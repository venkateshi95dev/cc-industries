<?php

namespace Crimson\Testimonial\Block\Adminhtml\Category\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('category_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Category Information'));

        $this->addTab(
            'main_section',
            [
             'label'   => __('General Infomration'),
             'content' => $this->getLayout()->createBlock('Crimson\Testimonial\Block\Adminhtml\Category\Edit\Tab\Main')->toHtml(),
            ]
        );
    }
}
