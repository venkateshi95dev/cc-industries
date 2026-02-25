<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Controller\Adminhtml\Pricelevel;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use I95DevConnect\PriceLevel\Helper\Data;

/**
 * Renders the Price Level tab
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
    public $data;

    /**
     * Class constructor to include all the dependencies
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $data
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $data
    ) {

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->data = $data;
    }
    // @codingStandardsIgnoreStart
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('I95DevConnect_PriceLevel::pricelevel');
    }
    // @codingStandardsIgnoreEnd

    /**
     * Price levels list
     *
     * @return Page
     */
    public function execute()
    {
        if (!$this->data->isPriceGroupsEnabledInAnyWebsite()) {
            $this->messageManager->addError('Please Enable the Price Level Extension');
            // @codingStandardsIgnoreStart
            return $this->_redirect('adminhtml/dashboard');
            // @codingStandardsIgnoreEnd
        }
        /**
         * @var Page $resultPage
         */
        $title = 'Price Levels';
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('I95DevConnect_PriceLevel::customer_pricelevel');
        $resultPage->getConfig()->getTitle()->prepend(__($title));
        $resultPage->addBreadcrumb(__($title), __($title));
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock('I95DevConnect\PriceLevel\Block\Adminhtml\PriceLevel')
        );
        return $resultPage;
    }
}
