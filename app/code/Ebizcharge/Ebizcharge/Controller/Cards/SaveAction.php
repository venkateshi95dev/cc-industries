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
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\View\Result\PageFactory;
use Magento\Payment\Model\Config;

/**
 * Save Credit Card Action
 *
 * Class SaveAction
 */
class SaveAction extends Address
{
    /**
     * @var RegionFactory
     */
    protected RegionFactory $regionFactory;

    /**
     * @var HelperData
     */
    protected HelperData $helperData;

    /**
     * @var TranApi
     */
    protected TranApi $_tran;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $_scopeConfig;

    /**
     * @var Config
     */
    protected Config $_paymentconfig;

    /**
     * Default Method
     *
     * @var $_isDefaultMethod
     */
    protected $_isDefaultMethod;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

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
        TranApi $tranApi
    ) {
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
        /** @var  regionFactory */
        $this->regionFactory = $regionFactory;
        /** @var  helperData */
        $this->helperData = $helperData;
        /** @var  _tran */
        $this->_tran = $tranApi;
        /** @var  _scopeConfig */
        $this->_scopeConfig = $scopeConfig;
        /** @var  _paymentconfig */
        $this->_paymentconfig = $paymentconfig;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Execute Method
     *
     * Adds the new payment method to the customer's account, and
     * redirects the user to the "Manage My Payment Methods" page.
     *
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var $redirectUrl */
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/listaction');
        }

        try {
            /** @var $customerEmail */
            $customerEmail = $this->_customerSession->getCustomer()->getEmail();

            /** @var $customer */
            $customer = $this->_customerFactory->create()->loadByEmail($customerEmail);

            /** add customer if  not exists at Ebizcharge */
            if (!$customer->getEcCustInternalId() && !$customer->getEcCustToken()) {
                $customerId = $customer->getEntityId();
                $ebizCustomer = $this->_customerFactory->create()->getEbizCustomerById($customerId);

                if ($ebizCustomer->CustomerId) {
                    /** save Ebizcharge Fileds */
                    $customerFactory = $this->_customerFactory->create()->load($customerId)
                        ->setEcCustId($ebizCustomer->CustomerId)
                        ->save();
                } else {
                    /** @var  $ebizCustomerResponse */
                    $ebizCustomerResponse = $this->_customerFactory->create()->addCustomerToEbizcharge($customer);

                    if ($ebizCustomerResponse['status'] == 'Success') {
                        $ebizCustomerId = $ebizCustomerResponse['ebiz_customer_id'];
                        $ebizCustomer = $this->_customerFactory->create()->getEbizCustomerById($ebizCustomerId);

                        // phpcs:ignore
                        if (@$ebizCustomer->CustomerId) {
                            /** save Ebizcharge Fileds */
                            $this->_customerFactory->create()->saveEbizchargeFields($ebizCustomer);

                        } else {
                            $this->messageManager->addErrorMessage(__(
                                'Something wrong happened during interacting with EBizCharge Gateway '
                            ));
                            $this->_ebizchargeLogger->addError(__(
                                'Error occurred during saving customer to EBizCharge Gateway'
                            ));
                            return $this->resultRedirectFactory->create()->setPath('*/*/addaction');
                        }
                    } else {
                        $this->messageManager->addErrorMessage(__(
                            'Something wrong happened during interacting with EBizCharge Gateway '
                        ));
                        $this->_ebizchargeLogger->addError(__(
                            'Error occurred during saving customer to EBizCharge Gateway'
                        ));
                        return $this->resultRedirectFactory->create()->setPath('*/*/addaction');
                    }
                }
            }
            /** @var  $customerEbizInternalId */
            $customerEbizInternalId = $customer->getEcCustInternalId();
            /** @var  $customerEbizToken */
            $customerEbizToken = $customer->getEcCustToken();

            /** @var $paymentMethodParams */
            $paymentMethodParams = $this->getRequest()->getParams();
            $paymentMethodParams['ebiz_customer_internal_id'] = $customerEbizInternalId;
            $paymentMethodParams['ebiz_customer_token'] = $customerEbizToken;
            if ($this->_customerSession->getPciAddNewMethodResponse()) {
                $paymentMethodResponse = $this->_customerSession->getPciAddNewMethodResponse();
                $this->_customerSession->unsPciAddNewMethodResponse();
            } else {
                $paymentMethodResponse = $this->_customerFactory->create()
                    ->addNewPaymentMethod($customer->getId(), $paymentMethodParams);
            }

            /** payment method response */
            if ($paymentMethodResponse['error'] === false) {
                $this->messageManager->addSuccessMessage($paymentMethodResponse['message']);
                return $this->resultRedirectFactory->create()->setPath('*/*/listaction');
            } else {
                $this->messageManager->addErrorMessage($paymentMethodResponse['message']);
                return $this->resultRedirectFactory->create()->setPath('*/*/addaction');
            }

        } catch (Exception $exception) {
            $this->_ebizchargeLogger->addCritical(__(
                ' Exception occurred during adding Payment Method with EBizCharge Gateway Error: ' .
                $exception->getMessage()
            ));
            $this->messageManager->addErrorMessage(__('Error: '.$exception->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('*/*/addaction');
        }
    }
}
