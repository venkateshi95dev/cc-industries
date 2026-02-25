<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Observer;

use Exception;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\CustomerGroup;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Observer class for customer group save
 */
class CustomerGroupSaveAfterObserver implements ObserverInterface
{
    public const MAGLOGNAME = 'MagentoToERP';
    public const ERPLOGNAME = 'ERPToMagento';
    public const I95EXC = 'i95devApiException';
    public const CUSTOMER_GROUP_SAVE_FLAG = 'customer_group_save_processed';

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var date
     */
    public $date;

    /**
     * @var customerGroup
     */
    public $customerGroup;

    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @var CustomerGroup
     */
    public $customerGroupPersistence;

    /**
     * @var Http
     */
    public $request;

    /**
     * CustomerGroupSaveAfterObserver constructor.
     *
     * @param Data $dataHelper
     * @param Registry $coreRegistry
     * @param DateTime $date
     * @param \I95DevConnect\MessageQueue\Model\CustomerGroup $customerGroup
     * @param CustomerGroup $customerGroupPersistence
     * @param LoggerInterfaceFactory $logger
     * @param Http $request
     */
    public function __construct(
        Data $dataHelper,
        Registry $coreRegistry,
        DateTime $date,
        \I95DevConnect\MessageQueue\Model\CustomerGroup $customerGroup,
        CustomerGroup $customerGroupPersistence,
        LoggerInterfaceFactory $logger,
        Http $request
    ) {

        $this->dataHelper = $dataHelper;
        $this->coreRegistry = $coreRegistry;
        $this->date = $date;
        $this->customerGroup = $customerGroup;
        $this->customerGroupPersistence = $customerGroupPersistence;
        $this->logger = $logger;
        $this->request = $request;
    }

    /**
     * Create and update customer group
     *
     * @param Observer $observer
     *
     * @throws Exception
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
        try {
            $customerGroupObj = $observer->getEvent()->getDataObject();
            $customerGroupData = $customerGroupObj->getData();
            $currentDate = $this->date->gmtDate();
            $customerGroupId = isset($customerGroupData['customer_group_id']) ?
                    $customerGroupData['customer_group_id'] : '';
            $customGroup = $this->customerGroupPersistence->getCustomerGroupById($customerGroupId);

            if (!isset($customGroup['id'])) {
                $customGroupModel = $this->customerGroup;
                $customGroupModel->setcreatedAt($currentDate);
            } else {
                $customerGroupId = $customGroup['id'];
                $customGroupModel = $this->customerGroup->load($customGroup['id']);
            }
            $customGroupModel->setcustomerGroupId($customerGroupId);
            $customGroupModel->setupdatedAt($currentDate);
            $customGroupModel->setupdateBy('Magento');
            $customGroupModel->save();
        } catch (LocalizedException $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, 'critical');
        }
    }
}
