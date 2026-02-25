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

use GuzzleHttp\Client;
use Ebizcharge\Ebizcharge\Model\Config as EbizchargeConfig;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\Controller\ResultFactory;


/**
 * Ebiz Web Form Response Action class
 *
 * Class EbizWebFormResponse
 */
class CardsWebHostedFormResponse extends Action implements ActionInterface
{

    /**
     * @var TranApi
     */
    protected $soapApiModel;
    /**
     * @var Client
     */
    protected $httpClient;
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $_checkoutSession;
    /**
     * @var CustomerSession
     */
    protected CustomerSession $_customerSession;
    /**
     * @var SessionManagerInterface
     */
    protected SessionManagerInterface $_sessionManagerInterface;


    /**
     * @var Quote
     */
    protected Quote $quoteModel;

    protected EbizchargeConfig $ebizchargeConfig;

    /**
     * @var Quote
     */
    protected Quote $_quoteModel;

    /**
     * @param Context $context
     * @param TranApi $soapApiModel
     * @param SessionManagerInterface $sessionManager
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param Quote $quoteModel
     * @param Client $httpClient
     * @param EbizchargeConfig $ebizchargeConfig
     */
    public function __construct(
        Context                 $context,
        TranApi                 $soapApiModel,
        SessionManagerInterface $sessionManager,
        CheckoutSession         $checkoutSession,
        CustomerSession         $customerSession,
        Quote                   $quoteModel,
        Client                  $httpClient,
        EbizchargeConfig        $ebizchargeConfig

    )
    {

        parent::__construct($context);

        /** @var  httpClient */
        $this->httpClient = $httpClient;
        /** @var  soapApiModel */
        $this->soapApiModel = $soapApiModel;
        /** @var  _customerSession */
        $this->_customerSession = $customerSession;
        /** @var  _sessionManagerInterface */
        $this->_sessionManagerInterface = $sessionManager;
        /** @var  _checkoutSession */
        $this->_checkoutSession = $checkoutSession;
        /** @var  _quoteModel */
        $this->_quoteModel = $quoteModel;
        /** @var  ebizchargeConfig */
        $this->ebizchargeConfig = $ebizchargeConfig;


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
        $storeId = $this->ebizchargeConfig->getStoreId();


        $cardsListingsUrl = $this->ebizchargeConfig->getCardsPaymentMethodsListingUrl($storeId);
        $achListingsUrl = $this->ebizchargeConfig->getAchPaymentMethodsListingUrl($storeId);
        $paymentType = isset($hostedWebFormResponse["payment_type"]) ? $hostedWebFormResponse["payment_type"] : "";

        if (isset($hostedWebFormResponse["CustToken"])) {
            $this->messageManager->addSuccessMessage(__("Success, your payment method has been added successfully."));
            $paymentMethodTypeUrl = $cardsListingsUrl;
            $html = "<div  class=\"message success\"><h2 style=\"margin: 0 0 10px; padding: 12px 20px 12px 25px;  display: block;  font-size: 1.3rem;   background: #33F5B0FF;   color: #023B02FF;  padding-left: 45px;\">Success, payment method has been added successfully</h2></div>";
            if ($paymentType !== "cc") {
                $paymentMethodTypeUrl = $achListingsUrl;
            }
            if (empty($hostedWebFormResponse["CustToken"])) {
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
