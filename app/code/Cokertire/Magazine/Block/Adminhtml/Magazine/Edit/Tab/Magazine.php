<?php

namespace Cokertire\Magazine\Block\Adminhtml\Magazine\Edit\Tab;

class Magazine extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Wysiwyg config
     *
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;



    /**
     * Country options
     *
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_booleanOptions;

    /**
     * website options
     *
     * @var \Magento\Config\Model\Config\Source\Website
     */
    protected $_websiteOptions;

    /**
     * Sample Multiselect options
     *
     * @var \Mageplaza\HelloWorld\Model\Post\Source\SampleMultiselect
     */
    protected $_sampleMultiselectOptions;


    protected $_attributeSet;

    /**
     * constructor
     *
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Config\Model\Config\Source\Locale\Country $countryOptions
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Config\Model\Config\Source\Locale\Country $countryOptions,
        \Magento\Config\Model\Config\Source\Yesno $booleanOptions,
        \Magento\Config\Model\Config\Source\Website $website,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSet,
        array $data = []
    )
    {
        $this->_wysiwygConfig            = $wysiwygConfig;
        $this->_countryOptions           = $countryOptions;
        $this->_booleanOptions           = $booleanOptions;
        $this->_websiteOptions           = $website;
        $this->_attributeSet                   = $attributeSet;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $magazine = $this->_coreRegistry->registry('ct_magazine');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('post_');
        $form->setFieldNameSuffix('post');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Magazine Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        if ($magazine->getMagazineId()) {
            $fieldset->addField(
                'magazine_id',
                'hidden',
                ['name' => 'magazine_id']
            );
        }
        $fieldset->addField(
            'status',
            'select',
            [
                'name'  => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => $this->_booleanOptions->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'name',
            'text',
            [
                'name'  => 'name',
                'label' => __('Identifier'),
                'title' => __('Identifier'),
                'required'  => true,
            ]
        );

        $fieldset->addField(
            'year',
            'text',
            [
                'name'  => 'year',
                'label' => __('Year'),
                'title' => __('Year'),
                'required'  => true,
            ]
        );

        $fieldset->addField(
            'url',
            'text',
            [
                'name'  => 'url',
                'label' => __('Root Path'),
                'title' => __('Root Path'),
                'after_element_html' => '<small>Defaults to "/media/magviewer"</small>',
            ]
        );

        $fieldset->addField(
            'pages',
            'text',
            [
                'name'  => 'pages',
                'label' => __('Number of pages'),
                'title' => __('Number of pages'),
                'required'  => true,
            ]
        );

        $fieldset->addField(
            'location',
            'text',
            [
                'name'  => 'location',
                'label' => __('Path to files'),
                'title' => __('Path to files'),
                'after_element_html' => '<small>Defaults to "PDF/Catalog_{year}"</small>',
            ]
        );

        $fieldset->addField(
            'width',
            'text',
            [
                'name'  => 'width',
                'label' => __('Width of Magazine'),
                'title' => __('Width of Magazine'),
                'after_element_html' => '<small>Defaults to 1022px</small>',
            ]
        );

        $fieldset->addField(
            'height',
            'text',
            [
                'name'  => 'height',
                'label' => __('Height of Magazine'),
                'title' => __('Height of Magazine'),
                'after_element_html' => '<small>Defaults to 692px</small>',
            ]
        );



        $magazineData = $this->_session->getData('ct_magazine', true);
        if ($magazineData) {
            $magazine->addData($magazineData);
        } else {
            if (!$magazine->getMagazineId()) {
                $magazine->addData($magazine->getDefaultValues());
            }
        }
        $form->addValues($magazine->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Magazine');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
