<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Controller\Adminhtml\MessageQueue;

use Exception;
use I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\DataPersistenceInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\I95DevServer\Model\ServiceMethod\ForwardSync\MQToErp\SendEntityResponse;

/**
 * Controller for mass syncing of message queue records
 */
class OutboundReSync extends Action
{
    public const MASSACTION_PREPARE_KEY = 'massaction_prepare_key';

    /**
     * @var SendEntityResponse
     */
    public $sendEntityResponse;

    /**
     * @var ManagerInterface
     */
    public $messageManager;

    /**
     * @var AbstractDataPersistence
     */
    public $abstractDataPersistence;

    /**
     * @var LoggerInterfaceFactory
     */
    public $logger;

    /**
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    public $i95DevMagMQRepository;

    /**
     * @var DataPersistenceInterfaceFactory
     */
    public $dataPersistence;

    /**
     * @var ResultFactory
     */
    public $resultFactory;

    /**
     * @var array
     */
    public $eligibleIds;

    /**
     *
     * @param Context $context
     * @param AbstractDataPersistence $abstractDataPersistence
     * @param LoggerInterfaceFactory $logger
     * @param I95DevMagMQRepositoryInterfaceFactory $i95DevMagMQRepository
     * @param DataPersistenceInterfaceFactory $dataPersistence
     * @param ResultFactory $resultFactory
     * @param SendEntityResponse $sendEntityResponse
     */
    public function __construct(
        Context $context,
        AbstractDataPersistence $abstractDataPersistence,
        LoggerInterfaceFactory $logger,
        I95DevMagMQRepositoryInterfaceFactory $i95DevMagMQRepository,
        DataPersistenceInterfaceFactory $dataPersistence,
        ResultFactory $resultFactory,
        SendEntityResponse $sendEntityResponse
    ) {
        parent::__construct($context);
        $this->messageManager = $context->getMessageManager();
        $this->abstractDataPersistence = $abstractDataPersistence;
        $this->logger = $logger;
        $this->i95DevMagMQRepository = $i95DevMagMQRepository;
        $this->dataPersistence = $dataPersistence;
        $this->resultFactory = $resultFactory;
        $this->sendEntityResponse = $sendEntityResponse;
    }

    /**
     * Is Allowed
     *
     * @return bool
     */
    protected function _isAllowed()// phpcs:ignore
    {
        return $this->_authorization->isAllowed('I95DevConnect_MessageQueue::report');
    }

    /**
     * Sync records
     *
     * @return Page
     * @throws Exception
     */
    public function execute()
    {
        $msgId = null;
        try {
            $selectedIds = [];
            $this->eligibleIds = [];
            $data = $this->getRequest()->getPostValue();
            if (isset($data[self::MASSACTION_PREPARE_KEY]) && !empty($data[$data[self::MASSACTION_PREPARE_KEY]])) {
                $selectedIds = $data[$data[self::MASSACTION_PREPARE_KEY]];
                sort($selectedIds);
                foreach ($selectedIds as $msgId) {
                    $messageQueue = $this->i95DevMagMQRepository->create()->get($msgId);
                    if ($messageQueue->getMsgId()
                        && $messageQueue->getStatus() == Data::ERROR
                    ) {
                        $this->sendEntityResponse->updateMessageQueue($msgId, Data::PENDING);
                        $this->eligibleIds[$msgId] = $msgId;
                    }
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->create()->createLog(
                __METHOD__,
                $e->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
        }
        $this->messageManager->addSuccess(
            "Please check the Updated status for the IDS :- " . implode(",", $this->eligibleIds)
        );

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        // @codingStandardsIgnoreStart
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        // @codingStandardsIgnoreEnd
        return $resultRedirect;
    }
}
