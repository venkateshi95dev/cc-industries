<?php

namespace Crimson\Testimonial\Block\Adminhtml\Testimonial\Edit\Tab;

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

    protected $_category;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Crimson\Testimonial\Model\ResourceModel\Testimonial\Collection $testimonialCollection,
        \Crimson\Testimonial\Model\Category $category,
        array $data = []
    ) {
        $this->_systemStore           = $systemStore;
        $this->_wysiwygConfig         = $wysiwygConfig;
        $this->_testimonialCollection = $testimonialCollection;
        $this->_category = $category;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    /**
     * Prepare form
     *
     * @return                                        $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $this->_eventManager->dispatch(
        'Crimson_check_license',
        ['obj' => $this,'ex'=>'Crimson_Testimonial']
        );
        // @var $model \Magento\Cms\Model\Page
        $model = $this->_coreRegistry->registry('testimonial_testimonial');


        $wysiwygConfig = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId().time()]);
        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Crimson_Testimonial::testimonial_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
            $wysiwygConfig['enabled'] = $wysiwygConfig['add_variables'] = $wysiwygConfig['add_widgets'] = $wysiwygConfig['add_images'] = 0;
            $wysiwygConfig['plugins'] = [];
        }
        if (!$this->getData('is_valid') && !$this->getData('local_valid')) {
            $isElementDisabled = true;
            $wysiwygConfig['enabled'] = $wysiwygConfig['add_variables'] = $wysiwygConfig['add_widgets'] = $wysiwygConfig['add_images'] = 0;
            $wysiwygConfig['plugins'] = [];
        }

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('testimonial_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Testimonial Information')]);

        if ($model->getId()) {
            $fieldset->addField('testimonial_id', 'hidden', ['name' => 'testimonial_id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
             'name'     => 'title',
             'label'    => __('Title'),
             'title'    => __('Title'),
             'required' => true,
             'disabled' => $isElementDisabled,
            ]
        );


        $fieldset->addField(
            'testimonial',
            'editor',
            [
             'name'     => 'testimonial',
             'label'    => __('Testimonial'),
             'title'    => __('Testimonial'),
             'style'    => 'height:20em;',
             'required' => true,
             'disabled' => $isElementDisabled,
             'config'   => $wysiwygConfig,
            ]
        );
        $fieldset->addField(
            'rating',
            'text',
            [
             'name'     => 'rating',
             'label'    => __('Rating'),
             'title'    => __('Rating'),
             'required' => true,
             'disabled' => $isElementDisabled,
            ]
        );

        $categoryCollection = $this->_category->getCollection();
        $categories         = [];
        foreach ($categoryCollection as $k => $v) {
            $categories[] = [
                             'label' => $v->getName(),
                             'value' => $v->getCategoryId(),
                            ];
        }

        $field = $fieldset->addField(
            'category_id',
            'multiselect',
            [
             'name'     => 'categories[]',
             'label'    => __('Category'),
             'title'    => __('Category'),
             'required' => true,
             'values'   => $categories,
             'disabled' => $isElementDisabled,
             'style'    => 'width: 200px;',
            ]
        );
        /*
            * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field    = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                 'name'     => 'stores[]',
                 'label'    => __('Store View'),
                 'title'    => __('Store View'),
                 'required' => true,
                 'values'   => $this->_systemStore->getStoreValuesForForm(false, true),
                 'disabled' => $isElementDisabled,
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                 'name'  => 'stores[]',
                 'value' => $this->_storeManager->getStore(true)->getId(),
                ]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }//end if

        $fieldset->addField(
            'position',
            'text',
            [
             'name'     => 'position',
             'label'    => __('Position'),
             'title'    => __('Position'),
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

        $this->_eventManager->dispatch('adminhtml_testimonial_testimonial_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();

    }//end _prepareForm()


    public function getCatCollection()
    {
        $model      = $this->_coreRegistry->registry('testimonial_testimonial');
        $collection = $this->_categoryCollection
            ->addFieldToFilter('testimonial_id', array('neq' => $model->getId()))
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
        return __('Testimonial Information');
    }


    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Testimonial Information');
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
