<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Observer\Reverse;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observer class to save product custom attribute variantId
 */
class AssignVariantIdToProduct implements ObserverInterface
{
    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * AssignVariantIdToProduct constructor.
     *
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Save i95Dev custom attribute
     *
     * @param Observer $observer
     * @autor Hrusieksh Manna
     */
    public function execute(Observer $observer)
    {
        $component = $this->dataHelper->getComponent();
        if ($component == 'AX' || $component == 'D365FO') {
            $currentObj = $observer->getEvent()->getData("currentObject");
            $variantId = $currentObj->dataHelper->getValueFromArray("variantId", $currentObj->stringData);
            if (isset($variantId)) {
                $currentObj->productInterface->setCustomAttribute("variant_id", $variantId);
            }
        }
    }
}
