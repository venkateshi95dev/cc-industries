<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 10:51 AM
 * @brief
 */

namespace Crimson\MachOrder\Model\Service;

use Crimson\MachBase\Service\ZipStoreIdByCode;
use Crimson\MachOrder\Model\Api\AddOrder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Class ExportOrdersToMach
 * @package Crimson\MachOrder\Model\Service
 */
class ExportOrdersToMach
{

    public function __construct(
        protected AddOrder $addOrder,
        protected OrderRepositoryInterface $orderRepository,
        protected SearchCriteriaBuilder $searchCriteriaBuilder,
        protected ZipStoreIdByCode $zipStoreIdByCode
    ) {}

    /**
     * @return void
     */
    public function execute(): void
    {
        $ordersSearchResult = $this->_getUnsubmittedOrderSearchResult();
        if ($ordersSearchResult->getTotalCount() === 0) {
            return;
        }

        foreach ($ordersSearchResult->getItems() as $order) {
            try {
                $this->addOrder->addOrder($order);
            } catch (\Exception $e) {

            }
        }
    }

    /**
     * @return OrderSearchResultInterface
     */
    protected function _getUnsubmittedOrderSearchResult(): OrderSearchResultInterface
    {
        $this->searchCriteriaBuilder
            ->addFilter('extension_attribute_mach_order_number.mach_submitted', 0)
            ->addFilter('state', $this->_getUnexportableStates(), 'nin')
            ->addFilter('main_table.store_id', $this->zipStoreIdByCode->get())
            ->addFilter('extension_attribute_mach_order_number.mach_submit_hold', 0);

        return $this->orderRepository->getList($this->searchCriteriaBuilder->create());
    }

    /**
     * @return array
     */
    protected function _getUnexportableStates(): array
    {
        return [
            Order::STATE_CLOSED,
            Order::STATE_COMPLETE,
            Order::STATE_CANCELED,
        ];
    }
}
