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

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

/**
 * Get response from Ebizcharge Gateway and send email
 *
 * Class EmailAction
 */
class EmailAction extends Action implements HttpGetActionInterface
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_email';

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * EmailAction constructor.
     *
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($context);

        /** @var  ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Execute Method
     *
     * @return ResponseInterface|Redirect|ResultInterface|void
     */
    public function execute()
    {
        if ($this->_request->getParam('tid') !== null) {

            $params = $this->_request->getParams();

            $emailReceiptResp = $this->_customerFactory->create()->sendCustomerReceipt($params);

            if ($emailReceiptResp['error'] == false) {

                $this->_ebizchargeLogger->addInfo(__(
                    "Success, Email has been sent to \"" . $params['email'] . "\" successfully"
                ));
                $this->messageManager->addSuccessMessage(__(
                    'Success, Email has been sent to "' . $params['email'] . '" successfully!'
                ));
            } else {
                $this->_ebizchargeLogger->addError(__(
                    "Oops error occurred during sending email to \"" . $params['email'] . "\" "
                ));
                $this->messageManager->addErrorMessage(__(
                    'Oops Error occurred during sending email to "' . $params['email'] . '" '
                ));
            }

            /** returning back the response */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/history');

            return $resultRedirect;
        }
    }
}
