<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */

namespace I95DevConnect\ErrorData\Plugin\Forward;

use I95DevConnect\ErrorData\Plugin\Forward\AbstractErrorLog;
use \I95DevConnect\I95DevServer\Model\ServiceMethod\ForwardSync\MQToErp\SendEntityResponse;
use \I95DevConnect\ErrorData\Helper\Generic;

/**
 * Plugin class responsible for saving the error information of ERP to Magento sync flow
 */
class PullResponseErrorLog extends AbstractErrorLog
{

    /**
     * After update message queue
     *
     * @param SendEntityResponse $subject
     * @param string $result
     * @param int $messageId
     * @param string $status
     * @param string $errorMessage
     * @param int $code
     * @return mixed|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterUpdateMessageQueue(
        SendEntityResponse $subject, //NOSONAR
        $result,
        $messageId,
        $status,
        $errorMessage = null,
        $code = 110
    ) {

        return $this->forwardResponseErrorLog($result, $messageId, $status, $errorMessage, $code = 110);
    }
}
