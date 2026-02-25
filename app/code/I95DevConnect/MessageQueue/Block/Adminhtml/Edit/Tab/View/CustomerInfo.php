<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block\Adminhtml\Edit\Tab\View;

use I95DevConnect\MessageQueue\Helper\Config;
use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Registry;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Block for displaying target information in customer view page
 * @api
 */
class CustomerInfo extends Template
{
    /**
     * @var CustomerInterface
     */
    public $customerRepository;

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
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
     * @var Data
     */
    public $baseHelper;

    /**
     * @var string
     */
    public $_template = 'I95DevConnect_MessageQueue::customer/tab/view/custom_info.phtml';// phpcs:ignore

    /**
     *
     * @param Context $context
     * @param Registry $registry
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerFactory $customerFactory
     * @param Data $baseHelper
     * @param Config $configHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        Data $baseHelper,
        Config $configHelper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->baseHelper = $baseHelper;
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve customer id
     *
     * @return Customer
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
        return $this->baseHelper->getCustomAttribute(
            $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        );
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
        $customerId = (isset($customerCollection['target_customer_id']) ?
                $customerCollection['target_customer_id'] : '');
        $origin = (isset($customerCollection['origin']) ?
                $customerCollection['origin'] : '');
        if ($customerId == "" && $origin === null) {
            return false;
        }
        return true;
    }
}
