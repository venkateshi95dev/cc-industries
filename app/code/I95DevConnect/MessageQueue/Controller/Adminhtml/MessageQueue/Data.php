<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Controller\Adminhtml\MessageQueue;

use I95DevConnect\MessageQueue\Api\I95DevErpDataRepositoryInterfaceFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Controller for rendering data string in Message Queue
 */
class Data extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     *
     * @var I95DevErpDataRepositoryInterfaceFactory
     */
    public $i95DevErpData;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param I95DevErpDataRepositoryInterfaceFactory $i95DevErpData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        I95DevErpDataRepositoryInterfaceFactory $i95DevErpData
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->i95DevErpData = $i95DevErpData;
    }

    /**
     * Is allowed
     *
     * @return bool
     */
    protected function _isAllowed()// phpcs:ignore
    {
        return $this->_authorization->isAllowed('I95DevConnect_MessageQueue::report');
    }

    /**
     * Render Message Queue data string
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $msgId = $data['data_id'];
        $mesageQueueData = $this->i95DevErpData->create()->load($msgId)->getData();
        $data = $mesageQueueData['data_string'];
        $data = json_encode(json_decode($data), JSON_PRETTY_PRINT);
        $this->getResponse()->setBody($data);
    }
}
