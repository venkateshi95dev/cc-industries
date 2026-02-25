<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Controller\Adminhtml\MessageQueue;

use I95DevConnect\MessageQueue\Model\ErrorUpdateData;
use I95DevConnect\MessageQueue\Model\ErrorUpdateDataFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Controller for rendering error message in Message Queue
 */
class ErrorData extends Action
{
    /**
     * @var Context
     */
    public $context;
    /**
     * @var ErrorUpdateData
     */
    public $modelErrorUpdateDataFactory;
    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @param Context $context
     * @param ErrorUpdateDataFactory $modelErrorUpdateDataFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        ErrorUpdateDataFactory $modelErrorUpdateDataFactory,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->modelErrorUpdateDataFactory = $modelErrorUpdateDataFactory;
        $this->resultJsonFactory        = $resultJsonFactory;
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
     * Render Message Queue error message
     *
     * @return void
     */
    public function execute()
    {
        $jsonResult = $this->resultJsonFactory->create();

        $data = $this->getRequest()->getPostValue();
        $errorId = $data['error_id'];
        $mesageQueueErrorData = $this->modelErrorUpdateDataFactory->create()->load($errorId)->getData();
        if (!empty($mesageQueueErrorData)) {
            $list = explode(",", $mesageQueueErrorData['msg']);
            $msg = "";
            foreach ($list as $lt) {
                if ($msg != "") {
                    $msg .= ",";
                }
                $msg .= __(trim($lt));
            }
        } else {
            $msg = "No record found for this Error ID :-" . $errorId . " please contact I95Dev";
        }

        $jsonResult->setData($msg);
        return $jsonResult;
    }
}
