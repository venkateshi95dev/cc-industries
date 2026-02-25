<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */

namespace I95DevConnect\ErrorData\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * data model for error message data class
 */
class ErrorMessageData extends AbstractModel
{
    /**
     * Error message data constructor
     */
    protected function _construct()
    {
        $this->_init('I95DevConnect\ErrorData\Model\ResourceModel\ErrorMessageData');
    }
}
