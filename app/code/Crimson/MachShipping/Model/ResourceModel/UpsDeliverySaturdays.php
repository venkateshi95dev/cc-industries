<?php
/**
 * @namespace   Crimson
 * @module      MachShipping
 * @date        06/07/2022 12:00 am
 * @brief
 */

namespace Crimson\MachShipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class UpsDeliverySaturdays extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('crimson_ups_delivery_satudays_zipcodes', 'entity_id');
    }
}
