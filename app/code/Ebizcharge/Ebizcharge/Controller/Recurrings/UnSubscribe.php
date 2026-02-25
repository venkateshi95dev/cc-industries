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
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
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
 * Unsubscribe a recurring
 *
 * Class UnSubscribe
 */
class UnSubscribe implements AccountInterface
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
     * @var Validator
     */
    protected $fkValidator;

    /**
     * @var TranApi
     */
    protected $tranApi;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var RecurringRepository
     */
    protected $recurringRepository;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var EbizchargeLogger
     */
    protected $ebizchargeLogger;

    /**
     * @var RecurringFactory
     */
    protected $_recurringFactory;

    /**
     * Errors Map Array
     *
     * @var array
     */
    protected $errorsMap = [];

    /**
     * UnSubscribe constructor.
     *
     * @param ManagerInterface $messageManager
     * @param RecurringRepository $recurringRepository
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param RecurringFactory $recurringFactory
     * @param Validator $fkValidator
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        ManagerInterface $messageManager,
        RecurringRepository $recurringRepository,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        RecurringFactory $recurringFactory,
        Validator $fkValidator,
        EbizchargeLogger $ebizchargeLogger
    ) {
        /** @var  messageManager */
        $this->messageManager = $messageManager;
        /** @var  recurringRepository */
        $this->recurringRepository = $recurringRepository;
        /** @var  redirectFactory */
        $this->redirectFactory = $redirectFactory;
        /** @var  request */
        $this->request = $request;
        /** @var  fkValidator */
        $this->fkValidator = $fkValidator;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var _recurringFactory */
        $this->_recurringFactory = $recurringFactory;

        /** @var  errorsMap */
        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.')
        ];
    }

    /**
     * Execute Method
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

        $mid = $this->request->getParam('mid');
        $sid = $this->request->getParam('sid');
        $recurringId = $this->request->getParam('recurring_id');

        /** @var $recurringParams */
        $recurringParams = [
            'selected' => [
                $recurringId
            ],
            'actionName' => 'unsubscribe'
        ];

        /** @var $recurringStatusResp */
        $recurringStatusResp = $this->_recurringFactory->create()
            ->suspendUnsubscribeRecurrings($recurringParams);

        if ($recurringStatusResp[$recurringId]['error'] == false) {
            $this->ebizchargeLogger->addInfo(__('Success, the Subscription has been unsubscribed.'));
            return $this->createSuccessMessage($sid);
        } else {
            /** Exception occured and logging it to the logger */
            $this->ebizchargeLogger->addCritical(__("Exception occured during unsubscribing the Recurring Id"));
            return $this->createErrorResponse(self::ACTION_EXCEPTION);
        }
    }

    /**
     * Creates an error message
     *
     * Creates an error message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param int $errorCode
     * @return Redirect
     */
    private function createErrorResponse($errorCode)
    {
        $this->messageManager->addErrorMessage(
            $this->errorsMap[$errorCode]
        );

        /** loging the error */
        $this->ebizchargeLogger->addError(__(
            "An error occured during subscriptions error code is : " . $errorCode
        ));

        return $this->redirectFactory->create()->setPath('ebizcharge/recurrings');
    }

    /**
     * Creates a success message
     *
     * Creates a success message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param mixed $id
     * @return Redirect
     */
    private function createSuccessMessage($id)
    {
        $str = $id == 0 ? 'Resubscribed' : 'Unsubscribed';

        /** Logging the logger to the file */
        $this->ebizchargeLogger->addInfo(__("Subscription successfully $str."));
        $this->messageManager->addSuccessMessage(__("Success! the subscription has been $str."));

        return $this->redirectFactory->create()->setPath('ebizcharge/recurrings/');
    }
}
