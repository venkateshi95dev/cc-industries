<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Controller\Adminhtml\MessageQueue;

use Exception;
use I95DevConnect\MessageQueue\Api\Data\I95DevErpMQInterfaceFactory;
use I95DevConnect\MessageQueue\Api\DataPersistenceInterfaceFactory;
use I95DevConnect\MessageQueue\Api\I95DevErpMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Model\AbstractDataPersistence;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Controller for mass syncing of message queue records
 */
class MassSync extends Action
{
    public const MASSACTION_PREPARE_KEY = 'massaction_prepare_key';

    /**
     * @var \Magento\Framework\Message\ManagerInterface
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
     * @var I95DevErpMQRepositoryInterfaceFactory
     */
    public $i95DevErpMQRepository;

    /**
     * @var DataPersistenceInterfaceFactory
     */
    public $dataPersistence;

    /**
     * @var I95DevErpMQInterfaceFactory
     */
    public $i95DevErpMQFactory;

    /**
     * @var int
     */
    public $statusCode = 2;

    /**
     * @var inManagerInterfacet
     */
    public $eventManager;

    /**
     *
     * @param Context $context
     * @param AbstractDataPersistence $abstractDataPersistence
     * @param LoggerInterfaceFactory $logger
     * @param I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository
     * @param DataPersistenceInterfaceFactory $dataPersistence
     * @param I95DevErpMQInterfaceFactory $i95DevErpMQFactory
     * @param ResultFactory $resultFactory
     * @param ManagerInterface $eventManager
     */
    public function __construct( // NOSONAR
        Context $context,
        AbstractDataPersistence $abstractDataPersistence,
        LoggerInterfaceFactory $logger,
        I95DevErpMQRepositoryInterfaceFactory $i95DevErpMQRepository,
        DataPersistenceInterfaceFactory $dataPersistence,
        I95DevErpMQInterfaceFactory $i95DevErpMQFactory,
        ResultFactory $resultFactory,
        ManagerInterface $eventManager
    ) {
        parent::__construct($context);
        $this->messageManager = $context->getMessageManager();
        $this->abstractDataPersistence = $abstractDataPersistence;
        $this->logger = $logger;
        $this->i95DevErpMQRepository = $i95DevErpMQRepository;
        $this->dataPersistence = $dataPersistence;
        $this->i95DevErpMQFactory = $i95DevErpMQFactory;
        $this->resultFactory = $resultFactory;
        $this->eventManager = $eventManager;
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
            $data = $this->getRequest()->getPostValue();
            if (isset($data[self::MASSACTION_PREPARE_KEY]) && !empty($data[$data[self::MASSACTION_PREPARE_KEY]])) {
                $selectedIds = $data[$data[self::MASSACTION_PREPARE_KEY]];
                sort($selectedIds);
                $product_mapping_counter = 0;
                foreach ($selectedIds as $msgId) {
                    $messageQueue = $this->i95DevErpMQRepository->create()->get($msgId);
                    if ($messageQueue->getMsgId() && $messageQueue->getDataString()
                        && $messageQueue->getStatus() != \I95DevConnect\MessageQueue\Helper\Data::PROCESSING
                    ) {
                        $product_mapping_counter = $this->attributeMapEvent($messageQueue, $product_mapping_counter);

                        // @Hrusikesh removed save message queue status to processing
                        //@Hrusikesh Added MessageId as extra parameter
                        $response = $this->dataPersistence->create()->createEntity(
                            $messageQueue->getEntityCode(),
                            $messageQueue->getDataString(),
                            $messageQueue->getMsgId(),
                            null
                        );

                        if (isset($response)) {
                            $this->abstractDataPersistence->updateErpMQStatus(
                                $response->getStatus(),
                                $response->getResultdata(),
                                $response->getMessage(),
                                $msgId,
                                $response->getCode()
                            );
                        } else {
                            $this->abstractDataPersistence->updateErpMQStatus(
                                \I95DevConnect\MessageQueue\Helper\Data::ERROR,
                                null,
                                'Some issue occur while saving data. Please contact admin.',
                                $msgId,
                                105
                            );
                        }
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
            $this->abstractDataPersistence->updateErpMQStatus(
                \I95DevConnect\MessageQueue\Helper\Data::ERROR,
                null,
                $e->getMessage(),
                $msgId,
                $e->getCode()
            );
        }
        $this->messageManager->addSuccess(
            "Please check the Updated status for selected IDS :- " . implode(",", $selectedIds)
        );

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        // @codingStandardsIgnoreStart
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        // @codingStandardsIgnoreEnd
        return $resultRedirect;
    }

    /**
     * Attribute mapping event
     *
     * @param object $messageQueue
     * @param int $product_mapping_counter
     * @return int|mixed
     */
    public function attributeMapEvent($messageQueue, $product_mapping_counter)
    {
        if ($messageQueue->getEntityCode() == "product" && $product_mapping_counter == 0) {
            $this->eventManager->dispatch('fetch_product_mapping_reverse');
            $product_mapping_counter = 1;
        }

        return $product_mapping_counter;
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
}
