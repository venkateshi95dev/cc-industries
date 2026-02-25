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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\ACH;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Add ACH Bank Account
 *
 * Class Add
 */
class Add extends Action implements HttpGetActionInterface
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_ach_add';

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * Main constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        /** @var  resultPageFactory */
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute Method
     *
     * @return Page
     */
    public function execute()
    {
        $pageTitle = __('Add New Bank Account');

        $resultPage = $this->resultPageFactory->create();

        if ($this->_request->getParam('action') == 'edit') {
            // phpcs:ignore
            $pageTitle = 'Update Bank Account';
        }
        $resultPage->getConfig()->getTitle()->prepend(__($pageTitle));

        return $resultPage;
    }
}
