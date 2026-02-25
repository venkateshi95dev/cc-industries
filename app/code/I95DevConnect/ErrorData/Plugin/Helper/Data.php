<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2020 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ErrorData
 */
namespace I95DevConnect\ErrorData\Plugin\Helper;

use I95DevConnect\ErrorData\Model\ErrorDataFactory;
use I95DevConnect\ErrorData\Model\ErrorMessageDataFactory;
use I95DevConnect\MessageQueue\Helper\Data as HelperData;

/**
 * plugin to remove the error notification data on scheduled basis
 */
class Data
{
    /**
     * @var ErrorDataFactory
     */
    public $errorDataFactory;

    /**
     * @var ErrorMessageDataFactory
     */
    public $errorMessageDataFactory;

    /**
     * @var HelperData;
     */
    public $mqDataHelper;

    /**
     * @param ErrorDataFactory $errorDataFactory
     * @param ErrorMessageDataFactory $errorMessageDataFactory
     * @param HelperData $mqDataHelper
     */
    public function __construct(
        ErrorDataFactory $errorDataFactory,
        ErrorMessageDataFactory $errorMessageDataFactory,
        HelperData $mqDataHelper
    ) {
        $this->errorDataFactory = $errorDataFactory;
        $this->errorMessageDataFactory = $errorMessageDataFactory;
        $this->mqDataHelper = $mqDataHelper;
    }

    /**
     * After delete mq data
     *
     * @param HelperData $subject
     * @param object $mqEntity
     * @return mixed
     */
    public function afterDeleteMQData(
        HelperData $subject, //NOSONAR
        $mqEntity //NOSONAR
    ) {
        $this->deleteNotificationData("erp");

        return true;
    }

    /**
     * After delete mmq data
     *
     * @param HelperData $subject
     * @param object $mqEntity
     * @return mixed|void
     */
    public function afterDeleteMMQData(
        HelperData $subject, //NOSONAR
        $mqEntity //NOSONAR
    ) {
        try {
            $this->deleteNotificationData("magento");

            return true;
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), self::CLEAN, LoggerInterface::CRITICAL);
        }
    }

    /**
     * Delete notification data
     *
     * @param string $origin
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteNotificationData($origin)
    {
        $toDeleteDate = $this->mqDataHelper->getMQCleanDate();
        $errorDataRec = $this->errorDataFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter("origin", $origin)
            ->addFieldtoFilter('updated_at', ['to' => $toDeleteDate]);

        foreach ($errorDataRec as $record) {
            $this->errorDataFactory->create()->load($record['id'])->delete();
            $this->deleteErrorMsgData($record);
        }
    }

    /**
     * Delete error msg data
     *
     * @param array $notification_record
     */
    public function deleteErrorMsgData($notification_record)
    {
        $notificationDataList = $this->errorMessageDataFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter("notification_id", $notification_record['id']);

        foreach ($notificationDataList as $notificationData) {
            $this->errorMessageDataFactory->create()->load($notificationData['id'])->delete();
        }
    }
}
