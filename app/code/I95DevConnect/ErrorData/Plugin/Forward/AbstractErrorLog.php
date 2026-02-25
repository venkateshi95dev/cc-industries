<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */

namespace I95DevConnect\ErrorData\Plugin\Forward;

use \I95DevConnect\I95DevServer\Model\ServiceMethod\ForwardSync\MQToErp\SendEntityData;
use I95DevConnect\ErrorData\Model\InstantReport;
use Magento\Framework\Exception\LocalizedException;
use I95DevConnect\ErrorData\Model\ErrorDataFactory;
use I95DevConnect\ErrorData\Model\ErrorMessageDataFactory;
use I95DevConnect\ErrorData\Helper\Generic;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;

/**
 * Plugin class responsible for saving the error information of ERP to Magento sync flow
 */
class AbstractErrorLog
{
    /**
     * @var LoggerInterfaceFactory
     */
    protected $logger;

    /**
     * @var InstantReport
     */
    public $instantReport;

    /**
     * @var ErrorDataFactory
     */
    public $errorData;

    /**
     * @var ErrorMessageDataFactory
     */
    public $errorMsgData;

    /**
     * @var Generic
     */
    public $genericHelper;

    /**
     * AbstractErrorLog constructor.
     * @param InstantReport $instantReport
     * @param ErrorDataFactory $errorData
     * @param ErrorMessageDataFactory $errorMsgData
     * @param Generic $genericHelper
     * @param LoggerInterfaceFactory $logger
     */
    public function __construct(
        InstantReport $instantReport,
        ErrorDataFactory $errorData,
        ErrorMessageDataFactory $errorMsgData,
        Generic $genericHelper,
        LoggerInterfaceFactory $logger
    ) {
        $this->instantReport = $instantReport;
        $this->errorData = $errorData;
        $this->errorMsgData = $errorMsgData;
        $this->genericHelper = $genericHelper;
        $this->logger = $logger;
    }

    /**
     * Send instant error report whenever an error occurs in record syncing.
     *
     * @param int $msgId
     * @param strig $origin
     * @param string $entityCode
     * @param string $flowType
     */
    public function sendReport($msgId, $origin, $entityCode, $flowType)
    {
        $outboundEntities = $this->genericHelper->getEnabledEntities($flowType);
        if (empty($outboundEntities) || !in_array($entityCode, $outboundEntities)) {
            return;
        }
        $this->instantReport->sendReport($msgId, $origin);
    }

    /**
     * Forward response error log
     *
     * @param object $result
     * @param int $messageId
     * @param string $status
     * @param string $errorMessage
     * @param int $code
     * @return mixed|void
     * @throws LocalizedException
     */
    public function forwardResponseErrorLog($result, $messageId, $status, $errorMessage, $code = 110)
    {
        try {
            if ($status !== Data::ERROR || empty($errorMessage)) {
                return $result;
            }

            // phpcs:disable
            if (is_object($errorMessage) && get_class($errorMessage) == \Magento\Framework\Phrase::class) {
                $errorMessage = $errorMessage->getText();
            }
            // phpcs:enable

            $entityCode = $this->genericHelper->getMQData($messageId, "forward")->getEntityCode();

            $errorLogRec = $this->errorData->create()
                ->getCollection()
                ->addFieldToFilter("msg_id", $messageId)
                ->addFieldToFilter("origin", "magento");

            $errorLogSize = $errorLogRec->getSize();
            if ($errorLogSize) {
                $this->genericHelper->deleteErrorMessageData($errorLogRec->getData(), $errorMessage, $status);
            }
             $this->genericHelper->updateErrorData($messageId, $errorMessage, $entityCode, $code, "magento");

            if ($this->genericHelper->getReportType() == "Instant") {
                $this->sendReport($messageId, "magento", $entityCode, "forward");
            }

        } catch (\Exception $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), Generic::I95REPORT, Generic::CRITICAL);
            throw new LocalizedException(__($ex->getMessage()));
        }
    }
}
