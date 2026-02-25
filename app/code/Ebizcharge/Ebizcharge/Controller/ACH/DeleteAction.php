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

namespace Ebizcharge\Ebizcharge\Controller\ACH;

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Exception;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;

/**
 * Delete ACH Bank Action
 *
 * Class DeleteAction
 */
class DeleteAction implements AccountInterface, HttpGetActionInterface, HttpPostActionInterface
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
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $_scopeConfig;

    /**
     * @var array
     */
    protected array $_errorsMap = [];

    /**
     * @var Validator
     */
    protected Validator $_fkValidator;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $_request;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $_messageManager;

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
     * DeleteAction constructor.
     *
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerFactory $customerFactory
     * @param Validator $fkValidator
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        CustomerFactory $customerFactory,
        Validator $fkValidator,
        EbizchargeLogger $ebizchargeLogger
    ) {
        /** @var _scopeConfig */
        $this->_scopeConfig = $scopeConfig;

        /** @var fkValidator */
        $this->_fkValidator = $fkValidator;
        /** @var messageManager */
        $this->_messageManager = $messageManager;
        /** @var redirectFactory */
        $this->_redirectFactory = $redirectFactory;
        /** @var request */
        $this->_request = $request;
        /** @var  ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;

        /**
         * Errors Map Array
         */
        $this->_errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.')
        ];
    }

    /**
     * Deletes customer's payment method.
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if (!$this->_request instanceof Http) {
            $this->_ebizchargeLogger->addInfo(__("Wrong request for http : " . self::WRONG_REQUEST));
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        if (!$this->_fkValidator->validate($this->_request)) {
            $this->_ebizchargeLogger->addInfo(__("Wrong request for fk validator: " . self::WRONG_REQUEST));
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        /** @var  $customerId */
        $customerId = $this->_request->getParam('cid');
        /** @var  $paymentMethodId */
        $paymentMethodId = $this->_request->getParam('mid');

        /** $cid and $mid */
        if ($customerId === null || $paymentMethodId === null) {
            return $this->createErrorResponse(self::WRONG_TOKEN);
        }

        try {
            $customer = $this->_customerFactory->create()->load($customerId);

            if (!$customer->getId()) {
                $this->_ebizchargeLogger->addError(__(
                    "Exception occured during deleting the Payment Method"
                ));
                return $this->createErrorResponse(__(
                    "Error occured during deleting Payment Method, Customer Does not Exists"
                ));

            }
            $deletePaymentMethodResponse = $customer->deleteCustomerPaymentMethod($customerId, $paymentMethodId);

            if ($deletePaymentMethodResponse['error'] === false) {
                $this->_ebizchargeLogger->addInfo(__(
                    "Success, the Payment Method has been deleted succcessfully"
                ));
                return $this->createSuccessMessage();
            }

            if ($deletePaymentMethodResponse['error'] === true) {
                if ($deletePaymentMethodResponse['error_code'] == 502) {
                    $errorMessage = $deletePaymentMethodResponse['message'];
                    $this->_ebizchargeLogger->addError(__(
                        "Exception occured during deleting the Payment Method"
                    ));
                    return $this->createErrorResponse($errorMessage);
                } else {
                    $errorMessage = $deletePaymentMethodResponse['message'];
                    $this->_ebizchargeLogger->addError(__(
                        "Exception occured during deleting the Payment Method"
                    ));
                    return $this->createErrorResponse($errorMessage);
                }
            }
        } catch (Exception $e) {
            $this->_ebizchargeLogger->addCritical(__(
                "Exception occured during deleting payment method profile " . $e->getMessage()
            ));
            return $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->createSuccessMessage();
    }

    /**
     * Create Error Response
     *
     * Creates an error message,
     * and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param mixed $errorMessage
     * @return Redirect
     */
    public function createErrorResponse($errorMessage): Redirect
    {
        $this->_messageManager->addErrorMessage(
            $errorMessage
        );

        return $this->_redirectFactory->create()->setPath('ebizcharge/ach/listaction');
    }

    /**
     * Create Success Message
     *
     * Creates a success message,
     * and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @return Redirect
     */
    public function createSuccessMessage(): Redirect
    {
        $this->_messageManager->addSuccessMessage(__('Bank Account successfully deleted.'));
        return $this->_redirectFactory->create()->setPath('ebizcharge/ach/listaction');
    }
}
