<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */

namespace I95DevConnect\ErrorData\Plugin\Reverse;

use I95DevConnect\ErrorData\Model\ErrorDataFactory;
use I95DevConnect\ErrorData\Model\ErrorMessageDataFactory;
use I95DevConnect\ErrorData\Model\InstantReport;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use \I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use \I95DevConnect\ErrorData\Helper\Generic;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Plugin class responsible for saving the error information of ERP to Magento sync flow
 */
class ErrorLog
{
    public const ERP = "erp";

    /**
     * @var ErrorDataFactory
     */
    public $errorData;

    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var ErrorMessageDataFactory
     */
    public $errorMsgData;

    /**
     * @var Generic
     */
    public $genericHelper;

    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @var InstantReport
     */
    public $instantReport;

    /**
     *
     * @param ErrorDataFactory $errorData
     * @param ErrorMessageDataFactory $errorMsgData
     * @param LoggerInterfaceFactory $logger
     * @param DateTime $date
     * @param Generic $genericHelper
     * @param InstantReport $instantReport
     */
    public function __construct(
        ErrorDataFactory $errorData,
        ErrorMessageDataFactory $errorMsgData,
        LoggerInterfaceFactory $logger,
        DateTime $date,
        Generic $genericHelper,
        InstantReport $instantReport
    ) {
        $this->errorData = $errorData;
        $this->errorMsgData = $errorMsgData;
        $this->logger = $logger;
        $this->date = $date;
        $this->genericHelper = $genericHelper;
        $this->instantReport = $instantReport;
    }

    /**
     * Before plugin method to validate the entity data required for order creation
     *
     * @param AbstractDataPersistence $subject
     * @param string $status
     * @param object $data
     * @param object $message
     * @param int $msgId
     * @param string $code
     *
     * @throws LocalizedException
     */
    public function beforeUpdateErpMQStatus(
        AbstractDataPersistence $subject, //NOSONAR
        $status,
        $data, //NOSONAR
        $message,
        $msgId,
        $code = 107
    ) {
        try {
            if ($status !== Data::ERROR || empty($message)) {
                return;
            }

            // phpcs:disable
            if (is_object($message) && get_class($message) == \Magento\Framework\Phrase::class) {
                $message = $message->getText();
            }
            // phpcs:enable

            $entityCode = $this->genericHelper->getMQData($msgId, "reverse")->getEntityCode();

            $errorLogRec = $this->errorData->create()
                ->getCollection()
                ->addFieldToFilter("msg_id", $msgId)
                ->addFieldToFilter("origin", self::ERP)
            ;
            $errorLogSize = $errorLogRec->getSize();
            if ($errorLogSize) {
                $this->genericHelper->deleteErrorMessageData($errorLogRec->getData(), $message, $status);
            }

            $this->genericHelper->updateErrorData($msgId, $message, $entityCode, $code, self::ERP);
            if ($this->genericHelper->getReportType() == "Instant") {
                $this->sendReport($msgId, self::ERP, $entityCode);
            }
        } catch (\Exception $ex) {
            $this->logger->create()->createLog(__METHOD__, $ex->getMessage(), Generic::I95REPORT, Generic::CRITICAL);
            throw new LocalizedException(__($ex->getMessage()));
        }
    }

    /**
     * Send instant error report whenever an error occurs in record syncing.
     *
     * @param int $msgId
     * @param string $origin
     * @param string $entityCode
     */
    public function sendReport($msgId, $origin, $entityCode)
    {
        $inboundEntities = $this->genericHelper->getEnabledEntities("reverse");

        if (empty($inboundEntities) || !in_array($entityCode, $inboundEntities)) {
            return;
        }
        $this->instantReport->sendReport($msgId, $origin);
    }
}
