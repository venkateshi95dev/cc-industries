<?php

namespace Crimson\Testimonial\Block\Adminhtml\Testimonial\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('testimonial_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Testimonial Information'));

        $this->addTab(
            'main_section',
            [
             'label'   => __('General Infomration'),
             'content' => $this->getLayout()->createBlock('Crimson\Testimonial\Block\Adminhtml\Testimonial\Edit\Tab\Main')->toHtml(),
            ]
        );
         $this->addTab(
             'author_section',
             [
              'label'   => __('Author Information'),
              'content' => $this->getLayout()->createBlock('Crimson\Testimonial\Block\Adminhtml\Testimonial\Edit\Tab\Author')->toHtml(),
             ]
         );
         $this->addTab(
             'products',
             [
              'label' => __('Products'),
              'url'   => $this->getUrl('testimonial/*/products', ['_current' => true]),
              'class' => 'ajax',
             ]
         );
    }
}
