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

namespace Ebizcharge\Ebizcharge\Controller\Checkout;


use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Ebizcharge\Ebizcharge\Model\OrderFactory;
use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Controller\ResultFactory;

class PlaceSubscriptionsAction extends Action
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var ConfigFactory
     */
    protected ConfigFactory $configFactory;

    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $recurringFactory;

    /**
     * @var Session
     */
    protected Session $checkoutSessin;

    /**
     * @var OrderFactory
     */
    protected OrderFactory $orderFactory;

    /**
     * @var Cart
     */
    protected Cart $cart;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ConfigFactory $configFactory
     * @param EbizchargeLogger $ebizchargeLogger
     * @param RecurringFactory $recurringFactory
     * @param OrderFactory $orderFactory
     * @param Cart $cart
     * @param Session $checkoutSession
     */
    public function __construct(
        Context          $context,
        JsonFactory      $resultJsonFactory,
        ConfigFactory    $configFactory,
        EbizchargeLogger $ebizchargeLogger,
        RecurringFactory $recurringFactory,
        OrderFactory     $orderFactory,
        Cart             $cart,
        Session          $checkoutSession

    )
    {
        parent::__construct($context);

        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var  resultJsonFactory */
        $this->resultJsonFactory = $resultJsonFactory;
        /** @var  configFactory */
        $this->configFactory = $configFactory;
        /** @var  recurringFactory */
        $this->recurringFactory = $recurringFactory;
        $this->orderFactory = $orderFactory;
        /**
         * checkout Session
         */
        $this->checkoutSessin = $checkoutSession;
        $this->cart = $cart;
    }

    public function execute()
    {
        $request = $this->getRequest() ?? [];
        /**
         * placing recurrings
         */
        $this->placeRecurrings($request);

    }

    /**
     * @param $request
     * @return void
     */
    public function placeRecurrings($request = null)
    {
        $response = [
            "status" => false,
            'success' => false,
            'response' => [],
            'message' => 'Error occurred during subscription processing',
        ];
        try {
            $recurringParams = $this->getRequest()->getParams();
            $recurringFactory = $this->recurringFactory->create();

            $addRecurringsResponse = $recurringFactory->addSingleRecurringOrder($recurringParams);

            if (isset($addRecurringsResponse["status"]) && $addRecurringsResponse["status"] === true ) {
                $quoteId = $this->checkoutSessin->getQuote()->getId();
                $session = $this->checkoutSessin;
                $this->cart->truncate()->save();
                $this->_eventManager->dispatch('checkout_cart_item_remove_all');

                $session->setLastSuccessQuoteId($quoteId)
                    ->setLastOrderId($quoteId)
                    ->setLastQuoteId($quoteId)
                ;

                $response = [
                    "status" => true,
                    'success' => true,
                    'response' => $addRecurringsResponse,
                    'message' => 'Success, the subscription has been added.',
                ];
                $this->ebizchargeLogger->addInfo(__("Success, the subscription has been added to the Database"));
            } else {
                $response["message"] = isset($addRecurringsResponse["message"]) ? $addRecurringsResponse["message"] : $response["message"];
            }

        } catch (Exception $exception) {
            $this->ebizchargeLogger->addCritical(__("Exception occurred during adding schedule error: " . $exception->getMessage()));
            $response["message"] = "Error during adding subscription " . $exception->getMessage();
        }

        echo json_encode($response);

    }
}
