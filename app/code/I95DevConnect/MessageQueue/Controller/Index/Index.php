<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Controller\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Controller for Message Queue report
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;
     /**
      * @var EntityUpdateDataFactory
      */
    public $modelEntityUpdateDataFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Message Queue Report render
     *
     * @return Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('I95DevConnect_MessageQueue::MessageQueue');
        $resultPage->getConfig()->getTitle()->prepend(__('Message Queue Report'));
        $resultPage->addBreadcrumb(__('MessageQueue Report'), __('Message Queue'));
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock('I95DevConnect\MessageQueue\Block\Adminhtml\MessageQueue')
        );
        return $resultPage;
    }
}
