<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Controller\Adminhtml\DiscountGroups;

use I95DevConnect\DiscountGroups\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Itemdiscountgroup extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Data
     */
    protected $discountGroupsHelper;

    /**
     * Class constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $discountGroupsHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $discountGroupsHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->discountGroupsHelper = $discountGroupsHelper;
    }

    /**
     * Item discount groups controller
     *
     * @return false|Page
     */
    public function execute()
    {
        $isEnabled = $this->discountGroupsHelper->isDiscountGroupsEnabledInAnyWebsite();
        if (!$isEnabled) {
              $this->messageManager->addError('Please Enable the Item Discount Groups Extension');
            // @codingStandardsIgnoreStart
            return $this->_redirect('adminhtml/dashboard');
            // @codingStandardsIgnoreEnd
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('I95DevConnect_DiscountGroups::itemdiscountGroups');
        $resultPage->getConfig()->getTitle()->prepend(__('I95Dev Item Discount Groups'));
        $resultPage->addBreadcrumb(__('I95Dev Discount Groups'), __('I95Dev Item Discount Groups'));
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock('I95DevConnect\DiscountGroups\Block\Adminhtml\Itemdiscountgroups')
        );
        return $resultPage;
    }
}
