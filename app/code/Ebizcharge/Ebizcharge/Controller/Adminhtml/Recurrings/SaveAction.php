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
use Ebizcharge\Ebizcharge\Model\FutureSubscription;
use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote as AdminQuoteSession;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Model\Config as PaymentConfig;

/**
 * Recurring Save Action class
 *
 * Class SaveAction
 */
class SaveAction extends Action implements HttpPostActionInterface
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_save';

    /**
     * Response Return URL
     *
     * @const: RESPONSE_RETURN_URL
     */
    public const RESPONSE_RETURN_URL = 'ebizcharge_ebizcharge/recurrings';

    /**
     * @const: RESPONSE_ADD_SUBSCRIPTION_URL
     */
    public const RESPONSE_ADD_SUBSCRIPTION_URL = 'ebizcharge_ebizcharge/recurrings/addaction/key';

    /**
     * Wrong Request
     *
     * @const WRONG_REQUEST
     */
    public const WRONG_REQUEST = 1;

    /**
     * Wrong Token
     *
     * @const WRONG_TOKEN
     */
    public const WRONG_TOKEN = 2;

    /**
     * Action Exception
     *
     * @const ACTION_EXCEPTION
     */
    public const ACTION_EXCEPTION = 3;

    /**
     * @var PaymentConfig
     */
    protected PaymentConfig $paymentConfig;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $productFactory;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;

    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $recurringFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var FutureSubscription
     */
    protected FutureSubscription $futureSubscription;

    /**
     * @var AdminQuoteSession
     */
    protected AdminQuoteSession $adminQuoteSession;


    /**
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param CustomerFactory $customerFactory
     * @param FutureSubscription $futureSubscription
     * @param RecurringFactory $recurringFactory
     * @param PaymentConfig $paymentConfig
     * @param AdminQuoteSession $adminQuoteSession
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context            $context,
        ProductFactory     $productFactory,
        CustomerFactory    $customerFactory,
        FutureSubscription $futureSubscription,
        RecurringFactory   $recurringFactory,
        PaymentConfig      $paymentConfig,
        AdminQuoteSession  $adminQuoteSession,
        EbizchargeLogger   $ebizchargeLogger
    )
    {
        parent::__construct($context);

        /** @var  productFactory */
        $this->productFactory = $productFactory;
        /** @var customerFactory */
        $this->customerFactory = $customerFactory;
        /** @var  paymentConfig */
        $this->paymentConfig = $paymentConfig;
        /** @var recurringFactory */
        $this->recurringFactory = $recurringFactory;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var  futureSubscription */
        $this->futureSubscription = $futureSubscription;
        /** @var  adminQuoteSession */
        $this->adminQuoteSession = $adminQuoteSession;
    }

    /**
     * Execute Method
     *
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        /** @var  $recurringOrderParams */
        $recurringOrderParams = $this->getRequest()->getParams();
        /** @var $recurringId */
        $recurringId = $this->getRequest()->getParams('recurring_id');
        $key = $recurringOrderParams["key"];
        $currentBillingAddressId = isset($recurringOrderParams["current_billing_method"]) && !empty($recurringOrderParams["current_billing_method"]) ? $recurringOrderParams["current_billing_method"]: "";
        $currentShippingAddressId = isset($recurringOrderParams["current_shipping_method"]) && !empty($recurringOrderParams["current_shipping_method"]) ? $recurringOrderParams["current_shipping_method"]: "";

        $recurringOrderParams["addresBill"] = isset($recurringOrderParams["addressBill"]) && !empty($recurringOrderParams["addressBill"]) ? $recurringOrderParams["addressBill"]: $currentBillingAddressId;
        $recurringOrderParams["addressShip"] = isset($recurringOrderParams["addressShip"]) && !empty($recurringOrderParams["addressShip"]) ? $recurringOrderParams["addressShip"]: $currentShippingAddressId;
        $paymentOption = isset($recurringOrderParams["payment"]["ebzc_option"]) ? $recurringOrderParams["payment"]["ebzc_option"] : "";

        if($paymentOption === "new"){
            $recurringOrderParams["payment"]["method_id"] = "";
            $recurringOrderParams["payment"]["payment_method_name"] = "";
            $recurringOrderParams["payment_method_name"] = "";
        }

      //  dump($recurringOrderParams);exit;
        /** @var $recurringOrderResponse */
        $recurringOrderResponse = $this->recurringFactory->create()->addRecurringOrder($recurringOrderParams);

        $recurringMessage = $recurringOrderResponse["message"] ??
            __("Error occurred during adding recurring.");

        $this->ebizchargeLogger->addInfo($recurringOrderResponse['message']);

        if ($recurringOrderResponse['error'] === false) {
            $this->adminQuoteSession->unsQuoteId();
            $this->adminQuoteSession->unsQuote();
            $this->messageManager->addSuccessMessage($recurringMessage);
            return $this->_redirect(self::RESPONSE_RETURN_URL);

        } else {

            $this->messageManager->addErrorMessage($recurringMessage);
            return $this->_redirect(self::RESPONSE_ADD_SUBSCRIPTION_URL . "/" . $key);
        }

    }
}
