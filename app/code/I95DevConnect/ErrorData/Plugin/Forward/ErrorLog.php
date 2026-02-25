<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */

namespace I95DevConnect\ErrorData\Plugin\Forward;

use \I95DevConnect\I95DevServer\Model\ServiceMethod\ForwardSync\MQToErp\SendEntityData;
use \I95DevConnect\ErrorData\Helper\Generic;
use I95DevConnect\ErrorData\Plugin\Forward\AbstractErrorLog;

/**
 * Plugin class responsible for saving the error information of ERP to Magento sync flow
 */
class ErrorLog extends AbstractErrorLog
{

    /**
     * Before plugin method to validate the entity data required for order creation
     *
     * @param SendEntityData $subject
     * @param object $result
     * @param int $messageId
     * @param string $status
     * @param string $errorMessage
     * @param int $code
     * @return mixed|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aftersaveRecord(
        SendEntityData $subject, //NOSONAR
        $result,
        $messageId,
        $status,
        $errorMessage = '',
        $code = 107
    ) {
        return $this->forwardResponseErrorLog($result, $messageId, $status, $errorMessage, $code = 110);
    }
}
