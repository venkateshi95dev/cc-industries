<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_OrderEdit
 */
namespace I95DevConnect\OrderEdit\Observer\Reverse;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use I95DevConnect\OrderEdit\Model\DataPersistence\OrderEdit\Edit;
use I95DevConnect\OrderEdit\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

/**
 * Observer for edit order
 */
class BeforeValidateOrderObserver implements ObserverInterface
{
    public const TARGET_ORDER_EDIT_STATUS = "targetOrderEditStatus";
    /**
     * @var Edit
     */
    public $orderEdit;
    /**
     * @var Data
     */
    public $helper;

    /**
     * @param Edit $orderEdit
     * @param Data $helper
     */
    public function __construct(
        Edit $orderEdit,
        Data $helper
    ) {
        $this->orderEdit = $orderEdit;
        $this->helper = $helper;
    }

    /**
     * Validate if Order already synced and do the edit order operation
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $orderValidateObject = $observer->getData('orderValidateObject');
        try {
            if ($this->helper->isEnabled()) {
                $targetOrderId = $orderValidateObject->dataHelper->getValueFromArray(
                    "targetId",
                    $orderValidateObject->stringData
                );
                $loadCustomOrder = $orderValidateObject->customSalesOrder->create()
                ->getCollection()
                ->addFieldToFilter("target_order_id", $targetOrderId)
                ->setOrder('id', 'DESC')
                ->getFirstItem();
                if ($loadCustomOrder->getId()) {
                    $orderValidateObject->stringData[self::TARGET_ORDER_EDIT_STATUS] =
                        isset($orderValidateObject->stringData[self::TARGET_ORDER_EDIT_STATUS]) ?
                            $orderValidateObject->stringData[self::TARGET_ORDER_EDIT_STATUS] : "edited";
                    $orderValidateObject->stringData["sourceOrderId"] = $loadCustomOrder->getSourceOrderId();
                    $orderValidateObject->stringData["isValidated"] = true;
                    $orderValidateObject->validationResult =
                        $this->orderEdit->editOrder($orderValidateObject->stringData);
                }
            }
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
