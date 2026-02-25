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

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\ACH;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect as Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Save ACH bank
 *
 * Class Save
 */
class Save extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_ach_save';

    /**
     * @var RedirectFactory
     */
    protected RedirectFactory $_redirectFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param RedirectFactory $redirectFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context          $context,
        CustomerFactory  $customerFactory,
        RedirectFactory  $redirectFactory,
        EbizchargeLogger $ebizchargeLogger
    )
    {
        parent::__construct($context);

        /** @var  redirectFactory */
        $this->_redirectFactory = $redirectFactory;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var  ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Execute Method
     *
     * @return Redirect|ResponseInterface|ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        /** @var $customerId */
        $customerId = $this->_request->getParam('id') ?? $this->_request->getParam('customerId');

        $customer = $this->_customerFactory->create()->load($customerId);
        $ebizCustomerInternalId = $customer->getEcCustInternalId();
        $ebizCustomerToken = $customer->getEcCustToken();
        $ebizCustomerId = $customer->getEcCustId();

        /** @var $pageAction */
        $pageAction = $this->_request->getParam('action');

        switch ($pageAction) {
            case 'edit':
                $methodId = $this->getRequest()->getParam('mid');

                $bankAccountParams = [
                    'ach_type' => $this->getRequest()->getParam('achType'),
                    'ach_number' => $this->getRequest()->getParam('achNumber'),
                    'ach_holder' => $this->getRequest()->getParam('accountHolder'),
                    'ach_route' => $this->getRequest()->getParam('achRoute'),
                    'is_default' => $this->getRequest()->getParam('isDefault'),
                    'bank_account_method_id' => $this->getRequest()->getParam('mid')
                ];

                $bankAccountResults = $this->_customerFactory->create()
                    ->updateCustomerBankAccount($customer, $bankAccountParams);

                if ($bankAccountResults['error'] === false) {
                    $this->messageManager->addSuccessMessage(__(
                        'The payment method was successfully updated.'
                    ));
                } else {
                    $this->messageManager->addErrorMessage(__(
                        'Error occurred during updating your payment method, please contact support.'
                    ));
                }

                break;
            case 'del':
                $paymentMethodId = $this->getRequest()->getParam('mid');

                $deletePaymentMethodResponse = $customer->deleteCustomerPaymentMethod($customerId, $paymentMethodId);

                if ($deletePaymentMethodResponse['error'] === false) {
                    $this->_ebizchargeLogger->addInfo(__(
                        "The payment method was successfully removed."
                    ));
                    return $this->createSuccessMessage($customerId);
                }

                if ($deletePaymentMethodResponse['error'] === true) {
                    if ($deletePaymentMethodResponse['error_code'] === 502) {
                        $errorMessage = $deletePaymentMethodResponse['message'];
                        $this->_ebizchargeLogger->addError(__(
                            "Error occurred during updating your payment method, please contact support."
                        ));
                        return $this->createErrorResponse($errorMessage, $customerId);
                    } else {
                        $errorMessage = $deletePaymentMethodResponse['message'];
                        $this->_ebizchargeLogger->addError(__(
                            "Error occurred during updating your payment method, please contact support."
                        ));
                        return $this->createErrorResponse($errorMessage, $customerId);
                    }
                }

                break;
            default:
                /** @var $bankAccountParams */

                $bankAccountParams = [
                    'ach_type' => $this->getRequest()->getParam('achType'),
                    'ach_number' => $this->getRequest()->getParam('achNumber'),
                    'ach_holder' => $this->getRequest()->getParam('accountHolder'),
                    'ach_route' => $this->getRequest()->getParam('achRoute'),
                    'is_default' => $this->getRequest()->getParam('is_default')
                ];
                /** @var $bankAccountResults */
                $bankAccountResults = $this->_customerFactory->create()->addCustomerBankAccount(
                    $customer,
                    $bankAccountParams
                );

                if ($bankAccountResults['error'] === false) {
                    $this->messageManager->addSuccessMessage(__(
                        'The payment method was successfully added.'
                    ));
                } else {
                    $this->messageManager->addErrorMessage(__(
                        'Error occurred during adding your payment method, please contact support.'
                    ));
                }

                break;
        }

        return $this->_redirectFactory->create()->setPath('customer/index/edit/', [
            'id' => $customerId
        ]);
    }

    /**
     * Create Success Message
     *
     * Creates a success message,
     * and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param mixed $customerId
     * @return Redirect
     */
    public function createSuccessMessage($customerId): Redirect
    {
        $this->messageManager->addSuccessMessage(__('The payment method was successfully removed.'));
        return $this->_redirectFactory->create()->setPath('customer/index/edit/', ['id' => $customerId]);
    }

    /**
     * Creates Error Message
     *
     * Creates an error message,
     * and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param mixed $errorMessage
     * @param mixed $customerId
     * @return Redirect
     */
    public function createErrorResponse($errorMessage, $customerId): Redirect
    {
        $this->messageManager->addErrorMessage(
            $errorMessage
        );
        return $this->_redirectFactory->create()->setPath('customer/index/edit/', ['id' => $customerId]);
    }
}
