<?php

namespace Crimson\Testimonial\Block\Adminhtml\Category\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Crimson\Testimonial\Model\ResourceModel\Testimonial\Collection
     */
    protected $_testimonialCollection;

    protected $_drawLevel;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Crimson\Testimonial\Model\ResourceModel\Testimonial\Collection $testimonialCollection,
        array $data = []
    ) {
        $this->_systemStore           = $systemStore;
        $this->_wysiwygConfig         = $wysiwygConfig;
        $this->_testimonialCollection = $testimonialCollection;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        // @var $model \Magento\Cms\Model\Page
        $model = $this->_coreRegistry->registry('testimonial_category');

        /*
            * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Crimson_Testimonial::category_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $wysiwygConfig = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId().time()]);
        /*
            * @var \Magento\Framework\Data\Form $form
        */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('testimonial_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Testimonial Information')]);

        if ($model->getId()) {
            $fieldset->addField('category_id', 'hidden', ['name' => 'category_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            [
             'name'     => 'name',
             'label'    => __('Name'),
             'title'    => __('Name'),
             'required' => true,
             'disabled' => $isElementDisabled,
            ]
        );
        $fieldset->addField(
            'is_active',
            'select',
            [
             'label'    => __('Status'),
             'title'    => __('Testimonial Status'),
             'name'     => 'is_active',
             'options'  => $model->getAvailableStatuses(),
             'disabled' => $isElementDisabled,
            ]
        );
        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $this->_eventManager->dispatch('adminhtml_testimonial_category_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();

    }


    public function getCatCollection()
    {
        $model      = $this->_coreRegistry->registry('testimonial_category');
        $collection = $this->_categoryCollection
            ->addFieldToFilter('category_id', array('neq' => $model->getId()))
            ->setOrder('position');
        return $collection;
    }


    public function getCats($categories, $cats = [], $level = 0)
    {
        foreach ($cats as $k => $v) {
        }
    }


    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Category Information');
    }


    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Category Information');
    }


    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
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
