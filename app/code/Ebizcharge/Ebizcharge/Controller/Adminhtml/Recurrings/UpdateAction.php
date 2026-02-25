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

use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Payment\Model\Config as PaymentConfig;

/**
 * Recurring Update Action
 *
 * Class UpdateAction
 */
class UpdateAction extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * ACL for Admin Resources
     *
     * @const ADMIN_RESOURCE
     */
    public const ADMIN_RESOURCE = "Ebizcharge_Ebizcharge::admin_actions_subscriptions_orders_update_status";

    /**
     * @var TranApi
     */
    protected TranApi $_tran;

    /**
     * @var array
     */
    protected array $errorsMap = [];

    /**
     * @var Validator
     */
    protected Validator $fkValidator;

    /**
     * @var PaymentConfig
     */
    protected PaymentConfig $paymentConfig;

    /**
     * @var RecurringRepositoryInterface
     */
    protected RecurringRepositoryInterface $recurringRepository;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $recurringFactory;

    /**
     * @var array
     */
    protected array $_errorMaps = [];

    /**
     * UpdateAction constructor.
     *
     * @param Context $context
     * @param PaymentConfig $paymentConfig
     * @param RecurringRepositoryInterface $recurringRepository
     * @param RecurringFactory $recurringFactory
     * @param Validator $fkValidator
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        Context $context,
        PaymentConfig $paymentConfig,
        RecurringRepositoryInterface $recurringRepository,
        RecurringFactory $recurringFactory,
        Validator $fkValidator,
        EbizchargeLogger $ebizchargeLogger
    ) {
        parent::__construct($context);

        /** @var  paymentConfig */
        $this->paymentConfig = $paymentConfig;
        /** @var  recurringRepository */
        $this->recurringRepository = $recurringRepository;
        /** @var recurringFactory */
        $this->recurringFactory = $recurringFactory;
        /** @var  fkValidator */
        $this->fkValidator = $fkValidator;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;

        /** @var _errorMaps */
        $this->_errorMaps = [
            'token' => __('No token found to update this recurring.'),
            'request' => __('Wrong request happened when update recurring.'),
            'exception' => __('Exception occurred during update subscriptions. Please try again.')
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
            $this->messageManager->addErrorMessage(__("Error occurred during updating this Recurring."));
        }

        if (!$this->fkValidator->validate($this->_request)) {
            $this->messageManager->addErrorMessage(__(
                "Error occurred during updating this Recurring session key could not be validated "
            ));
        }

        /** @var $recurringParams */
        $recurringParams = $this->getRequest()->getParams();
        $customerId = $this->getRequest()->getParam('customer_id');
        $recurringId = $this->getRequest()->getParam('recurring_id');
        $mid = $this->getRequest()->getParam('mid');
        $currentBillingAddressId = isset($recurringParams["current_billing_method"]) && !empty($recurringParams["current_billing_method"]) ? $recurringParams["current_billing_method"]: "";
        $currentShippingAddressId = isset($recurringParams["current_shipping_method"]) && !empty($recurringParams["current_shipping_method"]) ? $recurringParams["current_shipping_method"]: "";

        $recurringParams["addresBill"] = isset($recurringParams["addresBill"]) && !empty($recurringParams["addresBill"]) ? $recurringParams["addresBill"]: $currentBillingAddressId;
        $recurringParams["addressShip"] = isset($recurringParams["addressShip"]) && !empty($recurringParams["addressShip"]) ? $recurringParams["addressShip"]: $currentShippingAddressId;

        /** @var $recurringResponse */
        $recurringResponse = $this->recurringFactory->create()->updateRecurrings($recurringParams);

        if ($recurringResponse['error'] === false) {
            $this->messageManager->addSuccessMessage(__(
                "Success, your subscription has been modified successfully"
            ));
        } else {
            $this->messageManager->addErrorMessage($recurringResponse['message']);
            return $this->_redirect('ebizcharge_ebizcharge/recurrings/editaction/magcid/' . $customerId .
                '/rid/' . $recurringId . '/mid/' . $mid);
        }
        return $this->_redirect('ebizcharge_ebizcharge/recurrings/editaction/magcid/' . $customerId .
            '/rid/' . $recurringId . '/mid/' . $mid);
    }
}
