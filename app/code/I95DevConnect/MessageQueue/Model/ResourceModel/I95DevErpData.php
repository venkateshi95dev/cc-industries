<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * I95Dev Erp Data resource model
 */
class I95DevErpData extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()// phpcs:ignore
    {

        $this->_init('i95dev_erp_data', 'data_id');
    }
}
