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
use Ebizcharge\Ebizcharge\Model\TranApi;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Recurring Load Customer action
 *
 * Class LoadCustomerAction
 */
class LoadCustomerAction extends Action implements HttpPostActionInterface
{
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
     * @var TranApi
     */
    protected TranApi $_soapApiModel;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * LoadCustomerAction constructor.
     *
     * @param Context $context
     * @param TranApi $soapApiModel
     * @param CustomerFactory $customerFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context $context,
        TranApi $soapApiModel,
        CustomerFactory $customerFactory,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($context);

        /** @var  soapApiModel */
        $this->_soapApiModel = $soapApiModel;
        /** @var  ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Execute Function
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $requestParams = $this->getRequest()->getParams();
        $customerId = $requestParams['customer_id'] ?? '';
        $methodType = $requestParams['ebizs_option'] ?? '';
        /** @var  $customerPaymentMethods */
        $customerPaymentMethods = $this->_customerFactory->create()
            ->getEbizCustomerPaymentMethods($customerId);
        $output = '';

        if (count($customerPaymentMethods) > 0) {
            try {
                foreach ($customerPaymentMethods as $paymentMethod) {
                    $paymentMethodName = $paymentMethod->MethodName;
                    $paymentMethodJson = json_decode($paymentMethodName);

                    if (is_object($paymentMethodJson)) {
                        $paymentMethodName = $paymentMethodJson->a."-".$paymentMethodJson->b;
                    }
                    if (!empty($methodType) && $paymentMethod->MethodType === $methodType) {
                        $output .= "<option value='" . $paymentMethod->MethodID . "||".$paymentMethodName."'>" .
                            $paymentMethodName . "</option>";
                    } elseif (empty($methodType)) {
                        $output .= "<option value='" . $paymentMethod->MethodID .  "||".$paymentMethodName."'>" .
                            $paymentMethodName . "</option>";
                    }

                }
                return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['html_data' => $output]);
            } catch (Exception $ex) {
                $this->_ebizchargeLogger->addCritical(__("No payment method found " . $ex->getMessage()));
                $output = "<option value=''>No payment method found</option>";
                return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['html_data' => $output]);
            }
        } else {
            $this->_ebizchargeLogger->addError(__("Invalid Customer Id"));

            $output = "<option value=''>No payment method found</option>";
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['html_data' => $output]);
        }
    }
}
