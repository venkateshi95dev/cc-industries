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

namespace Ebizcharge\Ebizcharge\Block\Checkout;

use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Model\Config as ConfigModel;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\ProductFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Js Config Block
 *
 * Class JsConfig
 */
class JsConfig extends Template
{
    /**
     * @var ConfigModel
     */
    protected ConfigModel $_configModel;

    /**
     * @var TranApi
     */
    protected TranApi $_soapApiModel;

    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;

    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $_checkoutSession;

    /**
     * @var Session
     */
    protected Session $_customerSession;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;

    /**
     * @var DirectoryList
     */
    protected DirectoryList $_directoryList;

    /**
     * @var Quote\Item
     */
    protected Quote\Item $_quoteItem;

    /**
     * @var UrlInterface
     */
    protected UrlInterface $_urlInterface;

    /**
     * JsConfig constructor.
     * @param Context $context
     * @param ConfigModel $configModel
     * @param TranApi $soapApiModel
     * @param OrderFactory $orderFactory
     * @param Session $customerSession
     * @param CheckoutSession $checkoutSession
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlInterface
     * @param ProductFactory $productFactory
     * @param Quote\Item $quoteItem
     * @param DirectoryList $directoryList
     * @param array $data
     */
    public function __construct(
        Context               $context,
        ConfigModel           $configModel,
        TranApi               $soapApiModel,
        OrderFactory          $orderFactory,
        Session               $customerSession,
        CheckoutSession       $checkoutSession,
        CustomerFactory       $customerFactory,
        StoreManagerInterface $storeManager,
        UrlInterface          $urlInterface,
        ProductFactory        $productFactory,
        Quote\Item            $quoteItem,
        DirectoryList         $directoryList,
        array                 $data = []
    )
    {
        parent::__construct($context, $data);

        /** @var _configModel */
        $this->_configModel = $configModel;
        /** @var _soapApiModel */
        $this->_soapApiModel = $soapApiModel;
        /** @var _orderFactory */
        $this->_orderFactory = $orderFactory;
        /** @var _checkoutSession */
        $this->_checkoutSession = $checkoutSession;
        /** @var  _customerSession */
        $this->_customerSession = $customerSession;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _storeManager */
        $this->_storeManager = $storeManager;
        /** @var  _productFactory */
        $this->_productFactory = $productFactory;
        /** @var  _directoryList */
        $this->_directoryList = $directoryList;
        /** @var  _quoteItem */
        $this->_quoteItem = $quoteItem;
        /** @var  _urlInterface */
        $this->_urlInterface = $urlInterface;
    }

    /**
     * Check if PCI Compliance is enabled
     *
     * @return bool
     */
    public function isPciComplainceEnabled()
    {
        $ebizchargeActive = $this->_configModel->isEbizchargeActive();
        $pciComplianceEnabled = $this->_configModel->getPciComplianceEnabled();

        /** pci Compliance enabled  */
        if ($ebizchargeActive && $pciComplianceEnabled) {
            return true;
        }
        return false;
    }

    /**
     * Get PCI compliance URL
     *
     * @return mixed
     */
    public function getPciComplianceGatewayActionUrl()
    {
        return $this->_configModel->getPciComplianceActionUrl();
    }

    /**
     * Is Ebizcharge Enabled
     *
     * @return mixed
     */
    public function isEbizchargeEnabled()
    {
        return $this->_configModel->isEbizchargeActive();
    }

    /**
     * Get Quote Id
     *
     * @return int
     */
    public function getQuoteId()
    {
        return $this->_checkoutSession->getQuoteId();
    }

    /**
     * Checkout Session
     *
     * @return CheckoutSession|Session
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Get Merchant Security Id
     *
     * @return mixed
     */
    public function getMerchantSecurityKey()
    {
        return $this->_configModel->getSourceKey();
    }

    /**
     * Get Merchant User Id
     *
     * @return mixed
     */
    public function getMerchantUserId()
    {
        return $this->_configModel->getSourceId();
    }

    /**
     * Get Merchant Password
     *
     * @return mixed
     */
    public function getMerchantPassword()
    {
        return $this->_configModel->getSourcePin();
    }

    /**
     * Get SoapPrcessor Config Vars
     *
     * @return mixed|null
     */
    public function getSoapProcessorConfigVars()
    {
        $jsConfigVars = $this->prepareEbizSoapProcessorConfig();

        return json_encode($jsConfigVars);
    }

    /**
     * Prepare Ebizcharge Soadp Processor Config
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareEbizSoapProcessorConfig()
    {
        /**
         * Soap Processor Response Data
         */
        $soapProcessorResponseData = [];

        /** @var  $store */
        $store = $this->_storeManager->getStore();
        $storeId = $store->getId();
        $websiteId = $store->getWebsiteId();
        $websiteUrl = $this->getBaseUrl();
        $softwareId = TranApi::EBIZCHARGE_MAGENTO_SOFTWARE;
        $isRecurred = false;
        $isPreAuthTransactionEnabled = false;

        /** laoding customer variables */
        $customerSession = $this->_customerSession;
        $checkoutSession = $this->_checkoutSession;
        $currentUrl = $this->_urlInterface->getCurrentUrl();

        $isEbizchargeActive = $this->_configModel->isEbizchargeActive($storeId);

        $saleCommand = PaymentInterface::EBIZCHARGE_COMMAND_SALE;
        $selectedTransactionCommandType = $this->_configModel->transactionCommandType($storeId);

        if ($selectedTransactionCommandType === PaymentInterface::PAYMENT_TRANSACTION_TYPE_AUTHORIZE) {
            $saleCommand = PaymentInterface::EBIZCHARGE_COMMAND_TYPE_AUTHONLY;
        }

        if ($isEbizchargeActive && count($checkoutSession->getData()) > 0) {
            // phpcs:ignore
            if (strpos($currentUrl, "checkout") > 0) {
                $isPreAuthTransactionEnabled = $this->isPreAuthTransactionsEnabled($storeId);
            }
            /** @var  $algSalt */
            $algSalt = $this->_configModel->algorithmSalt($storeId);
            $merchantApiKey = $this->_configModel->getSourceKey($storeId);
            $merchantApiUserId = $this->_configModel->getSourceId($storeId);
            $merchantApiPassword = $this->_configModel->getSourcePin($storeId);

            /** @var $quote */
            $quote = $checkoutSession->getQuote();
            $quoteId = $checkoutSession->getQuote()->getId();
            $customerEmail = "";
            /** @var  $paymentMethod */
            $paymentMethod = $quote->getPayment() ? $quote->getPayment()->getMethod() : PaymentInterface::CODE;
            /** @var  $currentDate */
            $currentDate = $this->_soapApiModel->formateDateTime('', 'Y-m-d H:i:s');
            /** @var  $currentDate */
            $currentDate = $this->_soapApiModel->formatTZDateTime($currentDate);

            /** @var $soapProcessorData */
            $soapProcessorResponseData = [
                'customer_id' => "guest",
                'ebiz_customer_internal_id' => "",
                'is_pre_auth_enabled' => $isPreAuthTransactionEnabled,
                'customer_token' => "",
                'current_date' => $currentDate,
                'is_ebiz_enabled' => $isEbizchargeActive,
                'is_pci_enabled' => $this->_configModel->getPciComplianceEnabled(),
                'pci_action_gateway_url' => $this->_configModel->getPciComplianceActionUrl(),
                'pci_place_order_url' => $this->_configModel->getPciPlaceOrderUrl(),
                'quote_id' => $quoteId,
                'is_guest' => $this->_configModel->isGuestCustomer(),
                'reserve_order_id' => $this->getQuoteReserveOrderId(),
                'po_number' => $this->getQuoteReserveOrderId(),
                'invoice_number' => $this->getQuoteReserveOrderId(),
                'ebk' => base64_encode($merchantApiKey),
                'ebu' => base64_encode($merchantApiUserId),
                'ebp' => base64_encode($merchantApiPassword),
                'alg' => $this->_configModel->algorithmSalt(),
                'current_date_time' => $this->_configModel->getCurrentDateTime(),
                'current_time' => $this->_configModel->getCurrentTime(),
                'payment_method' => $paymentMethod,
                'website_id' => $websiteId,
                'website_url' => $websiteUrl,
                'software_id' => $softwareId,
                'card_listing_url' => $this->_configModel->getCardListingUrl(),
                'add_update_payment_method_url' => $this->_configModel->getAddUpdatePaymentMethodUrl(),
                'save_payment_method' => $this->_configModel->getPaymentSavePayment() ? true : false,
                'place_order_action_url' => $this->_configModel->getAjaxPlacePciOrderUrl(),
                'add_pci_transactions_url' => $this->_configModel->getAjaxAddTransactionsUrl(),
                'redirect_success_page' => $this->_configModel->redirectSuccessPageUrl(),
                'line_items' => $this->getLineItems($isPreAuthTransactionEnabled),
                'is_recurring_enabled' => $this->_configModel->isRecurringActive(),
                'is_recurred' => $isRecurred,
                'sale_command' => $saleCommand,
                'client_ip' => $this->_soapApiModel->getClientIp(),
                'inventory_location' => "",
                'ignore_duplicate' => "1",
                'merchant_name' => "CBS",
                'merchant_receipt_name' => "",
                'merchant_receipt' => "1",
                'customer_receipt_name' => "",
                'customer_receipt_email' => $customerEmail,
                'customer_receipt_number' => "1",
                'allow_partial_auth' => "false",
                'order_currency' => "",
                'order_description' => "Order description",
                'order_comments' => "Order comments",
                'order_tip' => "0",
                'session_id' => "",
                'table' => "",
                'terminal' => "CBS",
                'duty_amount' => "0",
                'non_tax' => "1",
                'void_command' => PaymentInterface::EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID,
                'payment' => [

                ]
            ];

            /**
             * Soap Processor Response Data
             */
            $soapProcessorResponseData['pre_auth_trans_params'] = $this->_customerFactory->create()
                ->preparePreAuthPaymentParams($soapProcessorResponseData, $saleCommand);

            if (!$this->_configModel->isGuestCustomer()) {
                $customerId = $customerSession->getCustomerId();
                $customer = $this->_customerFactory->create()->load($customerId);
                /** @var $customerData */
                $customerData = $customerSession->getCustomer()->getData();
                $customerEmail = $customer->getEmail();
                $customerToken = $customer->getEcCustToken();

                $soapProcessorResponseData['customer_id'] = $customerId;
                $soapProcessorResponseData['ebiz_customer_internal_id'] = $customer->getEcCustInternalId();
                $soapProcessorResponseData['customer_token'] = $customerToken;
                $soapProcessorResponseData['customer'] = [
                    'entity_id' => $customer->getId(),
                    'email' => $customer->getEmail(),
                    'customer_token' => $customer->getEcCustToken(),
                    'ebiz_internal_id' => $customer->getEcCustInternalId(),
                    'ebiz_customer_id' => $customer->getEcCustId(),
                    'software_id' => $customer->getEcSoftwareId()
                ];
                $soapProcessorResponseData['payment'] = [
                    'customer_account' => true,
                    'entity_id' => $customer->getId(),
                    'customer_id' => $customer->getId(),
                    'email' => $customer->getEmail(),
                    'customer_token' => $customer->getEcCustToken(),
                    'ebiz_internal_id' => $customer->getEcCustInternalId(),
                    'ebiz_customer_id' => $customer->getEcCustId(),
                    'software_id' => $customer->getEcSoftwareId()
                ];
            }
        }
        return $soapProcessorResponseData;
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore(): StoreInterface
    {
        return $this->_configModel->getStore();
    }

    /**
     * Get Base Url
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Is Pre Auth Transactions Enabled
     *
     * @param mixed $storeId
     * @return bool|mixed
     */
    public function isPreAuthTransactionsEnabled($storeId)
    {
        return $this->_configModel->getPreAuthTransactionEnabled($storeId);
    }

    /**
     * Get Quote
     *
     * @return CartInterface|Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Get Quote Reserved Order id
     *
     * @return array|string|string[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteReserveOrderId()
    {
        $reservedOrderId = $this->_checkoutSession->getReservedOrderId();
        if (!$reservedOrderId) {
            $this->_checkoutSession->getQuote()->reserveOrderId();
            $reservedOrderId = $this->_checkoutSession->getQuote()->getReservedOrderId();
            $this->_checkoutSession->setReservedOrderId($reservedOrderId);
        }

        $envPrefix = $this->_configModel->getEnvoirnmentPrefix();
        $newOrderId = str_replace([$envPrefix, "-"], ["", ""], $reservedOrderId);
        $incOrderId = (int)$newOrderId;
        $newOrderId = $incOrderId + 1;
        $reservedOrderId = str_replace((string)$incOrderId, (string)$newOrderId, $reservedOrderId);

        return $reservedOrderId;
    }

    /**
     * Get Line Items
     *
     * @param int $isPreAuthEnabled
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getLineItems($isPreAuthEnabled = 0): array
    {
        $quoteItems = [];

        /** @var  $quoteAllItems */
        $quoteAllItems = $this->_checkoutSession->getQuote()->getItems();

        /** Quote Items  */
        if ($quoteAllItems && count($quoteAllItems) > 0) {

            foreach ($quoteAllItems as $item) {

                $quoteItem = $this->_quoteItem->load($item->getItemId());

                $isTaxAble = false;
                $taxAmount = (double)$item->getTaxAmount();
                /** tax amount */
                if ($taxAmount > 0) {
                    $isTaxAble = true;
                }
                $productId = $item->getProductId();
                /** @var  $product */
                $product = $this->_productFactory->create()->loadByAttribute(
                    'sku',
                    $item->getSku()
                );

                $productAttributes = [
                    'item_id' => $productId,
                    'item_discount_percent' => $quoteItem->getDiscountPercent(),
                    'item_discount_amount' => $quoteItem->getDiscountAmount(),
                    'item_row_total' => (float)$quoteItem->getPrice() * $quoteItem->getQty(),
                    'item_name' => $product->getName(),
                    'item_sku' => $quoteItem->getSku(),
                    'ebiz_internal_id' => $product->getEbizInternalId(),
                    'item_description' => substr($product->getData("name"), 0, 100),
                    'item_cost_price' => $quoteItem->getBaseCost(),
                    'item_qty' => $quoteItem->getQty(),
                    'item_price' => $quoteItem->getPrice(),
                    'item_price_incl_tax' => $quoteItem->getPriceInclTax(),
                    'item_type_id' => $product->getTypeId(),
                    'item_special_price' => $product->getSpecialPrice(),
                    'item_weight' => $product->getWeight(),
                    'item_tax_class_id' => $product->getTaxClassId(),
                    'item_quantity_and_stock_status' => $product->getQuantityAndStockStatus(),
                    'item_image' => $this->getMediaBaseUrl() . $product->getImage(),
                    'item_thumbnail' => $this->getMediaBaseUrl() . $product->getThumbnail(),
                    'item_url_key' => $product->getUrlKey(),
                    'item_is_taxable' => $isTaxAble,
                    'item_tax_amount' => $quoteItem->getTaxAmount(),
                    'item_tax_percent' => $quoteItem->getTaxPercent()
                ];
                /** @var $itemData */
                // phpcs:ignore
                $itemData = array_merge($productAttributes, $item->getData());
                $quoteItems[] = $itemData;
            }
        }
        return $quoteItems;
    }

    /**
     * Get Media Base Url
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get Customer
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->_checkoutSession->getQuote()->getCustomer();
    }

    /**
     * @throws NoSuchEntityException
     */
    public function is3DsecureEnabled($storeId = 0): bool
    {
        $is3DEnabled = false;
        $isCheckoutPage = $this->getRequest()->getModuleName() === 'checkout'
            && $this->getRequest()->getControllerName() === 'index'
            && $this->getRequest()->getActionName() === 'index';

        if ($isCheckoutPage) {
            $is3DEnabled = $this->_customerFactory->create()->is3DSecureEnabled($storeId);
        }

        return $is3DEnabled;
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getTokenKey($storeId=0): string
    {
     return $this->_configModel->getSourceKey($storeId);
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getTokenUser($storeId=0): string
    {
        return $this->_configModel->getSourceId($storeId);
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getTokenPass($storeId=0): string
    {
        return $this->_configModel->getSourcePin($storeId);
    }

    /**
     * @return StoreInterface|null
     * @throws NoSuchEntityException
     */
    public function getStoreId(): ?StoreInterface
    {
        return $this->getStore();
    }
}
