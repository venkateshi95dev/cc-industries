<?php
declare(strict_types=1);

namespace Crimson\CustomReports\Controller\Adminhtml\Report\Abandoned;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Reports::report_marketing';

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        protected PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Crimson_CustomReports::report_abandoned');
        $resultPage->getConfig()->getTitle()->prepend(__('Abandoned Carts by SKU'));

        return $resultPage;
    }
}
