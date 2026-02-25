<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Block\Adminhtml\Edit\Tab\View;

use I95DevConnect\MessageQueue\Helper\Config;
use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\CustomerFactory;

/**
 * Block for displaying target information in customer view page
 * @api
 */
class CustomerInfo extends Template
{
    public const TARGET_CUSTOMER_ID = 'target_customer_id';

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     *
     * @var CustomerFactory
     */
    public $customerFactory;

    /**
     * @var Data
     */
    public $messageQueueHelper;

    /**
     * @var Config
     */
    public $configHelper;

    /**
     * @var \I95DevConnect\PriceLevel\Helper\Data
     */
    public $baseHelper;
    // @codingStandardsIgnoreStart
    public $_template = 'I95DevConnect_PriceLevel::customer/tab/view/custom_info.phtml';
    // @codingStandardsIgnoreEnd

    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param CustomerFactory $customerFactory
     * @param \I95DevConnect\PriceLevel\Helper\Data $baseHelper
     * @param Config $configHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CustomerFactory $customerFactory,
        \I95DevConnect\PriceLevel\Helper\Data $baseHelper,
        Config $configHelper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->customerFactory = $customerFactory;
        $this->baseHelper = $baseHelper;
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve customer id
     *
     * @return CustomerFactory $customerFactory
     */
    public function getCustomer()
    {
        $customerId = $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        return $this->customerFactory->create()->load($customerId);
    }

    /**
     * Get customer attribute
     *
     * @return string
     */
    public function getCustomAttribute()
    {
        $customerCollection = $this->customerFactory->create()
                ->load($this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID))->getData();
        return isset($customerCollection[self::TARGET_CUSTOMER_ID]) ?
                $customerCollection[self::TARGET_CUSTOMER_ID] : "Customer Sync In Process";
    }

    /**
     * Get customer price level
     *
     * @return string
     */
    public function getCustomerPricelevel()
    {
        $priceLevel = '';
        if ($this->baseHelper->isPriceGroupsEnabledInAnyWebsite()) {
            $priceLevel = $this->getCustomer()->getPricelevel();
        }
        return $priceLevel;
    }

    /**
     * Get component data
     *
     * @return string
     */
    public function getComponent()
    {
        $configurationValues = $this->configHelper->getConfigValues()->getData();
        return $configurationValues['component'];
    }

    /**
     * Check custom attribute of customers
     *
     * @return boolean
     */
    public function checkCustomAttribute()
    {
        $customerCollection = $this->customerFactory->create()
                ->load($this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID))->getData();
        $customerId = (isset($customerCollection[self::TARGET_CUSTOMER_ID]) ?
                $customerCollection[self::TARGET_CUSTOMER_ID] : '');
        $origin = (isset($customerCollection['origin']) ?
                $customerCollection['origin'] : '');
        if (empty($customerId) && empty($origin)) {
            return false;
        }
        return true;
    }

    public function getEnabledCustomerWebsiteIds()
    {
       return $this->baseHelper->getEnabledCustomerWebsiteIds();
    }


}
