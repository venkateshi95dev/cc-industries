<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Observer\Reverse;

use I95DevConnect\DiscountGroups\Model\CustomerdiscountgroupFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class AssignCdgToCustomer implements ObserverInterface
{
    /**
     * @var CustomerdiscountgroupFactory
     */
    protected $customerdiscountgroupModel;

    /**
     *
     * @param CustomerdiscountgroupFactory $customerdiscountgroupModel
     */
    public function __construct(
        CustomerdiscountgroupFactory $customerdiscountgroupModel
    ) {
        $this->customerdiscountgroupModel = $customerdiscountgroupModel;
    }
    /**
     * Save i95Dev Custom attributes
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $currentObj = $observer->getEvent()->getData("currentObject");
        $is_enabled = $currentObj->dataHelper->isEnabled();
        if (!$is_enabled) {
            return;
        }

        $customerDiscountGroupId =
            $currentObj->dataHelper->getValueFromArray("customerDiscountGroupId", $currentObj->stringData);
        if (isset($customerDiscountGroupId) && $customerDiscountGroupId != '') {
            $groupData = $currentObj->dataHelper->checkInDefaultCustomerGroups('CC-'.$customerDiscountGroupId);
            $id = null;
            if ($groupData) {
                $id = $groupData->getId();
            }
            if ($id) {
                $currentObj->customerInterface->setGroupId($id);
            } else {
                $message = "Customer Group Not Found ::" . 'CC-'.$customerDiscountGroupId;
                throw new LocalizedException(__($message));
            }
            $customerDiscountData = $this->customerdiscountgroupModel->create()->getCollection()
                ->addFieldtoFilter('cdg_code', $customerDiscountGroupId)->getData();
            if (!empty($customerDiscountData)) {
                $currentObj->customerInterface->setCustomAttribute('customer_discount_group', $customerDiscountGroupId);
            } else {
                $message = "Customer Discount Group Not Found ::" . $customerDiscountGroupId;
                throw new LocalizedException(__($message));
            }
        }
    }
}
