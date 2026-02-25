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

use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;
use GuzzleHttp\Client;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Controller\ResultFactory;


/**
 * Ebiz Web Form Response Action class
 *
 * Class EbizWebFormResponse
 */
class AchWebHostedFormResponse extends Action implements ActionInterface
{


    /**
     * @var CheckoutSessionFactory
     */
    protected CheckoutSessionFactory $checkoutSessionFactory;

    protected CustomerSessionFactory $customerSessionFactory;
    /**
     * @var SessionManagerInterface
     */
    protected SessionManagerInterface $sessionManagerInterface;

    /**
     * @var QuoteFactory
     */
    protected QuoteFactory $quoteFactory;
    /**
     * @var ConfigFactory
     */
    protected ConfigFactory $configFactory;


    public function __construct(
        Context                 $context,
        SessionManagerInterface $sessionManager,
        CheckoutSessionFactory  $checkoutSessionFactory,
        CustomerSessionFactory  $customerSessionFactory,
        QuoteFactory            $quoteFactory,
        ConfigFactory           $configFactory

    )
    {

        parent::__construct($context);


        /** @var  customerSessionFactory */
        $this->customerSessionFactory = $customerSessionFactory;
        /** @var  sessionManagerInterface */
        $this->sessionManagerInterface = $sessionManager;
        /** @var  checkoutSessionFactory */
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        /** @var  quoteFactory */
        $this->quoteFactory = $quoteFactory;
        /** @var  configFactory */
        $this->configFactory = $configFactory;


    }

    /**
     * Execute function
     *
     * @return ResponseInterface|ResultInterface|Page
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        /** @var  $searchParams */
        $hostedWebFormResponse = $this->getRequest()->getParams();
        $storeId = $this->configFactory->create()->getStoreId();

        $cardsListingsUrl = $this->configFactory->create()->getCardsPaymentMethodsListingUrl($storeId);
        $achListingsUrl = $this->configFactory->create()->getAchPaymentMethodsListingUrl($storeId);
        $paymentType = isset($hostedWebFormResponse["payment_type"]) ? $hostedWebFormResponse["payment_type"] : "";

        // var_dump("<pre>", $_SERVER, $hostedWebFormResponse);exit;
        if (isset($hostedWebFormResponse["CustToken"]) && !empty($hostedWebFormResponse["CustToken"])) {
            $this->messageManager->addSuccessMessage(__("Success, your payment method has been added successfully."));
            $paymentMethodTypeUrl = $cardsListingsUrl;
            $html = "<div  class=\"message success\"><h2 style=\"margin: 0 0 10px; padding: 12px 20px 12px 25px;  display: block;  font-size: 1.3rem;   background: #33F5B0FF;   color: #023B02FF;  padding-left: 45px;\">Success, payment method has been added successfully</h2></div>";
            if (strtolower($paymentType) !== "cc") {
                $paymentMethodTypeUrl = $achListingsUrl;
            }
            if (isset($hostedWebFormResponse["CustToken"]) && empty($hostedWebFormResponse["CustToken"])) {
                $this->messageManager->addErrorMessage(__("Error occurred during adding your payment method."));
                $html = "<div  class=\"message success\"><h2 style=\"margin: 0 0 10px; padding: 12px 20px 12px 25px;  display: block;  font-size: 1.3rem;   background: #F39793FF;   color: #C60B0BFF;  padding-left: 45px;\">Error occurred during adding payment method.</h2></div>";
                $paymentMethodTypeUrl = str_replace("listaction", "addaction", $paymentMethodTypeUrl);
            }


            $html .= "<br/><h2 style='margin:auto;text-align: center; color:#666666; font-size:12px;'>Redirecting please wait...</h2>";
            $html .= "<a id='listaction' 
style='display:none;' href='" . $paymentMethodTypeUrl . "' target='_parent'>Click to Payment Methods</a>";

            $html .= "<script> const link = document.getElementById('listaction');  if (link) { link.click();   }   </script>";
            echo $html;
        }
        exit;


    }
}
