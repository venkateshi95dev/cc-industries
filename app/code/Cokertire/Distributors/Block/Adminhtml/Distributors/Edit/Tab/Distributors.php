<?php

namespace Cokertire\Distributors\Block\Adminhtml\Distributors\Edit\Tab;

class Distributors extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
     * @var \Magento\Config\Model\Config\Source\Locale\Country
     */
    protected $_countryOptions;

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
        array $data = []
    )
    {
        $this->_wysiwygConfig            = $wysiwygConfig;
        $this->_countryOptions           = $countryOptions;
        $this->_booleanOptions           = $booleanOptions;
        $this->_websiteOptions           = $website;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $distributors = $this->_coreRegistry->registry('cokertire_distributors');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('post_');
        $form->setFieldNameSuffix('post');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Distributors Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        if ($distributors->getDistributorsid()) {
            $fieldset->addField(
                'distributorsid',
                'hidden',
                ['name' => 'distributorsid']
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
            'company',
            'text',
            [
                'name'  => 'company',
                'label' => __('Company'),
                'title' => __('Company'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'contactName',
            'text',
            [
                'name'  => 'contactName',
                'label' => __('Contact Name'),
                'title' => __('Contact Name'),
                'required' => false,
            ]
        );
        $country = $this->_countryOptions->toOptionArray();
        $new = array('value'=>"",'label'=>"--Please Select--");
        array_unshift($country,$new);
        $fieldset->addField(
            'country',
            'select',
            [
                'name'  => 'country',
                'label' => __('Country'),
                'title' => __('Country'),
                'required' => false,
                'values' => $country,
            ]
        );
        $fieldset->addField(
            'website',
            'text',
            [
                'name'  => 'website',
                'label' => __('Website'),
                'title' => __('Website'),
                'required' => false,
            ]
        );
        $fieldset->addField(
            'address1',
            'text',
            [
                'name'  => 'address1',
                'label' => __('Address1'),
                'title' => __('Address1'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'zip1',
            'text',
            [
                'name'  => 'zip1',
                'label' => __('Zip1'),
                'title' => __('Zip1'),
                'required' => false,
            ]
        );
        $fieldset->addField(
            'address2',
            'text',
            [
                'name'  => 'address2',
                'label' => __('Address2'),
                'title' => __('Address2'),
                'required' => false,
            ]
        );
        
        $fieldset->addField(
            'zip2',
            'text',
            [
                'name'  => 'zip2',
                'label' => __('Zip2'),
                'title' => __('Zip2'),
                'required' => false,
            ]
        );
        $fieldset->addField(
            'phone1',
            'text',
            [
                'name'  => 'phone1',
                'label' => __('Phone1'),
                'title' => __('Phone1'),
                'required' => false,
            ]
        );
        $fieldset->addField(
            'phone2',
            'text',
            [
                'name'  => 'phone2',
                'label' => __('Phone2'),
                'title' => __('Phone2'),
                'required' => false,
            ]
        );
        $fieldset->addField(
            'fax',
            'text',
            [
                'name'  => 'fax',
                'label' => __('Fax'),
                'title' => __('Fax'),
                'required' => false,
            ]
        );
        $fieldset->addField(
            'languages',
            'text',
            [
                'name'  => 'languages',
                'label' => __('Languages'),
                'title' => __('Languages'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'name'  => 'email',
                'label' => __('Email'),
                'title' => __('Email'),
                'required' => false
            ]
        );


        $distributorsData = $this->_session->getData('cokertire_distributors', true);
        if ($distributorsData) {
            $distributors->addData($distributorsData);
        } else {
            if (!$distributors->getDistributorsid()) {
                $distributors->addData($distributors->getDefaultValues());
            }
        }
        $form->addValues($distributors->getData());
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
        return __('Distributors');
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
