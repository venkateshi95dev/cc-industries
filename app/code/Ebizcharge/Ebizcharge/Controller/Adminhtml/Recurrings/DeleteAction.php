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

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;

/**
 * Deletes the customer's saved payment method
 *
 * Class DeleteAction
 */
class DeleteAction extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = 'Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_delete';

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
     * @var TranApi
     */
    protected TranApi $_tran;

    /**
     * @var array
     */
    private array $errorsMap = [];

    /**
     * @var Validator
     */
    private Validator $fkValidator;

    /**
     * @var RecurringRepositoryInterface
     */
    private RecurringRepositoryInterface $recurringRepository;

    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $ebizchargeLogger;

    /**
     * Main Constructor of the Class
     *
     * @param Context $context
     * @param Validator $fkValidator
     * @param TranApi $tranApi
     * @param RecurringRepositoryInterface $recurringRepository
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context $context,
        Validator $fkValidator,
        TranApi $tranApi,
        RecurringRepositoryInterface $recurringRepository,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($context);

        /** @var  fkValidator */
        $this->fkValidator = $fkValidator;
        /** @var  _tran */
        $this->_tran = $tranApi;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;

        /** @var  errorsMap */
        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.')];
        $this->recurringRepository = $recurringRepository;
    }

    /**
     * Deletes customer's payment method.
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {

        $request = $this->_request;

        if (!$request instanceof Http) {
            $this->ebizchargeLogger->addError(__("Wrong http request " . self::WRONG_REQUEST));
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        if (!$this->fkValidator->validate($request)) {
            $this->ebizchargeLogger->addError(__("Wrong validation request " . self::WRONG_REQUEST));
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        $internalId = $this->getRequest()->getParam('internal_id');
        $atype = $this->getRequest()->getParam('atype');

        if (!empty($internalId)) {
            $deleteItems = explode(',', $internalId);
        }

        $type = $atype == 'del' ? 'Suspended' : 'Unsubscribed';

        try {
            $statusId = $atype == 'del' ? 3 : 1;

            if (!empty($deleteItems)) {
                foreach ($deleteItems as $id) {
                    $params = [
                        'securityToken' => $this->_tran->getUeSecurityToken(),
                        'scheduledPaymentInternalId' => $id,
                        'statusId' => $statusId,
                    ];

                    $res = $this->_tran->getClient()->ModifyScheduledRecurringPaymentStatus($params);
                    $ModifyScheduledRecurringPaymentStatusResult = $res->ModifyScheduledRecurringPaymentStatusResult;
                    if (!empty($ModifyScheduledRecurringPaymentStatusResult) &&
                        $ModifyScheduledRecurringPaymentStatusResult->StatusCode == 1) {
                        try {
                            $recurringRecord = $this->recurringRepository->getById(
                                trim($id),
                                RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID
                            );
                            $recurringRecord->setRecStatus((int)$statusId);
                            $this->recurringRepository->save($recurringRecord);
                        } catch (Exception $e) {
                            /** logging to logger the exception */
                            $this->ebizchargeLogger->addCritical(__("Exception occured " . $e->getMessage()));
                        }
                    }
                }
            }

        } catch (Exception $e) {
            /** Logging to the logger the exception */
            $this->ebizchargeLogger->addCritical(__("Action Exception Occured " . $e->getMessage()));
            return $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->createSuccessMessage($type);
    }

    /**
     * Creates an error message
     *
     * Creates an error message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param int $errorCode
     * @return ResponseInterface
     */
    private function createErrorResponse($errorCode)
    {
        $this->messageManager->addErrorMessage(
            $this->errorsMap[$errorCode]
        );

        return $this->_redirect('ebizcharge_ebizcharge/recurrings');
    }

    /**
     * Creates a success message
     *
     * Creates a success message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param mixed $type
     * @return ResponseInterface
     */
    private function createSuccessMessage($type)
    {
        /** logging the success message to the logger */
        $this->ebizchargeLogger->addInfo(__('Subscription(s) successfully ' . $type . '.'));

        $this->messageManager->addSuccessMessage(__('Subscription(s) successfully ' . $type . '.'));
        return $this->_redirect('ebizcharge_ebizcharge/recurrings');
    }
}
