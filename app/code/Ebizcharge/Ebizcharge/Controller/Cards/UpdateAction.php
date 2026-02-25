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
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Model\Config;

/**
 * Updates the details of the edited payment method.
 *
 * Class UpdateAction
 */
class UpdateAction implements AccountInterface, HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var TranApi
     */
    protected TranApi $_tranApi;

    /**
     * @var FormKeyValidator
     */
    protected FormKeyValidator $_formKeyValidator;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $_request;

    /**
     * @var RedirectFactory
     */
    protected RedirectFactory $_redirectFactory;

    /**
     * @var Config
     */
    protected Config $_paymentConfig;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $_messageManager;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * UpdateAction constructor.
     *
     * @param Config $paymentConfig
     * @param FormKeyValidator $formKeyValidator
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param CustomerFactory $customerFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param TranApi $tranApi
     */
    public function __construct(
        Config $paymentConfig,
        FormKeyValidator $formKeyValidator,
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        CustomerFactory $customerFactory,
        EbizchargeLogger $ebizchargeLogger,
        TranApi $tranApi
    ) {
        /** @var  paymentConfig */
        $this->_paymentConfig = $paymentConfig;
        /** @var  formKeyValidator */
        $this->_formKeyValidator = $formKeyValidator;
        /** @var  messageManager */
        $this->_messageManager = $messageManager;
        /** @var  redirectFactory */
        $this->_redirectFactory = $redirectFactory;
        /** @var  request */
        $this->_request = $request;
        /** @var  tranApi */
        $this->_tranApi = $tranApi;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Execute Method
     *
     * Saves the updated payment information
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->_request)) {
             return $this->_redirectFactory->create()->setPath('*/*/listaction');
        }

        $customerId = $this->_request->getParam('cid');
        $customer  = $this->_customerFactory->create()->load($customerId);

        $paymentMethodId = $this->_request->getParam('mid');
        $customerToken = $customer->getEcCustToken();
        $isDefault = $this->_request->getParam('default');

        /** CId or MID given */
        if ($customerId && $paymentMethodId) {

            try {
                /** @var $paymentMethodParams */
                $paymentMethodParams = [
                    'cc_exp_month' => $this->_request->getParam('cc_exp_month'),
                    'cc_exp_year' => $this->_request->getParam('cc_exp_year'),
                    'avs_street' => $this->_request->getParam('avs_street'),
                    'avs_zip' => $this->_request->getParam('avs_zip'),
                    'cc_type' => $this->_request->getParam('method_type'),
                    'cc_holder' => $this->_request->getParam('cc_holder'),
                    'method_name' => $this->_request->getParam('method_name'),
                    'card_number' => $this->_request->getParam('card_number'),
                    'created_at' => $this->_request->getParam('created_at'),
                    'modified_at' => $this->_request->getParam('modified_at'),
                    'is_update'  => $this->_request->getParam('is_update'),
                    'payment_method_id' => $paymentMethodId,
                    'ebiz_customer_token' => $customerToken,
                    'customer_id' => $customerId,
                    'is_default' => $isDefault
                ];
                /** @var  $paymentMethodUpdated */
                $paymentMethodUpdatedResponse = $this->_customerFactory->create()
                    ->updateCustomerPaymentMethod($customerId, $paymentMethodParams);

                if ($paymentMethodUpdatedResponse['error'] == false) {
                    $successMessage = $paymentMethodUpdatedResponse['message'];
                    $this->_messageManager->addSuccessMessage($successMessage);
                    $this->_ebizchargeLogger->addInfo($successMessage);

                    return $this->_redirectFactory->create()->setPath('*/*/listaction', ['_secure' => true]);
                } else {
                    $this->_messageManager->addErrorMessage(__(
                        'Unable to update card with Gateway. ' . $paymentMethodUpdatedResponse['message']
                    ));
                    $this->_ebizchargeLogger->addError(__(
                        'Unable to update payment method with Ebizcharge Gateway. ' .
                        $paymentMethodUpdatedResponse['message']
                    ));
                }
            } catch (Exception $ex) {
                $this->_messageManager->addExceptionMessage($ex, __('Unable to update customer payment method.'));
                $this->_ebizchargeLogger->addError(__('Unable to update customer payment method'));
            }
        } else {
            $this->_messageManager->addErrorMessage(__(
                'Unable to update payment method with Ebizcharge Gateway.'
            ));
            $this->_ebizchargeLogger->addError(__('Unable to update customer payment method'));
        }

        return $this->_redirectFactory->create()->setPath('*/*/listaction');
    }
}
