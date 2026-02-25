<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Model\ServiceMethod\Forward;

use Exception;
use I95DevConnect\CloudConnect\Model\Logger;
use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\ErrorUpdateData;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class to get Response Information
 */
class GetInfoResponse
{
    /**
     * @var \I95DevConnect\CloudConnect\Helper\Data
     */
    public $cloudHelper;
    /**
     * @var string
     */
    public $erpName = 'ERP';
    /**
     * @var Logger
     */
    public $logger;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;
    /**
     * @var ErrorUpdateData
     */
    public $messageErrorModel;
    /**
     * @var I95DevMagMQInterfaceFactory
     */
    public $magentoMQData;
    /**
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $I95DevMagMQ;

    /**
     * Constructor for DI
     * @param \I95DevConnect\CloudConnect\Helper\Data $cloudHelper
     * @param I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQ
     * @param I95DevMagMQInterfaceFactory $magentoMQData
     * @param ErrorUpdateData $messageErrorModel
     * @param Logger $logger
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \I95DevConnect\CloudConnect\Helper\Data $cloudHelper,
        I95DevMagMQRepositoryInterfaceFactory $I95DevMagMQ,
        I95DevMagMQInterfaceFactory $magentoMQData,
        ErrorUpdateData $messageErrorModel,
        Logger $logger,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->cloudHelper = $cloudHelper;
        $this->I95DevMagMQ = $I95DevMagMQ;
        $this->magentoMQData = $magentoMQData;
        $this->messageErrorModel = $messageErrorModel;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Forward cloud msg id sync implementation
     *
     * @param object $request
     * @param string $entity
     * @return bool
     * @throws LocalizedException
     */
    public function sync($request, $entity)
    {
        try {
            if (is_array($request) && !empty($request)) {
                foreach ($request as $record) {
                    if ($record['result'] && $record['sourceId']) {
                        $sourceId = $record['sourceId'];
                        $mqRecordCollection = $this->I95DevMagMQ->create()->getCollection();
                        $mqRecordCollection->addFieldToSelect('msg_id')
                        ->addFieldToFilter("entity_code", $entity)
                        ->addFieldToFilter("erp_code", $this->cloudHelper->getErpComponent())
                        ->addFieldToFilter("magento_id", $sourceId)
                        ->setOrder('msg_id', 'DESC');

                        $mqRecordCollection->getSelect()->limit(1);

                        $msgId =  $mqRecordCollection->getFirstItem()->getMsgId();

                        if (empty($record['message'])) {
                            $I95DevMagMQObj = $this->I95DevMagMQ->create();
                            $magentoMQDataObj = $this->magentoMQData->create();
                            $magentoMQDataObj->setMsgId($msgId);
                            $magentoMQDataObj->setStatus(Data::SUCCESS);
                            $magentoMQDataObj->setDestinationMsgId($record['messageId']);
                            $I95DevMagMQObj->saveMQData($magentoMQDataObj);
                        } else {
                            $errorDataModel = $this->messageErrorModel->create();
                            $errorDataModel->setMsg($record['message']);
                            $errorDataModel->save();
                            $errorId = $errorDataModel->getId();
                            $I95DevMagMQObj = $this->I95DevMagMQ->create();
                            $magentoMQDataObj = $this->magentoMQData->create();
                            $magentoMQDataObj->setMsgId($sourceId);
                            $magentoMQDataObj->setErrorId($errorId);
                            $magentoMQDataObj->setStatus(Data::ERROR);
                            $magentoMQDataObj->setDestinationMsgId($msgId);
                            $I95DevMagMQObj->saveMQData($magentoMQDataObj);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->createLog(
                __METHOD__,
                $e->getMessage(),
                Logger::EXCEPTION,
                'critical'
            );
        }
        return true;
    }
}
