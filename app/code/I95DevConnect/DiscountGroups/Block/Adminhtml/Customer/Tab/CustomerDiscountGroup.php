<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Block\Adminhtml\Customer\Tab;

use I95DevConnect\DiscountGroups\Helper\Data;
use I95DevConnect\DiscountGroups\Model\CustomerdiscountgroupFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Customer;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\System\Store;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Customer Customer Discount Group form block
 */
class CustomerDiscountGroup extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected $systemStore;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var string
     */
    protected $scopeConfig;

    /**
     * @var Customer
     */
    protected $customerModel;

    /**
     * @var array
     */
    protected $customerData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var string
     */
    protected $_template = 'I95DevConnect_DiscountGroups::customer/tab/customerdiscountgroup.phtml'; // phpcs:ignore

    /**
     * @var \I95DevConnect\DiscountGroups\Model\Customerdiscountgroup
     */
    public $cdg;

    /**
     * @var Data
     */
    public $helper;
    /**
     * @var CustomerRepositoryInterface
     */
    public $customerRepository;

    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param Customer $customerModel
     * @param CustomerdiscountgroupFactory $cdg
     * @param Data $helper
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        Customer $customerModel,
        CustomerdiscountgroupFactory $cdg,
        Data $helper,
        CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->systemStore = $systemStore;
        $this->scopeConfig = $context->getScopeConfig();
        $this->customerModel = $customerModel;
        $this->storeManager = $context->getStoreManager();
        $this->cdg = $cdg;
        $this->helper = $helper;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Get customer id
     *
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Get tab label
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Discount Group');
    }

    /**
     * Get tab title
     *
     * @return Phrase
     */
    public function getTabTitle() //NOSONAR
    {
        return __('Discount Group');
    }

    /**
     * Can show tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            $customer = $this->customerModel->load($this->getCustomerId())->getData();
        } else {
            $customer = [];
        }
        $this->customerData = $customer;
        $isEnabled = $this->scopeConfig->getValue(
            'i95devconnect_discountgroups/discountgroups_enabled_settings/enable_dg',
            ScopeInterface::SCOPE_STORE
        );
        if ($isEnabled) {
            return true;
        }

        return $this->helper->isDiscountGroupsEnabledInAnyWebsite();

        //return false;
    }

    /**
     * Is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl() //NOSONAR
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Get currency symbol
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCurrencySymbol()
    {
        return $this->storeManager->getStore()->getDefaultCurrency()->getCurrencySymbol();
    }

    /**
     * Can edit data
     *
     * @return bool
     */
    public function canEditData() //NOSONAR
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * Get customer cdg
     *
     * @return mixed|string
     */
    public function getCustomerCdg()
    {
        if (!$this->getCustomerId()) {
            return '';
        }
        return isset($this->customerData['customer_discount_group'])
                ? $this->customerData['customer_discount_group'] : '';
    }

    /**
     * Get cdgs
     *
     * @return mixed
     */
    public function getCdgs()
    {
        return $this->cdg->create()
            ->getCollection()
            ->addFieldToSelect('id')
            ->addFieldToSelect('cdg_code')
            ->getData();
    }
}
