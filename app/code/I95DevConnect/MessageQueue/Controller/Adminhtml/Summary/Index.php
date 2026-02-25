<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Controller\Adminhtml\Summary;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Controller for Inbound Summary
 */
class Index extends Action
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
     * Is allowed
     *
     * @return bool
     */
    protected function _isAllowed()// phpcs:disable
    {
        return $this->_authorization->isAllowed('I95DevConnect_MessageQueue::report');
    }

    /**
     * Render inbound summary
     *
     * @return Page
     */
    public function execute()
    {
        if ($this->helperData->isEnabledInAnyWebsite()) {
            $breadcrumb = ['label' => 'Summary Report', 'title' => 'Inbound Summary Report'];
            return $this->helperData->loadPage(
                'Inbound Summary Report',
                'I95DevConnect\MessageQueue\Block\Adminhtml\Summary',
                'I95DevConnect_MessageQueue::SummaryReport',
                $breadcrumb
            );
        } else {
            return $this->helperData->returnToIndex();
        }
    }
}
