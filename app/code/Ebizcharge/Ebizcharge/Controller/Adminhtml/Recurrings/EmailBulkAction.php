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

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;

/**
 * To send Email in bulk action
 *
 * Class EmailBulkAction
 */
class EmailBulkAction extends Action implements HttpPostActionInterface
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
     * @cosnt ACTION_EXCEPTION
     */
    public const ACTION_EXCEPTION = 3;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $_messageManagerInterface;

    /**
     * Array
     *
     * @var array
     */
    private $errorsMap = [];

    /**
     * @var Validator
     */
    private Validator $fkValidator;

    /**
     * @var EbizchargeLogger
     */
    private $ebizchargeLogger;

    /**
     * @var CustomerFactory
     */
    private CustomerFactory $customerFactory;

    /**
     * @var Redirect
     */
    private Redirect $resultRedirect;

    /**
     * EmailBulkAction constructor.
     *
     * @param Context $context
     * @param Validator $fkValidator
     * @param CustomerFactory $customerFactory
     * @param Redirect $resultRedirect
     * @param ManagerInterface $messageManagerInterface
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context $context,
        Validator $fkValidator,
        CustomerFactory $customerFactory,
        Redirect $resultRedirect,
        ManagerInterface $messageManagerInterface,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($context);

        /** @var  fkValidator */
        $this->fkValidator = $fkValidator;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var customerFactory */
        $this->customerFactory = $customerFactory;
        /** @var resultRedirect */
        $this->resultRedirect = $resultRedirect;
        /** @var _messageManagerInterface */
        $this->_messageManagerInterface = $messageManagerInterface;

        /**
         * Error Maps Array
         */
        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.')];
    }

    /**
     * Deletes customer's payment method
     *
     * @return Redirect|ResponseInterface|ResultInterface|ManagerInterface
     */
    public function execute()
    {
        $request = $this->_request;

        if (!$request instanceof Http) {
            return $this->messageManager->addErrorMessage(__(
                'Errors occured during sending Email, Wrong Request '
            ));
        }

        if (!$this->fkValidator->validate($request)) {
            return $this->messageManager->addErrorMessage(__(
                'Errors occured during sending Email, the Keys are not validated '
            ));
        }

        /** @var $selectedRefNumbersRows */
        $selectedRefNumbersRows = $this->getRequest()->getParam('selected');

        /** @var  $emailResponse */
        $emailResponseRows = $this->customerFactory->create()->getEmailReceipt($selectedRefNumbersRows);

        $errorRespIds = '';
        $successRespIds = '';
        $successCounter = 0;
        $errorCounter = 0;
        $totalRows = count($emailResponseRows);

        if (count($emailResponseRows) > 0) {
            foreach ($emailResponseRows as $emailResponseRow) {
                if ($emailResponseRow['error'] == false) {
                    $successRespIds .= $emailResponseRow['refid'];

                    if ($successCounter < $totalRows-1) {
                        $successRespIds .= ',';
                    }
                    $successCounter++;
                }
                if ($emailResponseRow['error'] == true) {
                    $errorRespIds .= $emailResponseRow['refid'];

                    if ($successCounter < $totalRows-1) {
                        $successRespIds .= ',';
                    }
                    $errorCounter++;
                }

            }
        }

        if ($successCounter > 0) {
            $this->messageManager->addSuccessMessage(
                'Success email has been sent to RefIds: [' . $successRespIds . ']'
            );
        }
        if ($errorCounter > 0) {
            $this->messageManager->addErrorMessage(__(
                'Error: Could not send Email to RefIds: [' . $errorRespIds . ']'
            ));
        }
        return $this->resultRedirect->setPath('*/*/history');
    }

    /**
     * Authorization level
     *
     * @see _isAllowed()
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Ebizcharge_Ebizcharge::admin_actions_recurring_emailbulkaction'
        );
    }
}
