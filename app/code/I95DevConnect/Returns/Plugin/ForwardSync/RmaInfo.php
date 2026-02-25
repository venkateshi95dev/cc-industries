<?php

/**
 *
 */

namespace I95DevConnect\Returns\Plugin\ForwardSync;

use I95DevConnect\I95DevServer\Model\I95DevServerRepository;
use I95DevConnect\MessageQueue\Block\Adminhtml\Order\View\Info;
use I95DevConnect\Returns\Model\RmaEntityFactory;
use Magento\Framework\Event\Manager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Rma\Model\Rma;
use I95DevConnect\MessageQueue\Helper\Data;

class RmaInfo
{
    /**
     *
     * @var Manager
     */
    public $eventManager;
    /**
     * @var Info
     */
    // @codingStandardsIgnoreLine
    public $_orderInfo;
    /**
     * @var DateTime
     */
    // @codingStandardsIgnoreLine
    public $_date;
    /**
     * @var RmaEntityFactory
     */
    // @codingStandardsIgnoreLine
    protected $_i95devRma;
    /**
     * @var Data
     */
    // @codingStandardsIgnoreLine
    public $_dataHelper;
    /**
     * @var \I95DevConnect\MessageQueue\Model\SalesOrderFactory
     */
    // @codingStandardsIgnoreLine
    public $_customSalesOrder;

    /**
     * RmaInfo constructor.
     * @param Manager $eventManager
     * @param RmaEntityFactory $rmaEntity
     * @param Info $orderInfo
     * @param DateTime $date
     * @param Data $dataHelper
     * @param \I95DevConnect\MessageQueue\Model\SalesOrderFactory $custOrder
     */
    public function __construct(
        Manager $eventManager,
        RmaEntityFactory $rmaEntity,
        Info $orderInfo,
        DateTime $date,
        Data $dataHelper,
        \I95DevConnect\MessageQueue\Model\SalesOrderFactory $custOrder
    ) {
        $this->eventManager = $eventManager;
        $this->_i95devRma = $rmaEntity;
        $this->_orderInfo = $orderInfo;
        $this->_date = $date;
        $this->_dataHelper = $dataHelper;
        $this->_customSalesOrder = $custOrder;
    }

    /**
     * After save rma
     *
     * @param Rma $subject
     * @param object $result
     * @param array $data
     * @return mixed
     */
    public function afterSaveRma(Rma $subject, $result, $data) // NOSONAR
    {
        if ($this->_dataHelper->getGlobalValue('i95_observer_skip')) {
            return $result;
        }

        if ($result && null !== $result->getId()) {
            $loadcustomRma = $this->_i95devRma->create()->load($result->getId(), 'return_id');
            if ($loadcustomRma->getReturnId()) {
                $this->customRmaRecord($loadcustomRma, $result);
            } else {
                $customRma = $this->_i95devRma->create();
                $customRma->setReturnId($result->getId());
                $this->customRmaRecord($customRma, $result);
            }
        }

        $aftereventname = 'magentorma_aftersave';
        $this->eventManager->dispatch($aftereventname, ['data_object' => $result]);
        return $result;
    }

    /**
     * Custom rma record
     *
     * @param object $customRma
     * @param object $result
     */
    public function customRmaRecord($customRma, $result)
    {
        $custSales = $this->_customSalesOrder->create()
            ->load($result->getOrder()->getIncrementId(), 'source_order_id');
        $targetOrderId = $custSales->getTargetOrderId();
        $customRma->setTargetOrderId($targetOrderId);
        $customRma->setCreatedDt($this->_date->gmtDate());
        $customRma->setUpdatedDt($this->_date->gmtDate());
        $customRma->setOrigin('website');
        $customRma->setUpdateBy('magento');
        $customRma->save();
    }
}
