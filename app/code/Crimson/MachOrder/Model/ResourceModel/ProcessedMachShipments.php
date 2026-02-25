<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/1/2019 3:20 PM
 * @brief
 */

namespace Crimson\MachOrder\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class ProcessedMachShipments
 * @package Crimson\MachOrder\Model\ResourceModel
 */
class ProcessedMachShipments extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sales_order_processed_mach_shipments', 'id');
    }

    /**
     * @param $machOrderNumber
     *
     * @return int
     * @throws LocalizedException
     */
    public function hasProcessedMachOrderNumberShipment($machOrderNumber): int
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), 'COUNT(*)')
            ->where('mach_order_number = ?', $machOrderNumber);

        return (int)$this->getConnection()->fetchOne($select) > 0;
    }

    /**
     * @param OrderInterface $order
     * @param string         $machOrderNumber
     *
     * @return $this
     * @throws LocalizedException
     */
    public function markMachOrderNumberShipmentProcessed(OrderInterface $order, $machOrderNumber): ProcessedMachShipments
    {
        $data = [
            'order_id'          => $order->getEntityId(),
            'mach_order_number' => $machOrderNumber,
        ];
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->getConnection()->insertArray(
            $this->getMainTable(), array_keys($data), [$data], AdapterInterface::INSERT_IGNORE
        );

        return $this;
    }
}
