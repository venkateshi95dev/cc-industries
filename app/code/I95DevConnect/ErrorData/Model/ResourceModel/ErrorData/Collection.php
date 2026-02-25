<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */

namespace I95DevConnect\ErrorData\Model\ResourceModel\ErrorData;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * collection class for error data
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'msg_id';

    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this -> _init(
            'I95DevConnect\ErrorData\Model\ErrorData',
            'I95DevConnect\ErrorData\Model\ResourceModel\ErrorData'
        );
        $this->_map['fields']['msg_id'] = 'main_table.msg_id';
    }

    /**
     * Update Data for given condition for collection
     *
     * @param string $condition
     * @param array $columnData
     * @return array
     */
    public function setTableRecords($condition, $columnData)
    {
        return $this->getConnection()->update(
            $this->getTable('i95dev_error_notification'),
            $columnData,
            $condition
        );
    }
}
