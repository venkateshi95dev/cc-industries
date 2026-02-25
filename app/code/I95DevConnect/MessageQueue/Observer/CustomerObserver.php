<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\Http;
use Psr\Log\LoggerInterface;

/**
 * Observer class to save customer custom attribute
 */
class CustomerObserver implements ObserverInterface
{
    public const CUSTOMER_SAVE_FLAG = 'customer_save_processed';
    public const MAGLOGNAME = 'MagentoToERP';
    public const ERPLOGNAME = 'ERPToMagento';
    public const I95EXC = 'i95devApiException';

    /**
     * @var LoggerInterface
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
     * @var Data
     */
    public $dataHelper;

    /**
     * @var Http
     */
    public $request;

    /**
     * CustomerObserver constructor.
     *
     * @param Data $dataHelper
     * @param Registry $coreRegistry
     * @param Http $request
     */
    public function __construct(
        Data $dataHelper,
        Registry $coreRegistry,
        Http $request
    ) {

        $this->dataHelper = $dataHelper;
        $this->coreRegistry = $coreRegistry;
        $this->request = $request;
    }

    /**
     * Save i95Dev customer Custom attributes
     *
     * @param  Observer $observer
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
        $customer = $observer->getEvent()->getCustomer();
        $this->dataHelper->customCustomerAttributes($customer);
    }
}
