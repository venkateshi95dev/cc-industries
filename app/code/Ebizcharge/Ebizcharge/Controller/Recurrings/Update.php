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

namespace Ebizcharge\Ebizcharge\Controller\Recurrings;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface as RecurringRepository;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Exception;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Model\Config;

/**
 * Updates the details of the edited payment method
 *
 * Class Update
 */
class Update implements AccountInterface
{

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var FormKeyValidator
     */
    private FormKeyValidator $formKeyValidator;

    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebizchargeLogger;

    /**
     * @var RecurringFactory
     */
    private RecurringFactory $recurringFactory;

    /**
     * Update constructor.
     * @param Config $paymentConfig
     * @param FormKeyValidator $formKeyValidator
     * @param ManagerInterface $messageManager
     * @param RecurringRepository $recurringRepository
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param RecurringFactory $recurringFactory
     * @param TranApi $tranApi
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Config              $paymentConfig,
        FormKeyValidator    $formKeyValidator,
        ManagerInterface    $messageManager,
        RecurringRepository $recurringRepository,
        RedirectFactory     $redirectFactory,
        RequestInterface    $request,
        RecurringFactory    $recurringFactory,
        TranApi             $tranApi,
        EbizchargeLogger    $ebizchargeLogger
    )
    {

        /** @var  request */
        $this->request = $request;
        /** @var  formKeyValidator */
        $this->formKeyValidator = $formKeyValidator;
        /** @var  redirectFactory */
        $this->redirectFactory = $redirectFactory;
        /** @var  messageManager */
        $this->messageManager = $messageManager;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var  recurringFactory */
        $this->recurringFactory = $recurringFactory;
    }


    /**
     * @return ResponseInterface|Redirect|ResultInterface|void
     * @throws Exception
     */
    public function execute()
    {
        /**
         * Returning back if there is issue with Validating the form key
         */
        if (!$this->formKeyValidator->validate($this->request)) {
            return $this->redirectFactory->create()->setPath('*/*/index');
        }
        /** @var  $requestParams */
        $requestParams = $this->request->getParams();
        if (isset($requestParams["rec_indefinitely"])) {
            $requestParams["rec_indefinitely"] = 1;
            $recurringParams["expire_date"] = $requestParams["end_date"];
        }

        /**
         * Subscription Params
         */
        $subscriptionAction = $this->request->getParam('subscription_action_name');

        /** @var  $recurringId */
        $recurringId = $this->request->getParam('recurring_id');

        /**
         * If action is to Update Subscription
         */
        if ($subscriptionAction === "subscription_save") {
            /** @var  $recurringFactory */
            $recurringFactory = $this->recurringFactory->create();
            /** @var  $subscriptionUpdateParams */
            $subscriptionUpdateParams = $recurringFactory->prepareFrontSubscriptionParams($requestParams);

            /** @var  $recurringResponse */
            $recurringResponse = $recurringFactory->updateRecurrings($subscriptionUpdateParams);

            $message = $recurringResponse['message'] ?? __("An unknown error occurred please try again");

            if ($recurringResponse['error'] === false) {
                $this->ebizchargeLogger->addInfo(__($message));
                $redirection = $this->createSuccessMessage($message, $recurringId);
            } else {
                /** Exception occurred and logging it to the logger */
                $this->ebizchargeLogger->addCritical(__($message));
                $redirection = $this->createErrorResponse($message, $recurringId);
            }
        }

        /**
         * if subscription action is Un Subscribe
         */
        if ($subscriptionAction === "subscription_unsubscribe" || $subscriptionAction === "subscription_suspend") {
            $actionName = RecurringInterface::EBIZCHARGE_RECURRING_STATUS_KEY_UNSUBSCRIBED;

            if ($subscriptionAction === "subscription_suspend") {
                $actionName = RecurringInterface::EBIZCHARGE_RECURRING_STATUS_KEY_SUSPENDED;
            }
            /** @var $recurringParams */
            $recurringParams = [
                'selected' => [
                    $recurringId
                ],
                'actionName' => $actionName
            ];

            /** @var $recurringStatusResp */
            $recurringStatusResp = $this->recurringFactory->create()->suspendUnsubscribeRecurrings($recurringParams);

            if ($recurringStatusResp[$recurringId]['error'] === false) {
                $message = 'Success, the Subscription has been unsubscribed.';
                if ($actionName === RecurringInterface::EBIZCHARGE_RECURRING_STATUS_KEY_SUSPENDED) {
                    $message = 'Success, the Subscription has been suspended.';
                }

                $this->ebizchargeLogger->addInfo(__($message));
                $redirection = $this->createSuccessMessage($message, $recurringId);
            } else {
                $message = "Error occurred during unsubscribing the subscription";
                if ($actionName === RecurringInterface::EBIZCHARGE_RECURRING_STATUS_KEY_SUSPENDED) {
                    $message = 'Error occurred during suspending the subscription';
                }
                /** Exception occurred and logging it to the logger */

                $this->ebizchargeLogger->addCritical(__($message));
                $redirection = $this->createErrorResponse($message, $recurringId);
            }

        }
        return $redirection;
    }

    /**
     * @param mixed|null $successMessage
     * @return Redirect
     */
    protected function createSuccessMessage(mixed $successMessage = null, $recurringId=null)
    {

        /** Logging the logger to the file */
        $this->ebizchargeLogger->addInfo(__($successMessage));
        $this->messageManager->addSuccessMessage(__($successMessage));

        return $this->redirectFactory->create()->setPath('ebizcharge/recurrings/edit/rec_id/' . $recurringId . '/');
    }

    /**
     * Creates an error message
     *
     * Creates an error message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param mixed|null $errorMessage
     * @param mixed|null $recurringId
     * @return Redirect
     */
    protected function createErrorResponse(mixed $errorMessage = null, mixed $recurringId = null)
    {
        /** logging the error */
        $this->ebizchargeLogger->addError(__($errorMessage));
        $this->messageManager->addErrorMessage(__($errorMessage));
        return $this->redirectFactory->create()->setPath('ebizcharge/recurrings/edit/rec_id/' . $recurringId . '/');
    }
}
