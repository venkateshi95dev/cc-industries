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

use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Recurring Print Action class
 *
 * Class PrintAction
 */
class PrintAction extends Action implements HttpGetActionInterface
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_print';

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * PrintAction constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerFactory $customerFactory
    ) {
        parent::__construct($context);

        /** @var  resultJsonFactory */
        $this->resultJsonFactory = $resultJsonFactory;
        /** @var customerFactory */
        $this->customerFactory = $customerFactory;
    }

    /**
     * Execute Method
     *
     * @return int|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var $transactionParams */
        $transactionParams = $this->_request->getParams();

        /** @var $printResponse */
        $printResponse = $this->customerFactory->create()->printEmailTemplate($transactionParams);

        /** @var $htmlData */
        $htmlData = '';

        /** print response */
        if ($printResponse['error'] === false) {
            $htmlData = $printResponse['email_html'];
        }else{
            $this->messageManager->addErrorMessage($printResponse['message']);
        }

        return $this->resultJsonFactory->create()->setData(
            ['html_data' => $htmlData]
        );
    }
}
