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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Cards;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Validate Credit Cards action
 *
 * Class ValidateCvvAvsCards
 */
class ValidateCvvAvsCards extends Action
{
    /**
     * @var PageFactory
     */
    protected PageFactory $_pageFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $_jsonFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var Validator
     */
    protected $_formKeyValidator;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $_messageManager;

    /**
     * ValidateCvvAvsCards constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param JsonFactory $jsonFactory
     * @param Validator $formKeyValidator
     * @param EbizchargeLogger $ebizchargeLogger
     * @param CustomerFactory $customerFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context          $context,
        PageFactory      $pageFactory,
        JsonFactory      $jsonFactory,
        Validator        $formKeyValidator,
        EbizchargeLogger $ebizchargeLogger,
        CustomerFactory  $customerFactory,
        ManagerInterface $messageManager
    )
    {
        /** @var  _pageFactory */
        $this->_pageFactory = $pageFactory;
        /** @var  _jsonFactory */
        $this->_jsonFactory = $jsonFactory;
        /** @var  _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var  _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var  _formKeyValidator */
        $this->_formKeyValidator = $formKeyValidator;
        /** @var  _messageManager */
        $this->_messageManager = $messageManager;

        parent::__construct($context);
    }

    /**
     * Execute JSON Response
     *
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var  $resultJson */
        $resultJson = $this->_jsonFactory->create();

        /** @var  $jsonResponse */
        $validatorResponse = [
            'error' => true,
            'status' => false,
            'action' => 'new',
            'valid' => false,
            'cvv_code' => "",
            'avs_code' => "",
            'cvv_avs_warnings' => true,
            'result_code' => "D",
            'message' => "Card is not valid",
            'response' => [
                'avs' => [],
                'cvv' => []
            ]
        ];

        if (!$this->_formKeyValidator->validate($this->getRequest())) {

            $validatorResponse['message'] = __("Error the form is not valid");
            return $resultJson->setData(
                [
                    'resp_data' => $validatorResponse
                ]
            );
        }

        /**
         * customer ID validating Response
         */
        try {

            $paymentMethodParams = $this->getRequest()->getParams();
            $customerId = $this->getRequest()->getParam('customer_id');


            if ($customerId) {
                /** @var $customerId */
                $customer = $this->_customerFactory->create()->load($customerId);
            } else {
                $customerEmail = $paymentMethodParams['order']['account']['email'] ?? '';
                $customer = $this->_customerFactory->create()->loadByEmail($customerEmail);
                $paymentMethodParams['payment']['avs_zip'] = $paymentMethodParams['payment']['ebzc_avs_zip'] ?? "";
                $paymentMethodParams['payment']['avs_street'] = $paymentMethodParams['payment']['ebzc_avs_street'] ?? "";
            }

            if (!$customer->getId()) {
                $validatorResponse['message'] = __("Error selected customer do not exists.");
                return $resultJson->setData(
                    [
                        'resp_data' => $validatorResponse
                    ]
                );
            }

            $ebizCustomerInternalId = $customer->getEcCustInternalId();
            $ebizCustomerToken = $customer->getEcCustToken();
            $ebizCustomerId = $customer->getEcCustId();

            /** @var $paymentMethodParams */
            $paymentMethodParams['customer_id'] = $customer->getId() ?? "";
            $paymentMethodParams['ebiz_customer_internal_id'] = $ebizCustomerInternalId;
            $paymentMethodParams['ebiz_customer_token'] = $ebizCustomerToken;
            $paymentMethodParams['ebiz_customer_id'] = $ebizCustomerId;
            $paymentMethodParams['is_ajax'] = true;


            if (!isset($paymentMethodParams["ebzc_new_payment_method"]) &&
                isset($paymentMethodParams["payment_method_id_used"])) {
                $validatorResponse['result_code'] = "A";
                $validatorResponse['status'] = true;
                $validatorResponse['error'] = false;
                $validatorResponse['message'] = __("Already have payment method ID");
                return $resultJson->setData(
                    [
                        'resp_data' => $validatorResponse
                    ]
                );
            }

            /** @var  $paymentMethodResponse */
            $validatorResponse = $this->_customerFactory->create()
                ->addNewPaymentMethod($customerId, $paymentMethodParams);


            if (isset($validatorResponse['result_code']) && $validatorResponse['result_code'] === 'D') {
                $validatorResponse['message'] = __('AVS warnings response: ') . $validatorResponse['message'] . '. ' .
                    __('Please update the entered information or try a different card.');
                $validatorResponse["avs_code"] = isset($validatorResponse["response"]["avs"]) ? $validatorResponse["response"]["avs"] : "";
                $validatorResponse["cvv_code"] = isset($validatorResponse["response"]["cvv"]) ? $validatorResponse["response"]["cvv"] : "";
                $validatorResponse["error"] = false;
                $validatorResponse["status"] = true;

                $callFor = $paymentMethodParams['call_for'] ?? '';
                if ($callFor === 'adminCheckout' || $callFor === 'adminCustomer') {
                    $this->_messageManager->addErrorMessage($validatorResponse['message']);
                }
            }
            if (isset($validatorResponse['result_code']) && $validatorResponse['result_code'] === 'E') {
                $validatorResponse['message'] = __('AVS declined response: ') . $validatorResponse['message'] . '. ' .
                    __('Please update the entered information or try a different card.');
                $validatorResponse["avs_code"] = isset($validatorResponse["response"]["avs"]) ? $validatorResponse["response"]["avs"] : "";
                $validatorResponse["cvv_code"] = isset($validatorResponse["response"]["cvv"]) ? $validatorResponse["response"]["cvv"] : "";
                $validatorResponse["error"] = true;
                $validatorResponse["status"] = false;

                $callFor = $paymentMethodParams['call_for'] ?? '';
                if ($callFor === 'adminCheckout' || $callFor === 'adminCustomer') {
                    $this->_messageManager->addErrorMessage($validatorResponse['message']);
                }
            }

        } catch (\Exception $exception) {
            //  var_dump($exception->getMessage());exit;
            $validatorResponse["error"] = true;
            $validatorResponse["status"] = false;

            $this->_ebizchargeLogger->addCritical(__("Error occurred during checking card validations Error: " .
                $exception->getMessage()));
            $validatorResponse["message"] = __("Error occurred during adding payment method. Error: " . $exception->getMessage() . ".");
        }

        return $resultJson->setData(
            [
                'resp_data' => $validatorResponse
            ]
        );
    }
}
