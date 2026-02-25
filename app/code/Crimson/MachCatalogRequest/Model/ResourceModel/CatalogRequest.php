<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/14/2019 3:45 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class CatalogRequest
 * @package Crimson\MachCatalogRequest\Model\ResourceModel
 */
class CatalogRequest extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('mach_catalog_requests', 'request_id');
    }
}
