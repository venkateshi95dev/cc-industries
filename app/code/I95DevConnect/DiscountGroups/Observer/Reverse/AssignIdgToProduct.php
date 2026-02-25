<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Observer\Reverse;

use I95DevConnect\DiscountGroups\Model\ItemdiscountgroupFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class AssignIdgToProduct implements ObserverInterface
{
    /**
     *
     * @var ItemdiscountgroupFactory
     */
    protected $itemdiscountgroupModel;

    /**
     * AssignIdgToProduct constructor.
     *
     * @param ItemdiscountgroupFactory $itemdiscountgroupModel
     */
    public function __construct(
        ItemdiscountgroupFactory $itemdiscountgroupModel
    ) {
        $this->itemdiscountgroupModel = $itemdiscountgroupModel;
    }

    /**
     * Save i95Dev Custom attributes
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $currentObj = $observer->getEvent()->getData("currentObject");
        $is_enabled = $currentObj->dataHelper->isEnabled();
        if (!$is_enabled) {
            return;
        }

        $itemDiscountGroupId =
            $currentObj->dataHelper->getValueFromArray("itemDiscountGroupId", $currentObj->stringData);
        if (isset($itemDiscountGroupId) && $itemDiscountGroupId != '') {
            $itemDiscountData = $this->itemdiscountgroupModel->create()->getCollection()
                    ->addFieldtoFilter('idg_code', $itemDiscountGroupId)
                    ->getData();
            if (!empty($itemDiscountData)) {
                $currentObj->productInterface->setCustomAttribute("item_discount_group", $itemDiscountGroupId);
            } else {
                $message = "Item Discount Group Not Found ::" . $itemDiscountGroupId;
                throw new LocalizedException(__($message));
            }
        }
    }
}
