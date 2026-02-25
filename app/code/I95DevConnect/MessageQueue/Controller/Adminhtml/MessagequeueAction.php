<?php

namespace I95DevConnect\MessageQueue\Controller\Adminhtml;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class MessagequeueAction
 * @package I95DevConnect\MessageQueue\Controller\Adminhtml
 */
abstract class MessagequeueAction extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var Data
     */
    public $helperData;

    /**
     * @var UrlInterface
     */
    public $urlInterface;

    /**
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helperData
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helperData,
        UrlInterface $urlInterface
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helperData = $helperData;
        $this->urlInterface = $urlInterface;
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
