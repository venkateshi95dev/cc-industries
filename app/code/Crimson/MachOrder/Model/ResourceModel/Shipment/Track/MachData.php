<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/4/2019 11:41 AM
 * @brief
 */

namespace Crimson\MachOrder\Model\ResourceModel\Shipment\Track;

use Crimson\Sales\Model\ResourceModel\Order\AbstractMachData;

/**
 * Class MachData
 * @package Crimson\MachOrder\Model\ResourceModel\Shipment\Track
 */
class MachData extends AbstractMachData
{
    protected function _construct()
    {
        $this->_init('sales_shipment_track_mach_data', 'id');
    }

    /**
     * @return string
     */
    protected function _getLinkedFieldId(): string
    {
        return 'track_id';
    }
}
