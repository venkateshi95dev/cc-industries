<?php
/**
 * @namespace   Crimson
 * @module      MachShipping
 * @date        06/07/2022 12:00 am
 * @brief
 */

namespace Crimson\MachShipping\Model\ResourceModel\UpsDeliverySaturdays;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection 
 *
 * @package Crimson\Badges\Model\ResourceModel\Order
 * @method \Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(
            \Crimson\MachShipping\Model\UpsDeliverySaturdays::class,
            \Crimson\MachShipping\Model\ResourceModel\UpsDeliverySaturdays::class
        );
    }
}
