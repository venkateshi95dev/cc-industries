<?php

namespace I95DevConnect\MessageQueue\Controller\Adminhtml\MessageQueue;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\MessageQueue\Api\LoggerInterfaceFactory;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\I95DevServer\Model\ServiceMethod\ForwardSync\MQToErp\SendEntityResponse;

class UpdateByClosed extends Action
{
    public const MASSACTION_PREPARE_KEY = 'massaction_prepare_key';

    /**
     * Authorization resource
     */
    const ADMIN_RESOURCE = 'I95DevConnect_MessageQueue::report';

    /**
     * @var I95DevMagMQRepositoryInterfaceFactory
     */
    protected $messageQueueRepository;

    /**
     * @var LoggerInterfaceFactory
     */
    protected $logger;

    /**
     * @var SendEntityResponse
     */
    protected $sendEntityResponse;

    public function __construct(
        Context $context,
        I95DevMagMQRepositoryInterfaceFactory $messageQueueRepository,
        LoggerInterfaceFactory $logger,
        SendEntityResponse $sendEntityResponse
    ) {
        parent::__construct($context);
        $this->messageQueueRepository = $messageQueueRepository;
        $this->logger = $logger;
        $this->sendEntityResponse = $sendEntityResponse;
    }

    /**
     * Execute mass action
     */
    public function execute()
    {
        $updatedIds = [];

        try {
            $data = $this->getRequest()->getPostValue();

            if (isset($data[self::MASSACTION_PREPARE_KEY])
                && !empty($data[$data[self::MASSACTION_PREPARE_KEY]])
            ) {
                $selectedIds = $data[$data[self::MASSACTION_PREPARE_KEY]];
                sort($selectedIds);

                foreach ($selectedIds as $msgId) {
                    $messageQueue = $this->messageQueueRepository
                        ->create()
                        ->get($msgId);

                    // Update ONLY ERROR records
                    if ($messageQueue->getMsgId()
                        && $messageQueue->getStatus() == Data::ERROR
                    ) {
                        $this->sendEntityResponse
                            ->updateMessageQueue($msgId, Data::CLOSED);

                        $updatedIds[] = $msgId;
                    }
                }
            }

            if (!empty($updatedIds)) {
                $this->messageManager->addSuccessMessage(
                    __('Selected records updated as Closed: %1', implode(', ', $updatedIds))
                );
            } else {
                $this->messageManager->addNoticeMessage(
                    __('No Error records found to update.')
                );
            }
        } catch (LocalizedException $e) {
            $this->logger->create()->createLog(
                __METHOD__,
                $e->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );

            $this->messageManager->addErrorMessage(
                __('Something went wrong while updating records.')
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Unexpected error occurred.')
            );
        }

        /** Redirect back to grid */
        $resultRedirect = $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}
