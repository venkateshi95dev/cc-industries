<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_OrderEdit
 */
namespace I95DevConnect\OrderEdit\Model\DataPersistence\OrderEdit;

use \I95DevConnect\MessageQueue\Helper\Data;

/**
 * Class edit order
 */
class Edit extends AbstractOrderEdit
{
    public const I95OBSKIP = 'i95_observer_skip';

    /**
     * @var null
     */
    public $oldOrder = null;
    /**
     * @var null
     */
    public $newOrder = null;

    /**
     * @var array
     */
    public $orderInformation;
    /**
     * Edit An order. Cancel old order and crate a new order.
     *
     * @param array $stringData
     * @return \I95DevConnect\MessageQueue\Model\AbstractDataPersistence
     */
    public function editOrder($stringData)
    {
        try {
            $this->orderInformation = $stringData;
            $this->oldOrder = $this->editOrderValidator->validateOrderToEdit($stringData);
            $this->orderInformation['isEditOrder'] = true;
            $this->orderInformation['parentOrder'] = $this->oldOrder;
            //@Hrusieksh Added Skip Observer Code
            $this->dataHelper->unsetGlobalValue(self::I95OBSKIP);
            $this->dataHelper->setGlobalValue(self::I95OBSKIP, true);
            $result = $this->orderCreate->createOrder($this->orderInformation, 'order', 'ERP');
            if ($result->status === Data::SUCCESS) {
                $this->updateOldOrder($result->resultData);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__($result->message));
            }
            $this->dataHelper->unsetGlobalValue(self::I95OBSKIP);
            $aftereventname = 'erpconnect_messagequeuetomagento_aftersave_editOrder';
            $this->eventManager->dispatch($aftereventname, ['orderObject' => $this]);
            if ($this->newOrder->getIncrementId()) {
                return $this->setResponse(
                    Data::SUCCESS,
                    "edit_order_success",
                    $this->newOrder->getIncrementId()
                );
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('edit_order_008'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            return $this->setResponse(
                Data::ERROR,
                $ex->getMessage(),
                null
            );
        }
    }

    /**
     * Sent RelationChildId & RelationChildRealId to old order and cancel.
     *
     * @param string $newOrderId
     */
    public function updateOldOrder($newOrderId)
    {
        try {
            $this->newOrder = $this->editOrderHelper->getOrderByIncrementId($newOrderId);
            // @updatedBy Subhan. Update old order state and status after updating
            if ($this->oldOrder->getId()) {
                $this->oldOrder->setRelationChildId($this->newOrder->getId());
                $this->oldOrder->setRelationChildRealId($this->newOrder->getIncrementId());
                //@Hrusieksh Added Oberver Skipper
                $this->dataHelper->unsetGlobalValue(self::I95OBSKIP);
                $this->dataHelper->setGlobalValue(self::I95OBSKIP, true);
                $this->orderManagement->cancel($this->oldOrder->getEntityId());
                $this->oldOrder->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
                $this->oldOrder->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
                $this->oldOrder->save();
                $this->dataHelper->unsetGlobalValue(self::I95OBSKIP);
            }
        } catch (\Exception $ex) {
            throw new \Magento\Framework\Exception\LocalizedException(__($ex->getMessage()));
        }
    }
}
