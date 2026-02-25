<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Controller\Adminhtml\AbstractAction;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Edit the recurring from admin side
 *
 * Class EditAction
 */
class EditAction extends AbstractAction implements HttpGetActionInterface
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_edit';

    /**
     * Page Title var
     *
     * @var string
     */
    protected  $pageTitle = 'Manage Subscription Payment History';

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * Main Constructor of the class
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        EbizchargeLogger $ebizchargeLogger
    ) {
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Execute
     *
     * Load the page defined in
     * view/adminhtml/layout/ebizcharge_ebizcharge_recurrings_editaction.xml
     *
     * @return Redirect|Page
     */
    public function execute()
    {
        $mid = $this->getRequest()->getParam('mid');

        if ($mid) {
            return $this->_init($this->resultPageFactory->create());
        } else {
            /** logging to the logger */
            $this->ebizchargeLogger->addError(__("Unable to update recurrings Payment"));
            $this->messageManager->addErrorMessage(__('Unable to update recurrings payment.'));
        }
        return $this->resultRedirectFactory->create()->setPath('*/recurrings/');
    }
}
