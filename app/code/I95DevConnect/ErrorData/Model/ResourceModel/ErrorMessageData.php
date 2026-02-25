<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */

namespace I95DevConnect\ErrorData\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ErrorMessageData Resource Model
 */
class ErrorMessageData extends AbstractDb
{

    /**
     * Error message data constructor
     */
    protected function _construct()
    {
        $this->_init('i95dev_error_message', 'id');
    }
}
