<?php

namespace Crimson\Testimonial\Block\Adminhtml\Category;

use Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }


    /**
     * Initialize cms page edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId   = 'category_id';
        $this->_blockGroup = 'Crimson_Testimonial';
        $this->_controller = 'adminhtml_category';

        parent::_construct();

        if ($this->_isAllowedAction('Crimson_Testimonial::category_save')) {
            $this->buttonList->update('save', 'label', __('Save Category'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                 'label'          => __('Save and Continue Edit'),
                 'class'          => 'save',
                 'data_attribute' => [
                                      'mage-init' => [
                                                      'button' => [
                                                                   'event'  => 'saveAndContinueEdit',
                                                                   'target' => '#edit_form',
                                                                  ],
                                                     ],
                                     ],
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }//end if

        if ($this->_isAllowedAction('Crimson_Testimonial::category_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Category'));
        } else {
            $this->buttonList->remove('delete');
        }
    }


    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('testimonial_category')->getId()) {
            return __("Edit Category '%1'", $this->escapeHtml($this->_coreRegistry->registry('testimonial_category')->getTitle()));
        } else {
            return __('New Category');
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


    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('cms/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }


    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            };
        ";

        return parent::_prepareLayout();
    }
}
