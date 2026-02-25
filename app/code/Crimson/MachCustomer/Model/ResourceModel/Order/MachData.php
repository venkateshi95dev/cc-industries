<?php
/**
 * @namespace   Crimson
 * @module      MachCustomer
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/28/2019 8:49 AM
 * @brief
 */

namespace Crimson\MachCustomer\Model\ResourceModel\Order;

use Crimson\Sales\Model\ResourceModel\Order\AbstractMachData;

/***
 * Class MachData
 * @package Crimson\MachCustomer\Model\ResourceModel\Order
 */
class MachData extends AbstractMachData
{
    protected function _construct()
    {
        $this->_init('sales_order_mach_customer', 'id');
    }
}
