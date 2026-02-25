<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/4/2019 1:57 PM
 * @brief
 */

namespace Crimson\MachOrder\Model\Service;

use Crimson\MachBase\Model\Log;
use Crimson\MachBase\Model\MachConfig;
use Crimson\MachBase\Service\ZipStoreIdByCode;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime as DateTimeGenerator;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Sales\Model\Order;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Crimson\MachOrder\Model\ResourceModel\Order\MachData;

/**
 * Class UpdateOrdersFromMach
 * @package Crimson\MachOrder\Model\Service
 */
class UpdateOrdersFromMach
{

    CONST BATCH_SIZE = 300;


    protected DateTimeGenerator $dateTime;
    protected Log $log;
    protected MachConfig $machConfig;
    protected OrderRepositoryInterface $orderRepository;
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    protected UpdateOrderFromMach $updateOrderFromMach;
    protected SortOrderBuilder $sortOrderBuilder;
    protected ResourceOrder $resourceOrder;
    protected MachData $machData;

    public function __construct(
        MachConfig $machConfig,
        Log $log,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        UpdateOrderFromMach $updateOrderFromMach,
        SortOrderBuilder $sortOrderBuilder,
        ResourceOrder $resourceOrder,
        MachData $machData,
        DateTimeGenerator $dateTime,
        protected ZipStoreIdByCode $zipStoreIdByCode
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository       = $orderRepository;
        $this->machConfig            = $machConfig;
        $this->dateTime              = $dateTime;
        $this->log                   = $log;
        $this->updateOrderFromMach   = $updateOrderFromMach;
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->resourceOrder         = $resourceOrder;
        $this->machData              = $machData;
    }

    /**
     * @param int $monthsBack
     * @return void
     */
    public function execute(int $monthsBack): void
    {
        try {
            $orderIds = $this->_getOrderIds($monthsBack);
            if (!$orderIds) {
                return;
            }

            $this->log->getDebugLog()->debug(__('in machErpOrderUpdate with %1 orders', count($orderIds)));
            $sortOrder = $this->sortOrderBuilder
                ->setField(OrderInterface::CREATED_AT)
                ->setDirection(SortOrder::SORT_ASC)
                ->create();
            foreach (array_chunk($orderIds, self::BATCH_SIZE, true) as $ids) {
                $this->_processOrders(
                    $this->_getOrdersByIds($ids, $sortOrder)
                );
            }
        } catch (\Exception $e) {
            $this->log->getCriticalLog()->critical($e);
        }
    }

    /**
     * @param array $ids
     * @param SortOrder $sortOrder
     * @return array
     */
    protected function _getOrdersByIds(array $ids, SortOrder $sortOrder): array
    {
        $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::ENTITY_ID, $ids, 'in')
            ->setSortOrders([$sortOrder])
        ;
        $searchResult = $this->orderRepository->getList($this->searchCriteriaBuilder->create());

        return $searchResult->getTotalCount() ? $searchResult->getItems() : [];
    }

    /**
     * @param int $monthsBack
     * @return array
     * @throws LocalizedException
     */
    protected function _getOrderIds(int $monthsBack): array
    {
        $connection  = $this->resourceOrder->getConnection();
        $filters     = $this->_getFilters($monthsBack, $connection);
        //main query
        $select = $connection->select()
            ->from(['main_table' => $this->resourceOrder->getMainTable()])
            ->joinLeft(
                ['s_o_m_o' => $this->machData->getMainTable()],
                'main_table.entity_id = s_o_m_o.order_id'
            )
            ->where($filters)
        ;
        //resetting columns
        $select
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'entity_id'     => 'main_table.entity_id',
            ]);
        //sort order and group
        $select
            ->order('main_table.created_at ASC')
            ->group('main_table.entity_id')
        ;

        $data = $connection->fetchCol($select);
        if (!$data) {
            $data = [];
        }

        return $data;
    }

    /**
     * @param array $orders
     * @return void
     */
    protected function _processOrders(array $orders): void
    {
        foreach ($orders as $order) {
            try {
                $this->log->getDebugLog()->debug(
                    __('in machErpOrderUpdate doing order %1', $order->getIncrementId())
                );
                $this->updateOrderFromMach->execute($order);
            } catch (\Exception $e) {
                //api will log as necessary.
            }
        }
    }

    /**
     * @param int $monthsBack
     * @param $connection
     * @return string
     */
    protected function _getFilters(int $monthsBack, $connection): string
    {
        $monthsBack  = sprintf(' -%d month', $monthsBack);
        $fromDate    = $this->dateTime->gmtDate(DateTime::DATETIME_PHP_FORMAT, $monthsBack);
        $toDate      = $this->dateTime->gmtDate(DateTime::DATETIME_PHP_FORMAT, '-1 day');
        $someFilters  = $connection->quoteInto('main_table.created_at >= ? ', $fromDate);
        $someFilters .= " AND ";
        $someFilters .= $connection->quoteInto('main_table.created_at < ? ', $toDate);
        $someFilters .= " AND ";
        $someFilters .= $connection->quoteInto('main_table.store_id = ? ', $this->zipStoreIdByCode->get());
        $someFilters .= " AND ";
        $someFilters .= $connection->quoteInto('main_table.state NOT IN (?) ', $this->_getExcludedStates());
        $someFilters .= " AND ";
        $someFilters .= "s_o_m_o.mach_order_number IS NOT NULL";
        $someFilters .= " AND ";
        $someFilters .= "s_o_m_o.mach_submitted = 1";

        return $someFilters;
    }

    /**
     * @return array
     */
    private function _getExcludedStates(): array
    {
        return [
            Order::STATE_CLOSED,
            Order::STATE_COMPLETE,
            Order::STATE_CANCELED,
        ];
    }
}
