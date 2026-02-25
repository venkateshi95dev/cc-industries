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

use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface as RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * Recurring Update Subscription Status
 *
 * Class UpdateSubscriptionStatus
 */
class UpdateSubscriptionStatus extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_update_status';

    /**
     * Wrong Request
     *
     * @const WRONG_REQUEST
     */
    public const WRONG_REQUEST = 1;

    /**
     * Wrong Token
     *
     * const WRONG_TOKEN
     */
    public const WRONG_TOKEN = 2;

    /**
     * Action Exception
     *
     * @const ACTION_EXCEPTION
     */
    public const ACTION_EXCEPTION = 3;

    /**
     * @var RecurringRepositoryInterface
     */
    protected RecurringRepositoryInterface $_recurringRepository;

    /**
     * @var array
     */
    protected array $_errorsMap = [];

    /**
     * @var Validator
     */
    protected Validator $_fkValidator;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $_recurringFactory;

    /**
     * UpdateSubscriptionStatus constructor.
     *
     * @param Context $context
     * @param Validator $fkValidator
     * @param RecurringRepositoryInterface $recurringRepository
     * @param RecurringFactory $recurringFactory
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context $context,
        Validator $fkValidator,
        RecurringRepositoryInterface $recurringRepository,
        RecurringFactory $recurringFactory,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($context);

        /** @var  recurringRepository */
        $this->_recurringRepository = $recurringRepository;
        /** @var  fkValidator */
        $this->_fkValidator = $fkValidator;
        /** @var  ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
        /** @var recurringFactory */
        $this->_recurringFactory = $recurringFactory;

        /** @var  errorsMap */
        $this->_errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Action failure. Please try again.')
        ];
    }

    /**
     * Execute Method
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        if (!$this->_request instanceof Http) {
            $this->createErrorResponse(self::WRONG_REQUEST);
        }

        if (!$this->_fkValidator->validate($this->_request)) {
            $this->createErrorResponse(self::WRONG_TOKEN);
        }

        $paymentMethodId = $this->getRequest()->getParam('payment_method_id');
        $currentStatus = $this->getRequest()->getParam('current_status');
        $recurringId = $this->getRequest()->getParam('recurring_id');
        $currentAction = $this->getRequest()->getParam('action_name');

        /** @var $paymentMethodInternalId */
        $paymentMethodInternalId = $paymentMethodId;

        /**
         * $recurring Resp
         */
        $recurringResp = $this->_recurringFactory->create()
            ->suspendScheduledRecurringPaymentStatus($paymentMethodInternalId, $currentAction);

        /** recurring Response */
        if (isset($recurringResp['error']) && $recurringResp['error'] == false) {

            $recurringStatusResp = $this->_recurringFactory->create()
                ->updateRecurringStatus($recurringId, $currentAction);

            if (isset($recurringStatusResp['error']) && $recurringStatusResp['error'] == false) {
                $this->_ebizchargeLogger->addInfo(__(
                    "Success, the Subscription status has been updated successfully "
                ));
                $this->messageManager->addSuccessMessage(__(
                    "Success, the Subscription status has been updated successfully"
                ));
            } else {
                $this->createErrorResponse(self::ACTION_EXCEPTION);
            }

        } else {
            $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->_redirect('ebizcharge_ebizcharge/recurrings');
    }

    /**
     * Creates an error message
     *
     * Creates an error message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param int $errorCode
     * @return void
     */
    protected function createErrorResponse($errorCode)
    {
        /** logging to the logger */
        $this->_ebizchargeLogger->addError(__("Error occurred " . $errorCode));
        $this->messageManager->addErrorMessage(
            $this->_errorsMap[$errorCode]
        );
    }
}
