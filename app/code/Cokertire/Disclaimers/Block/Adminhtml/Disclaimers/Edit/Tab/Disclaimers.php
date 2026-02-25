<?php

namespace Cokertire\Disclaimers\Block\Adminhtml\Disclaimers\Edit\Tab;

class Disclaimers extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        $disclaimers = $this->_coreRegistry->registry('cokertire_disclaimers');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('post_');
        $form->setFieldNameSuffix('post');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Disclaimers Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        if ($disclaimers->getDisclaimersId()) {
            $fieldset->addField(
                'disclaimers_id',
                'hidden',
                ['name' => 'disclaimers_id']
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
            'attribute_set',
            'select',
            [
                'name'  => 'attribute_set',
                'label' => __('Attribute Set'),
                'title' => __('Attribute Set'),
                'required' => true,
                'values'    => $this->_attributeSet->toOptionArray(),
                'after_element_html' => '<small>The attribute set of products you want this disclaimer to display.</small>',
            ]
        );
        $fieldset->addField(
            'disclaimer',
            'textarea',
            [
                'name'  => 'disclaimer',
                'label' => __('Disclaimer'),
                'title' => __('Disclaimer'),
                'required' => true,
            ]
        );



        $disclaimersData = $this->_session->getData('cokertire_distributors', true);
        if ($disclaimersData) {
            $disclaimers->addData($disclaimersData);
        } else {
            if (!$disclaimers->getDisclaimersId()) {
                $disclaimers->addData($disclaimers->getDefaultValues());
            }
        }
        $form->addValues($disclaimers->getData());
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
        return __('Disclaimers');
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
