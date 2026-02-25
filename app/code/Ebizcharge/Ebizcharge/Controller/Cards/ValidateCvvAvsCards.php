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

namespace Ebizcharge\Ebizcharge\Controller\Cards;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Exception;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Controller\Address;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Helper\Data as HelperData;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Payment\Model\Config;

/**
 * To Validate Cvv Avs Cards Action class
 *
 * Class ValidateCvvAvsCards
 */
class ValidateCvvAvsCards extends Address
{
    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $_jsonFactory;

    /**
     * SaveAction constructor.
     *
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param FormFactory $formFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param DataObjectProcessor $dataProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param RegionFactory $regionFactory
     * @param HelperData $helperData
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $paymentconfig
     * @param CustomerFactory $customerFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param TranApi $tranApi
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        FormFactory $formFactory,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        DataObjectProcessor $dataProcessor,
        DataObjectHelper $dataObjectHelper,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        RegionFactory $regionFactory,
        HelperData $helperData,
        ScopeConfigInterface $scopeConfig,
        Config $paymentconfig,
        CustomerFactory $customerFactory,
        EbizchargeLogger $ebizchargeLogger,
        TranApi $tranApi,
        JsonFactory $jsonFactory
    ) {
        /**
         * Parent construct
         */
        parent::__construct(
            $context,
            $customerSession,
            $formKeyValidator,
            $formFactory,
            $addressRepository,
            $addressDataFactory,
            $regionDataFactory,
            $dataProcessor,
            $dataObjectHelper,
            $resultForwardFactory,
            $resultPageFactory
        );

        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var  _jsonFactory */
        $this->_jsonFactory = $jsonFactory;
    }

    /**
     * @return mixed
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
            'message' => "Error occurred during adding payment method, your card is not valid.",
            'response' => [
                'avs' => [],
                'cvv' => []
            ]
        ];

        try {
            /** @var $customerId */
            $customerId = $this->getRequest()->getParam('customer_id');
            $ebizCustomerId = isset($this->getRequest()->getParam('payment')['ebzc_cust_id']) ?
                $this->getRequest()->getParam('payment')['ebzc_cust_id'] : "";

            if ($ebizCustomerId) {
                $customer = $this->_customerFactory->create()->loadByEbizCustomerId($ebizCustomerId);
            } else {
                $customer = $this->_customerFactory->create()->load($customerId);
            }

            $ebizCustomerInternalId = $customer->getEcCustInternalId();
            $ebizCustomerToken = $customer->getEcCustToken();
            $ebizCustomerId = $customer->getEcCustId();

            /** @var $paymentMethodParams */
            $paymentMethodParams = $this->getRequest()->getParams();
            $paymentMethodParams['ebiz_customer_internal_id'] = $ebizCustomerInternalId;
            $paymentMethodParams['ebiz_customer_token'] = $ebizCustomerToken;
            $paymentMethodParams['ebiz_customer_id'] = $ebizCustomerId;
            //$paymentMethodParams['is_ajax'] = false;

            $paymentMethodParams['payment']['avs_zip'] = $this->getRequest()->getParam("payment")["cc_avs_zip"] ?? '';
            $paymentMethodParams['payment']['avs_street'] = $this->getRequest()->getParam("payment")["cc_avs_street"]
                ?? '';
            $paymentMethodParams['payment']['cc_owner'] = $this->getRequest()->getParam("payment")["cc_holder"] ?? '';

            if (isset($paymentMethodParams['customer_account'])) {
                $paymentMethodParams['payment']['customer_account'] = $this->getRequest()->getParam("customer_account");
                $paymentMethodParams['payment']['cc_owner'] = $this->getRequest()->getParam("cc_holder") ??
                    $paymentMethodParams['payment']['cc_owner'];
                $paymentMethodParams['payment']['avs_zip'] = $this->getRequest()->getParam("postcode");
                $paymentMethodParams['payment']['avs_street'] = $this->getRequest()->getParam("street")[0];
            }

            /** @var  $paymentMethodResponse */
            $validatorResponse = $this->_customerFactory->create()
                ->addNewPaymentMethod($customer->getId(), $paymentMethodParams);

        } catch (\Exception $exception) {
            $this->_ebizchargeLogger->addCritical(
                __("Error occurred during checking card validations. Error: " . $exception->getMessage())
            );
            $validatorResponse["message"] = __(
                "Error: " . $exception->getMessage() . "."
            );
        }

        if (isset($paymentMethodParams['is_ajax']) && $paymentMethodParams['is_ajax']) {
            return $resultJson->setData(
                [
                    'resp_data' => $validatorResponse
                ]
            );
        }

        if ($validatorResponse['error'] === false) {
            $this->messageManager->addSuccessMessage($validatorResponse['message']);
            return $this->resultRedirectFactory->create()->setPath('*/*/listaction');
        } else {
            $this->messageManager->addErrorMessage("Payment authentication error: " .
                $validatorResponse['message']);
            return $this->resultRedirectFactory->create()->setPath('*/*/addaction');
        }
    }
}
