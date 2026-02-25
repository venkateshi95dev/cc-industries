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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Abstract Action class for Admin
 *
 * Class AbstractAction
 */
abstract class AbstractAction extends Action
{
    /**
     * ACL resource for access management
     *
     * @const ACL_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::rec';

    /**
     * Active menu id
     *
     * @const MENU_ID
     */
    public const MENU_ID = 'Ebizcharge_Ebizcharge::rec';

    /**
     * Page Title var
     *
     * @var string
     */
    protected $pageTitle = 'Manage Subscriptions';

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * Main Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory
    ) {
        /** @var  resultPageFactory */
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    /**
     * Initializing breadcrumbs
     *
     * @param mixed $resultPage
     * @return mixed
     */
    public function _init($resultPage)
    {
        $resultPage->setActiveMenu(static::MENU_ID);
        $resultPage->addBreadcrumb(__('Subscriptions'), __('Subscriptions'));
        $resultPage->addBreadcrumb(__('Manage Subscriptions'), __('Manage Subscriptions'));
        $resultPage->getConfig()->getTitle()->prepend(__($this->pageTitle));

        return $resultPage;
    }
}
