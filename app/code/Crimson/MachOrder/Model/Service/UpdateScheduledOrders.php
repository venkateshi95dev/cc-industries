<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/4/2019 2:23 PM
 * @brief
 */

namespace Crimson\MachOrder\Model\Service;

use Crimson\MachBase\Model\Api\HealthCheck;
use Crimson\MachBase\Model\Log;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachBase\Service\ZipStoreIdByCode;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class UpdateScheduledOrders
 * @package Crimson\MachOrder\Model\Service
 */
class UpdateScheduledOrders
{
    /**
     * @var HealthCheck
     */
    protected $healthCheck;
    /**
     * @var MachConfig
     */
    protected $machConfig;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var UpdateOrderFromMach
     */
    protected $updateOrderFromMach;

    public function __construct(
        MachConfig $machConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        UpdateOrderFromMach $updateOrderFromMach,
        HealthCheck $healthCheck,
        protected Log $log,
        protected ZipStoreIdByCode $zipStoreIdByCode
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository       = $orderRepository;
        $this->machConfig            = $machConfig;
        $this->updateOrderFromMach   = $updateOrderFromMach;
        $this->healthCheck = $healthCheck;
    }

    /**
     * @throws \Exception
     */
    public function execute(): void
    {
        try {
            $orders = $this->_getOrdersForUpdate();
            if (!$orders->getTotalCount()) {
                return;
            }

            foreach ($orders->getItems() as $order) {
                $this->updateOrderFromMach->execute($order);
            }
        } catch (\Exception $e) {
            $this->log->getCriticalLog()->critical($e);
        }
    }

    /**
     * @return OrderSearchResultInterface
     */
    protected function _getOrdersForUpdate(): OrderSearchResultInterface
    {
        $this->searchCriteriaBuilder
            ->addFilter('main_table.store_id', $this->zipStoreIdByCode->get())
            ->addFilter('extension_attribute_mach_order_number.mach_submitted', 1)
            ->addFilter('extension_attribute_mach_order_number.mach_update_scheduled', 1)
            ->addFilter('extension_attribute_mach_order_number.mach_submit_hold', 0);

        return $this->orderRepository->getList($this->searchCriteriaBuilder->create());
    }
}
