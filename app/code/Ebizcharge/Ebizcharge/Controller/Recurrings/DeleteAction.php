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

use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface as RecurringRepository;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Exception;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;

/**
 * Deletes the customer's saved payment method
 *
 * Class DeleteAction
 */
class DeleteAction implements AccountInterface
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
     * Array Map
     *
     * @var array
     */
    private $errorsMap = [];

    /**
     * @var Validator
     */
    private $fkValidator;

    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RecurringRepository
     */
    private $recurringRepository;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @param ManagerInterface $messageManager
     * @param RecurringRepository $recurringRepository
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param TranApi $tranApi
     * @param Validator $fkValidator
     */
    public function __construct(
        ManagerInterface $messageManager,
        RecurringRepository $recurringRepository,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        TranApi $tranApi,
        Validator $fkValidator
    ) {
        /** @var  messageManager */
        $this->messageManager = $messageManager;
        /** @var  recurringRepository */
        $this->recurringRepository = $recurringRepository;
        /** @var  redirectFactory */
        $this->redirectFactory = $redirectFactory;
        /** @var  request */
        $this->request = $request;
        /** @var  tranApi */
        $this->tranApi = $tranApi;
        /** @var  fkValidator */
        $this->fkValidator = $fkValidator;

        /** @var  errorsMap */
        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.')
        ];
    }

    /**
     * Deletes customer's payment method
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        if (!$this->request instanceof Http) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        if (!$this->fkValidator->validate($this->request)) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        $internalId = $this->request->getParam('internal_id');

        if (!empty($internalId)) {
            $deleteItems = explode(',', $internalId);
        } else {
            return $this->redirectFactory->create()->setPath('ebizcharge/recurrings');
        }

        try {
            if (empty($deleteItems)) {
                return $this->createErrorResponse(self::ACTION_EXCEPTION);
            }

            foreach ($deleteItems as $id) {
                $params = [
                    'securityToken' => $this->tranApi->getUeSecurityToken(),
                    'scheduledPaymentInternalId' => trim($id),
                    'statusId' => 3,
                ];

                $res = $this->tranApi->getClient()->ModifyScheduledRecurringPaymentStatus($params);

                $modifyScheduledRecurringPaymentStatusResult = $res->ModifyScheduledRecurringPaymentStatusResult;
                if (!empty($modifyScheduledRecurringPaymentStatusResult)) {
                    if ($modifyScheduledRecurringPaymentStatusResult->StatusCode == 1) {
                        try {
                            $recurring = $this->recurringRepository->getById(
                                trim($id),
                                'eb_rec_scheduled_payment_internal_id'
                            )->setRecStatus(3);
                            $this->recurringRepository->save($recurring);
                        } catch (Exception $e) {
                            return $this->createErrorResponse(self::ACTION_EXCEPTION);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            return $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->createSuccessMessage();
    }

    /**
     * Creates an error message
     *
     * Creates an error message, and passes it to the "Manage
     * My Payment Methods" page
     *
     * @param mixed $errorCode
     * @return Redirect
     */
    private function createErrorResponse($errorCode)
    {
        $this->messageManager->addErrorMessage(
            $this->errorsMap[$errorCode]
        );
        return $this->redirectFactory->create()->setPath('ebizcharge/recurrings');
    }

    /**
     * Creates a success message
     *
     * Creates a success message, and passes it to the "Manage
     * My Payment Methods" page
     *
     * @return Redirect
     */
    private function createSuccessMessage()
    {
        /** logging to the server */
        $this->messageManager->addSuccessMessage(__('Subscription(s) successfully Deleted.'));
        return $this->redirectFactory->create()->setPath('ebizcharge/recurrings');
    }
}
