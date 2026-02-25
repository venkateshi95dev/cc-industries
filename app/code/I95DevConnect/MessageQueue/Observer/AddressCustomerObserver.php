<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Observer;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\Http;

/**
 * Customer address observer
 */
class AddressCustomerObserver implements ObserverInterface
{
    public const CUSTOMER_SAVE_FLAG = 'customer_save_processed';
    public const MAGLOGNAME = 'MagentoToERP';
    public const ERPLOGNAME = 'ERPToMagento';
    public const I95EXC = 'i95devApiException';

    /**
     * @var object
     */
    public $logger;

    /**
     * @var Data
     */
    public $data;

    /**
     * @var Data
     */
    public $attributeRepository;

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var ResourceConnection
     */
    public $resource;

    /**
     * @var AdapterInterface
     */
    public $connection;

    /**
     * @var Customer Model
     */
    public $customerModel;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var Http
     */
    public $request;

    /**
     * AddressCustomerObserver constructor.
     *
     * @param Registry $coreRegistry
     * @param ResourceConnection $resource
     * @param Customer $customerModel
     * @param Http $request
     * @param Data $dataHelper
     */
    public function __construct(
        Registry $coreRegistry,
        ResourceConnection $resource,
        Customer $customerModel,
        Http $request,
        Data $dataHelper
    ) {

        $this->dataHelper = $dataHelper;
        $this->coreRegistry = $coreRegistry;
        $this->resource = $resource;
        $this->connection = $resource->getConnection('write');
        $this->customerModel = $customerModel;
        $this->request = $request;
    }

    /**
     * To Save i95dev Customer address Custom attributes
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {

        $is_enabled = $this->dataHelper->isEnabled();
        if (!$is_enabled) {
            return;
        }
        if ($this->dataHelper->getGlobalValue('i95_observer_skip') ||
            $this->request->getParam('isI95DevRestReq') == 'true'
        ) {
            return;
        }

        $customer = $observer->getEvent()->getDataObject()->getCustomer();
        $customerAddress = $observer->getEvent()->getDataObject();
        $updateAt = $customerAddress->getUpdatedAt();
        $customerData = $customerAddress->getCustomer();
        $customerData->setDataUsingMethod('updated_at', $updateAt);
        $this->dataHelper->customCustomerAttributes($customer);
    }
}
