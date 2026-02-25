<?php

namespace Cokertire\Showpages\Block\Adminhtml\Showpages\Edit\Tab;

class Showpages extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->_wysiwygConfig            = $wysiwygConfig;
        $this->_countryOptions           = $countryOptions;
        $this->_booleanOptions           = $booleanOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {

        $showpages = $this->_coreRegistry->registry('coker_showpages');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('post_');
        $form->setFieldNameSuffix('post');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Showpages Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        if ($showpages->getShowpagesId()) {
            $fieldset->addField(
                'showpages_id',
                'hidden',
                ['name' => 'showpages_id']
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
            'show_title_meta',
            'text',
            [
                'name'  => 'show_title_meta',
                'label' => __('Title Tag (meta)'),
                'title' => __('Show Title Meta'),
                'after_element_html' => '<br><small>For improved SEO (Limit to around 55 characters)</small>'
            ]
        );
        $fieldset->addField(
            'show_desc_meta',
            'textarea',
            [
                'name'  => 'show_desc_meta',
                'label' => __('Meta Description'),
                'title' => __('Meta Description')
            ]
        );
        $fieldset->addField(
            'show_keywords_meta',
            'text',
            [
                'name'  => 'show_keywords_meta',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords')
            ]
        );
        $fieldset->addField(
            'show_title',
            'text',
            [
                'name'  => 'show_title',
                'label' => __('Show Page Title (H1)'),
                'title' => __('Show Title'),
                'required' => true,
            ]
        );
         $dateFormat = 'M/d/yyyy';
  /*       if($showpages->hasData('show_date')) {
             $datetime = new \DateTime($showpages->getData('show_date'));
             $showpages->setData('show_date', $datetime->setTimezone(new \DateTimeZone($this->_localeDate->getConfigTimezone())));
         }

         if($showpages->hasData('show_date_end')) {
             $datetime = new \DateTime($showpages->getData('show_date_end'));
             $showpages->setData('show_date_end', $datetime->setTimezone(new \DateTimeZone($this->_localeDate->getConfigTimezone())));
         }*/

         $fieldset->addField(
             'show_date',
             'date',
             [
                 'name' => 'show_date',
                 'label' => __('Show Date'),
                 'title' => __('Show Date'),
                 'required' => true,
                 'class' => 'required-entry',
                 'date_format' => $dateFormat
             ]
         );

        $fieldset->addField(
             'show_date_end',
             'date',
             [
                 'name' => 'show_date_end',
                 'label' => __('Show Date End'),
                 'title' => __('Show Date End'),
                 'required' => true,
                 'class' => 'required-entry',
                 'date_format' => $dateFormat
             ]
         );
        $fieldset->addField(
            'show_space',
            'text',
            [
                'name'  => 'show_space',
                'label' => __('Show Space'),
                'title' => __('Show Space'),
            ]
        );


        $fieldset->addField(
            'show_description',
            'textarea',
            [
                'name'  => 'show_description',
                'label' => __('Show Description'),
                'title' => __('Show Description')
            ]
        );
        $fieldset->addField(
            'show_venue',
            'text',
            [
                'name'  => 'show_venue',
                'label' => __('Show Venue'),
                'title' => __('Show Venue')
            ]
        );
        $fieldset->addField(
            'show_coordinates',
            'text',
            [
                'name'  => 'show_coordinates',
                'label' => __('Show Address'),
                'title' => __('Show Address')
            ]
        );

        $fieldset->addField(
            'show_city',
            'text',
            [
                'name'  => 'show_city',
                'label' => __('Show City'),
                'title' => __('Show City')
            ]
        );
        $fieldset->addField(
            'show_state',
            'text',
            [
                'name'  => 'show_state',
                'label' => __('Show State'),
                'title' => __('Show State')
            ]
        );
        $fieldset->addField(
            'show_skus',
            'text',
            [
                'name'  => 'show_skus',
                'label' => __('Show Skus'),
                'title' => __('Show Skus'),
                'after_element_html' => '<small>Seperate SKUs with a comma.  This will allow as many SKUs as you\'de like to use.</small>'
            ]
        );
        $fieldset->addField(
            'instagram',
            'text',
            [
                'name'  => 'instagram',
                'label' => __('Instagram Hashtag'),
                'title' => __('Instagram'),
                'after_element_html' => '<small>With or without the #.</small>'
            ]
        );
        $fieldset->addField(
            'offer_date_end',
            'date',
            [
                'name'  => 'offer_date_end',
                'label' => __('Offer Date End'),
                'title' => __('Offer Date End'),
                'date_format' => $dateFormat
            ]
        );
        $fieldset->addField(
            'view_img',
            'image',
            [
                'title' => __('View Image'),
                'label' => __('View Image'),
                'name' => 'view_img',
                'note' => 'Allow image type: jpg, jpeg, gif, png',
            ]
        );
        $fieldset->addField(
            'identifier',
            'text',
            [
                'name'  => 'identifier',
                'label' => __('URL Key'),
                'title' => __('URL Key'),
                'required' => true
            ]
        );

        $showpagesData = $this->_session->getData('coker_showpages', true);
        if ($showpagesData) {
            $showpages->addData($showpagesData);
        } else {
            if (!$showpages->getId()) {
                $showpages->addData($showpages->getDefaultValues());
            }
        }
        $form->addValues($showpages->getData());
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
        return __('Showpages');
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
