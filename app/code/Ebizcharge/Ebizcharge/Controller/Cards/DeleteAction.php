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

use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Exception;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;

/**
 * Delete Credit Card Action
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
     * @var RecurringRepositoryInterface
     */
    protected RecurringRepositoryInterface $_recurringRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $_searchCriteriaBuilder;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $_messageManager;

    /**
     * @var RedirectFactory
     */
    protected RedirectFactory $_redirectFactory;

    /**
     * @var TranApi
     */
    protected TranApi $_tranApi;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param CustomerFactory $customerFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param Validator $fkValidator
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        CustomerFactory $customerFactory,
        EbizchargeLogger $ebizchargeLogger,
        Validator $fkValidator
    ) {
        /** @var  messageManager */
        $this->_messageManager = $messageManager;
        /** @var  redirectFactory */
        $this->_redirectFactory = $redirectFactory;
        /** @var  request */
        $this->_request = $request;
        /** @var  fkValidator */
        $this->_fkValidator = $fkValidator;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;

        /** @var  errorsMap */
        $this->_errorsMap = [
            self::WRONG_TOKEN => __('No token found for Ebizcharge Gateway.'),
            self::WRONG_REQUEST => __('Wrong request found.'),
            self::ACTION_EXCEPTION => __('Deletion of card failed. Please try again.')
        ];
    }

    /**
     * Deletes customer's payment method.
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        if (!$this->_request instanceof Http) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        if (!$this->_fkValidator->validate($this->_request)) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        $formToken = $this->_request->getParam('cid');
        $paymentMethodId = $this->_request->getParam('mid');
        $customerId = $this->_request->getParam('cust_id');
        $customerToken = $this->_request->getParam('ebiz_cust_token');

        if ($formToken === null || $paymentMethodId === null) {
            return $this->createErrorResponse(self::WRONG_TOKEN);
        }

        try {
            /** @var $customerPaymentMethodId */
            $customerPaymentMethodId = $paymentMethodId;
            /** @var $deletePaymentMethodResponse */
            $deletePaymentMethodResponse = $this->_customerFactory->create()
                ->deleteCustomerPaymentMethod($customerId, $customerPaymentMethodId);

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
                        "Exception occurred during deleting the Payment Method"
                    ));
                    return $this->createErrorResponse($errorMessage);
                } else {
                    $errorMessage = $deletePaymentMethodResponse['message'];
                    $this->_ebizchargeLogger->addError(
                        __("Exception occurred during deleting the Payment Method")
                    );
                    return $this->createErrorResponse($errorMessage);
                }
            }
        } catch (Exception $e) {
            $this->_ebizchargeLogger->addCritical(__(
                'Exception occurred during deleting Card Exception: ' . $e->getMessage()
            ));
            return $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->createSuccessMessage();
    }

    /**
     * Creates an error message
     *
     * Creates an error message, and passes it to the
     * "Manage My Payment Methods" page.
     *
     * @param mixed $message
     * @return Redirect
     */
    private function createErrorResponse($message): Redirect
    {
        $this->_ebizchargeLogger->addCritical($message);
        $this->_messageManager->addErrorMessage(
            $message
        );
        return $this->_redirectFactory->create()->setPath('ebizcharge/cards/listaction');
    }

    /**
     * Creates a success message
     *
     * Creates a success message, and passes it to the
     * "Manage My Payment Methods" page.
     *
     * @return Redirect
     */
    private function createSuccessMessage(): Redirect
    {
        $this->_messageManager->addSuccessMessage(__(
            'Success, payment method has been removed successfully'
        ));
        $this->_ebizchargeLogger->addInfo(__(
            'Success, payment method has been removed successfully'
        ));
        return $this->_redirectFactory->create()->setPath('ebizcharge/cards/listaction');
    }
}
