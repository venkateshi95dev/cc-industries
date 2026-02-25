<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/14/2019 3:45 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Model\ResourceModel\CatalogRequest;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Crimson\MachCatalogRequest\Model\ResourceModel\CatalogRequest
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'request_id';

    protected function _construct()
    {
        $this->_init(
            'Crimson\MachCatalogRequest\Model\CatalogRequest',
            'Crimson\MachCatalogRequest\Model\ResourceModel\CatalogRequest'
        );
    }
}
