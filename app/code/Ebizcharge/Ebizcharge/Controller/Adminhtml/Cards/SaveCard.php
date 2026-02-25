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
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Payment\Model\Config as PaymentConfig;

/**
 * Save/delete customer payment card
 *
 * Class SaveCard
 */
class SaveCard extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_cards_save';

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var PaymentConfig
     */
    protected PaymentConfig $paymentConfig;

    /**
     * @var RedirectFactory
     */
    protected RedirectFactory $redirectFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;

    /**
     * SaveCard constructor.
     *
     * @param RedirectFactory $redirectFactory
     * @param Manager $messageManager
     * @param PaymentConfig $paymentConfig
     * @param CustomerFactory $customerFactory
     * @param RequestInterface $request
     * @param Context $context
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        Manager $messageManager,
        PaymentConfig $paymentConfig,
        CustomerFactory $customerFactory,
        RequestInterface $request,
        Context $context,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($context);

        /** @var  request */
        $this->request = $request;
        /** @var  paymentConfig */
        $this->paymentConfig = $paymentConfig;
        /** @var  messageManager */
        $this->messageManager = $messageManager;
        /** @var  redirectFactory */
        $this->redirectFactory = $redirectFactory;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var customerFactory */
        $this->customerFactory = $customerFactory;
    }

    /**
     * Execute Method
     *
     * @return Redirect|ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var $customerId */
        $customerId = $this->request->getParam('customer_id');
        $customer = $this->customerFactory->create()->load($customerId);
        $requestParams = $this->request->getParams();

        $ebizCustomerInternalId = $customer->getEcCustInternalId();
        $ebizCustomerToken = $customer->getEcCustToken();
        $ebizCustomerId = $customer->getEcCustId();

        /** @var $pageAction */
        $pageAction = $this->request->getParam('action');
        $pageAction = isset($requestParams['save_card_anyway']) && (int)$requestParams['save_card_anyway']=== 1 ?  "save" : $pageAction;
        if(!$pageAction && isset($requestParams["call_for"]) && !empty($requestParams["call_for"])){
            $pageAction = "save";
        }

        if($pageAction !== null) {
            /** checking page action */
            switch ($pageAction) {
                case 'del':
                    /** @var $customerToken */
                    $paymentMethodId = $this->request->getParam('mid');

                    /** deleting customer card */
                    $paymentMethodResults = $this->customerFactory->create()
                        ->deleteCustomerPaymentMethod($customerId, $paymentMethodId);

                    if ($paymentMethodResults['error'] === false) {
                        $this->messageManager->addSuccessMessage(__(
                            'The payment method was successfully removed.'
                        ));
                    } else {
                        $errorMessage = $paymentMethodResults['message'];

                        if ($paymentMethodResults['error_code'] === 502) {
                            // $this->messageManager->addErrorMessage($errorMessage);
                            $this->messageManager->addErrorMessage(__("Error occurred during removing your payment method, please contact support."));
                        } else {
                            $this->messageManager->addErrorMessage(__("Error occurred during removing your payment method, please contact support."));
                        }
                    }
                    return $this->redirectFactory->create()->setPath('customer/index/edit/', ['id' => $customerId]);
                //break;

                case 'edit':
                    $paymentMethodId = $this->request->getParam('mid');
                    $paymentCardParams = $this->getRequest()->getParam('payment');

                    $paymentCardParams['ebiz_customer_id'] = $ebizCustomerId;
                    $paymentCardParams['ebiz_customer_token'] = $ebizCustomerToken;
                    $paymentCardParams['ebiz_customer_internal_id'] = $ebizCustomerInternalId;
                    $paymentCardParams['payment_method_id'] = $paymentCardParams['method_id'] ?? '';
                    $paymentCardParams['cc_holder'] = $paymentCardParams['cc_owner'] ?? '';
                    $paymentCardParams['customer_id'] = $customerId;
                    $paymentCardParams['method_name'] = $paymentCardParams['method_name'] ?? '';
                    $paymentCardParams['payment'] = $paymentCardParams;

                    /** @var  $cardResults */
                    $paymentMethodResults = $this->customerFactory->create()
                        ->updateCustomerPaymentMethod($customerId, $paymentCardParams);

                    if ($paymentMethodResults['error'] === false) {
                        $this->messageManager->addSuccessMessage(__("The payment method was successfully updated."));
                    } else {
                        $this->messageManager->addErrorMessage(__("Error occurred during updating your payment method, please contact support."));
                    }

                    break;
                default:
                    /** @var  $paymentCardParams */
                    $paymentCardParams = $this->getRequest()->getParam('payment');
                    $paymentCardParams['default'] = $paymentCardParams['is_default'] ?? 0;
                    $paymentCardParams['street'] = [$paymentCardParams['avs_street'] ?? ''];
                    $paymentCardParams['cc_holder'] = $paymentCardParams['cc_owner'] ?? '*';

                    /** @var  $paymentParams */
                    $paymentParams = [
                        'payment' => $paymentCardParams,
                        'street' => $paymentCardParams['street'],
                        'postcode' => isset($paymentCardParams['avs_zip']) ? $paymentCardParams['avs_zip'] : '',
                        'ebiz_customer_internal_id' => $ebizCustomerInternalId,
                        'ebiz_customer_token' => $ebizCustomerToken,
                        'ebiz_customer_id' => $ebizCustomerId,
                        'envoirnment_type' => 'admin',
                        'customer_id' => $customerId,
                        'save_card_anyway' => $this->getRequest()->getParam('save_card_anyway') ? 1 : 0
                    ];

                    /** @var  $paymentMethodResults */
                    $paymentMethodResults = $this->customerFactory->create()
                        ->addNewPaymentMethod($customerId, $paymentParams);

                    /** if results and payment method added */
                    if ($paymentMethodResults['error'] === false) {
                        $this->messageManager->addSuccessMessage(__(
                            'The payment method was successfully added.'
                        ));
                    } else {
                        $this->messageManager->addErrorMessage(__(
                            'Error occurred during removing your payment method, please contact support.'
                        ));
                    }
                    break;

            }
        }

        return $this->redirectFactory->create()->setPath('customer/index/edit/', ['id' => $customerId]);
    }
}
