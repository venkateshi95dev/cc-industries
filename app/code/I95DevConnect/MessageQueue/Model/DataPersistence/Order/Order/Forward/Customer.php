<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward;

use Magento\Customer\Api\CustomerRepositoryInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Model\CustomerFactory;

/**
 * Class responsible for preparing customer data which will be added in order result to ERP
 */
class Customer
{
    /**
     * @var CustomerRepositoryInterfaceFactory
     */
    public $customerRepo;

    /**
     *
     * @var CustomerInterface
     */
    public $customer;

    protected $customerFactory;

    /**
     *
     * @param CustomerRepositoryInterfaceFactory $customerRepo
     */
    public function __construct(
        CustomerRepositoryInterfaceFactory $customerRepo,
        CustomerFactory $customerFactory
    ) {
        $this->customerRepo = $customerRepo;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Method to get customer information from order
     *
     * @param OrderInterface $order
     *
     * @return array
     * @throws LocalizedException
     * @createdBy Sravani Polu
     */
    public function getCustomerEntity($order)
    {
        $isGuestOrder = $order->getCustomerIsGuest();
        $customerData['email'] = $order->getCustomerEmail();
        try {
            if (!$isGuestOrder) {
                $customerData['sourceId'] = $order->getCustomerId();
                $this->customer = $this->customerRepo->create()->getById($order->getCustomerId());
                $customerModel = $this->customerFactory->create()->load($order->getCustomerId());
                $customerData['targetCustomerId'] = $this->customer->getCustomAttribute('target_customer_id') !== null ?
                    $this->customer->getCustomAttribute('target_customer_id')->getValue() : null;
                //@Hrusikesh Added firstName and lastName for Registered user
                $customerData['firstName'] = $order->getCustomerFirstname();
                $customerData['lastName'] = $order->getCustomerLastname();
                if(!empty($customerModel->getData('ec_cust_id'))){
                    $customerData['ecCustId'] = $customerModel->getData('ec_cust_id');
                }
                if(!empty($customerModel->getData('ec_cust_token'))){
                    $customerData['ecCustToken'] = $customerModel->getData('ec_cust_token');
                }
              
            } else {
                $customerData['firstName'] = $order->getBillingAddress()->getFirstname();
                $customerData['lastName'] = $order->getBillingAddress()->getLastname();
                $customerData['sourceId'] = null;
                $customerData['targetCustomerId'] = null;
                $customerData['isGuest'] = true;
                $date = date_create($order->getCreatedAt());
                $customerData['createdAt'] = date_format($date, 'Y-m-d');
                $customerData['updatedAt'] = date_format($date, 'Y-m-d');
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
        return $customerData;
    }
}
