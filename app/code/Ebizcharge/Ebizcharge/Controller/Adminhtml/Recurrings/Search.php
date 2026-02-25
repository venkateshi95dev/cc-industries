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
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Recurring Search Action class
 *
 * Class Search
 */
class Search extends AbstractAction implements HttpGetActionInterface
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_search';

    /**
     * Page Title var
     *
     * @var string
     */
    protected static $_pageTitle = 'Upcoming Subscription Orders';

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * Main Constructor of the Controller
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
        parent::__construct($context, $resultPageFactory);
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;

    }

    /**
     * Load the page defined in view/adminhtml/layout/ebizcharge_ebizcharge_recurrings_search.xml
     *
     * @return Page
     */
    public function execute(): Page
    {
        /** Structure to add logger in the code
         * That is a temp code, please remove once clear to added Loggers
         */
        $request = $this->getRequest();
        $params = [
            'module' => $this->getRequest()->getModuleName(),
            'action' => $this->getRequest()->getActionName()
        ];
        $params = json_encode($params);

        $this->_ebizchargeLogger->addInfo(__('current visited: ' . $params));
        /** temp code to remove */

        return $this->_init($this->resultPageFactory->create());
    }
}
