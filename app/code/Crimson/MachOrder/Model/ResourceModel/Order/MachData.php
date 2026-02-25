<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/28/2019 8:49 AM
 * @brief
 */

namespace Crimson\MachOrder\Model\ResourceModel\Order;

use Crimson\Sales\Model\ResourceModel\Order\AbstractMachData;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MachData
 * @package Crimson\MachOrder\Model\ResourceModel\Order
 */
class MachData extends AbstractMachData
{
    protected function _construct()
    {
        $this->_init('sales_order_mach_order', 'id');
    }

    /**
     * @param int $orderId
     *
     * @return string
     * @throws LocalizedException
     */
    public function getOrderNumberByOrderId(int $orderId): string
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), 'mach_order_id')
            ->where('order_id = ?', $orderId);

        return $this->getConnection()->fetchOne($select);
    }
}
