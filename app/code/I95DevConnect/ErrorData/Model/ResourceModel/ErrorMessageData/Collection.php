<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */

namespace I95DevConnect\ErrorData\Model\ResourceModel\ErrorMessageData;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Collection class for error messages
 */
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this -> _init(
            'I95DevConnect\ErrorData\Model\ErrorMessageData',
            'I95DevConnect\ErrorData\Model\ResourceModel\ErrorMessageData'
        );
    }
}
