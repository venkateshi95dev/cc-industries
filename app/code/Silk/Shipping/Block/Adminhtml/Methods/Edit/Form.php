<?php
/**
 * Adminhtml THN Shipping Methods Edit Form
 */
namespace Silk\Shipping\Block\Adminhtml\Methods\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Silk\Shipping\Model\MethodsFactory
     */
    protected $_silkShippingMethodFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param \Silk\Shipping\Model\MethodsFactory  $silkShippingMethodFactory
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Silk\Shipping\Model\MethodsFactory $silkShippingMethodFactory,
        array $data = []
    ) {
        $this->formKey = $context->getFormKey();
        $this->_systemStore = $systemStore;
        $this->_silkShippingMethodFactory = $silkShippingMethodFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init class
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('mappingShippingMethodsForm');
        $this->setTitle(__('Shipping Methods Information'));
        $this->setUseContainer(true);
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $shippingMethodId = $this->_coreRegistry->registry('shipping_method_id');
        try {
            $method = $this->_silkShippingMethodFactory->create()->load($shippingMethodId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            /** Tax rule not found */
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Shipping Methods Information')]);

        $fieldset->addField(
            'method_status',
            'select',
            [
                'name' => 'method_status',
                'label' => __('Status'),
                'class' => 'required-entry',
                'required' => true,
                'values' => ['0'=>__('Disable'),'1'=>__('Enable')]
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'select',
                [
                    'name' => 'store_id',
                    'label' => __('Store'),
                    'title' => __('Store'),
                    'values' => $this->_systemStore->getStoreValuesForForm(true, false),
                    'required' => true
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
                    'name' => 'store_id'
                ]
            );
            if (isset($method)) {
                $method->setThnStoreId($this->_storeManager->getStore(true)->getId());
            }
        }

        $fieldset->addField(
            'method_code',
            'text',
            [
                'name' => 'method_code',
                'label' => __('Shipping Method Code'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $fieldset->addField(
            'method_title',
            'text',
            [
                'name' => 'method_title',
                'label' => __('Shipping Method Title'),
                'class' => 'required-entry',
                'required' => true
            ]
        );
        $fieldset->addField(
            'carrier_id',
            'text',
            [
                'name' => 'carrier_id',
                'label' => __('Shipping Method For Erp Mapping'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $fieldset->addField(
            'error_message',
            'textarea',
            [
                'name' => 'error_message',
                'label' => __('Shipping Method Error Message'),
            ]
        );

        if (isset($method) && is_object($method) && $method->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                [
                    'name' => 'id',
                    'no_span' => true
                ]
            );
            $form->setValues($method->getData());
        }


        $form->setAction($this->getUrl('mappingshipping/index/save'));
        $form->setUseContainer($this->getUseContainer());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
