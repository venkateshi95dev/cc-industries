<?php

/**
 * Century Business Solutions.
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
 *
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 *
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model;

use DateMalformedStringException;
use DateTime;
use Ebizcharge\Ebizcharge\Api\Data\CustomerInterface;
use Ebizcharge\Ebizcharge\Api\Data\GraphQL\GraphQLInterface;
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Api\Data\SoapApiModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Helper\Data as EbizDataHelper;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Exception;
use Magento\Backend\Model\Session\Quote as BackendSessionQuote;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Bundle\Model\Product\Type;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\State;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\Session;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use SoapClient;
use SoapFault;

use const PHP_VERSION_ID;

/**
 * EBizCharge Transaction Class.
 *
 * Class TranApi
 */
class TranApi implements SoapApiModelInterface
{
    /**
     * SoapClient.
     *
     * @var null|SoapClient
     */
    public $ebizSoapClient;

    /**
     * Type of the card.
     *
     * @var string
     */
    public $cardtype;

    /**
     * Bank Account Types var.
     *
     * @var array
     */
    public $bankAccountTypes;

    /**
     * @var array
     */
    public $allowedProductTypes = [
        Grouped::TYPE_CODE,
        Type::TYPE_CODE,
        Configurable::TYPE_CODE,
    ];

    // Required for Commercial Card support
    public string $orderComments;

    /**
     * @var EbizchargeLogger
     */
    protected $ebizchargeLogger;

    /**
     * User Id var.
     *
     * @var int
     */
    protected $userid;

    /**
     * Source key | security Id.
     *
     * @var mixed
     */
    protected $key;

    /**
     * Source pin/password (optional).
     *
     * @var mixed
     */
    protected $pin;

    /**
     * The entire amount that will be charged to the
     * customers card (including tax, shipping, etc).
     *
     * @var mixed
     */
    protected $amount;

    /**
     * Invoice number.
     * Must be unique. Limited to 10 digits.
     * Use orderId if you need longer.
     *
     * @var int
     */
    protected $invoice;

    /**
     * Purchase Order Number.
     *
     * @var mixed
     */
    protected $ponum;

    /**
     * Tax var.
     *
     * @var mixed
     */
    protected $tax;

    /**
     * Order is non-taxable.
     *
     * @var bool
     */
    protected $nontaxable;

    /**
     * Amount details (optional).
     *
     * @var mixed
     */
    protected $tip;

    /**
     * Shipping charge.
     *
     * @var mixed
     */
    protected $shipping;

    /**
     * Discount amount (ie gift certificate or coupon code).
     *
     * @var mixed
     */
    protected $discount;

    /**
     *  if subtotal is set, then
     *  subtotal + tip + shipping - discount + tax must equal amount
     *  or the transaction will be declined.  If subtotal is left blank
     *  then it will be ignored.
     *
     * @var mixed
     */
    protected $subtotal;

    /**
     * Grand Total var.
     *
     * @var float
     */
    protected $grandtotal;

    /**
     * Currency of $amount.
     *
     * @var mixed
     */
    protected $currency;

    /**
     * card number, no dashes, no spaces
     * Required Fields for Card Not Present transacitons (Ecommerce).
     *
     * @var string
     */
    protected $card;

    /**
     * Expiration date 4 digits no.
     *
     * @var mixed
     */
    protected $exp;

    /**
     * Name of card-holder. Please enter a
     * valid credit card type number.
     *
     * @var mixed
     */
    protected $cardholder;

    /**
     * Street address.
     *
     * @var mixed
     */
    protected $street;

    /**
     * Zip code.
     *
     * @var mixed
     */
    protected $zip;

    /**
     * Current saved method Id.
     *
     * @var int
     */
    protected $savedMethodId;

    /**
     * Required Fields for Ach: ACH Route.
     *
     * @var mixed
     */
    protected $achroute;

    /**
     * ACH Type var.
     *
     * @var string
     */
    protected $achtype;

    /**
     * Fields for Card Present (POS)
     * Mag stripe data.  can be either Track 1,
     * Track2  or  Both  (Required if card,exp,cardholder,
     * street and zip aren't filled in).
     *
     * @var mixed
     */
    protected $magstripe;

    /**
     * Must be set to true if processing a card
     * present transaction  (Default is false).
     *
     * @var bool
     */
    protected $cardpresent = false;

    /**
     * fields required for check transactions
     *  Bank account number.
     *
     * @var mixed
     */
    protected $account;

    /**
     * Bank routing number.
     *
     * @var mixed
     */
    protected $routing;

    /**
     * Fields required for Secure Vault Payments (Direct Pay)
     * ID of cardholders bank.
     *
     * @var mixed
     */
    protected $svpbank;

    /**
     * URL that the bank should return the user
     * to when tran is completed.
     *
     * @var mixed
     */
    protected $svpreturnurl;

    /**
     * URL that the bank should return
     * the user if they cancel.
     *
     * @var mixed
     */
    protected $svpcancelurl;

    /**
     * Required if running post auth transaction.
     * Option parameters.
     *
     * @var mixed
     */
    protected $origauthcode;

    /**
     * Type of command to run; Possible values are:
     * sale, credit, void, preauth, postauth,
     * check and checkcredit.
     * Default is sale.
     *
     * @var string
     */
    protected $command = 'sale';

    /**
     * Unique order identifier.  This field can be used to reference
     * the order for which this transaction corresponds to. This field
     * can contain up to 64 characters and should be used instead of
     * UMinvoice when orderids longer than 10 digits are needed.
     *
     * @var mixed
     */
    protected $orderid;

    /**
     * Alpha-numeric id that uniquely
     * identifies the customer.
     *
     * @var mixed
     */
    protected $custid;

    /**
     * Description of charge.
     *
     * @var string
     */
    protected $description;

    /**
     * cvv2 code.
     *
     * @var mixed
     */
    protected $cvv2;

    /**
     * Prevent the system from detecting and folding duplicates.
     *
     * @var bool
     */
    protected $ignoreduplicate;

    /**
     *  IP address of remote host.
     *
     * @var mixed
     */
    protected $ip;

    /**
     *  Transaction timeout.  defaults to 90 seconds.
     *
     * @var int
     */
    protected $timeout = 90;

    /**
     * Save transaction as a recurring transaction
     * Recurring Billing.
     */
    protected bool $recurring;

    /**
     * How often to run transaction: daily,
     * weekly, biweekly, monthly, bimonthly,
     * quarterly, annually.
     * Default is monthly.
     *
     * @var mixed
     */
    protected $schedule;

    /**
     * The number of times to run.
     * Either a number or * for unlimited.
     * Default is unlimited.
     *
     * @var mixed
     */
    protected $numleft;

    /**
     * When to start the schedule.
     * Default is tomorrow.
     * Must be in YYYYMMDD  format.
     *
     * @var mixed
     */
    protected $start;

    /**
     * When to stop running transactions. Default is to run forever.
     * If both end and numleft are specified,
     * transaction will stop when the earliest condition is met.
     *
     * @var mixed
     */
    protected $expire;

    /**
     * Recurring is infinite.
     *
     * @var bool
     */
    protected $recurringIndefinitely;

    /**
     * Method ID to set for recurring payment.
     *
     * @var mixed
     */
    protected $recurringMethodId;

    /**
     * Optional recurring billing amount.
     * If not specified, the amount field will be used for
     * future recurring billing payments.
     *
     * @var mixed
     */
    protected $billamount;

    /**
     * Bill Tax var.
     *
     * @var mixed
     */
    protected $billtax;

    /**
     * Bill Source Key var.
     *
     * @var mixed
     */
    protected $billsourcekey;

    /**
     * Billing Fields.
     *
     * @var string
     */
    protected $billfname;

    /**
     * Billname var.
     *
     * @var string
     */
    protected $billlname;

    /**
     * Bill Company var.
     *
     * @var string
     */
    protected $billcompany;

    /**
     * @var Shipping RouteFees
     */
    protected $shippingRouteFees;

    /**
     * Bill Street var.
     *
     * @var string
     */
    protected $billstreet;

    /**
     * Bill Street 2 var.
     *
     * @var string
     */
    protected $billstreet2;

    /**
     * Bill City var.
     *
     * @var string
     */
    protected $billcity;

    /**
     * Bill State var.
     *
     * @var string
     */
    protected $billstate;

    /**
     * Shipping Methods Fields.
     */
    /**
     * Bill Zip var.
     *
     * @var mixed
     */
    protected $billzip;

    /**
     * Bill Country var.
     *
     * @var string
     */
    protected $billcountry;

    /**
     * Billed Phone var.
     *
     * @var mixed
     */
    protected $billphone;

    /**
     * Email var.
     *
     * @var string
     */
    protected $email;

    /**
     * Billed Fax.
     *
     * @var mixed
     */
    protected $fax;

    /**
     * Website var.
     *
     * @var mixed
     */
    protected $website;

    /**
     * Type of delivery method ('ship','pickup','download').
     *
     * @var string
     */
    protected $delivery;

    /**
     * Ship Name.
     *
     * @var string
     */
    protected $shipfname;

    /**
     * Ship Last Name.
     *
     * @var string
     */
    protected $shiplname;

    /**
     * Ship Compnay var.
     *
     * @var string
     */
    protected $shipcompany;

    /**
     * Shipping Street.
     *
     * @var string
     */
    protected $shipstreet;

    /**
     * Shipping Street 2.
     *
     * @var string
     */
    protected $shipstreet2;

    /**
     * Shipping City.
     *
     * @var string
     */
    protected $shipcity;

    /**
     * Shipping State.
     *
     * @var string
     */
    protected $shipstate;

    /**
     * Shipping Zip.
     *
     * @var mixed
     */
    protected $shipzip;

    /**
     * Shipping country.
     *
     * @var string
     */
    protected $shipcountry;

    /**
     * Shipping Phone.
     *
     * @var mixed
     */
    protected $shipphone;

    /**
     * Line items - see addLine().
     *
     * @var mixed
     */
    protected $lineitems = [];

    /**
     * Line items for tokenization - see addLineItem().
     *
     * @var mixed
     */
    protected $lineItems;

    /**
     * Additional transaction details or comments
     * (free form text field supports up to 65,000 chars).
     *
     * @var string
     */
    protected $comments;

    /**
     * Allows developers to identify their application to the
     * gateway (for troubleshooting purposes).
     *
     * @var string
     */
    protected $software = 'Magento2';

    /**
     * Raw result from gateway.
     *
     * @var mixed
     */
    protected $rawresult;

    /**
     * Full result:  Approved, Declined, Error.
     *
     * @var mixed
     */
    protected $result = 'Error';

    /**
     * Abbreviated result code: A|D|E.
     *
     * @var mixed
     */
    protected $resultcode = 'E';

    /**
     * Authorization code.
     *
     * @var mixed
     */
    protected $authcode;

    /**
     * Reference number.
     *
     * @var mixed
     */
    protected $refnum;

    /**
     * Batch number.
     *
     * @var mixed
     */
    protected $batch;

    /**
     * AVS result var.
     *
     * @var mixed
     */
    protected $avs_result;

    /**
     * AVS result code var.
     *
     * @var mixed
     */
    protected $avs_result_code;

    /**
     * Obsolete avs result.
     *
     * @var mixed
     */
    protected $avs;

    /**
     * cvv2 result var.
     *
     * @var mixed
     */
    protected $cvv2_result;

    /**
     * cvv2 result code var.
     *
     * @var mixed
     */
    protected $cvv2_result_code;

    /**
     * vpas result code var.
     *
     * @var mixed
     */
    protected $vpas_result_code;

    // Cardinal Response Fields
    /**
     * system identified transaction as a duplicate.
     *
     * @var bool
     */
    protected $isduplicate;

    /**
     * Transaction amount after server has
     * converted it to merchants currency.
     *
     * @var mixed
     */
    protected $convertedamount;

    /**
     * Merchants currency.
     *
     * @var mixed
     */
    protected $convertedamountcurrency;

    // Errors Response Fields
    /**
     * The conversion rate that was used.
     *
     * @var mixed
     */
    protected $conversionrate;

    /**
     * Gateway assigned customer ref number
     * for recurring billing.
     *
     * @var mixed
     */
    protected $custnum;

    // For TranAPI Config import
    /**
     * Card auth url.
     *
     * @var mixed
     */
    protected $acsurl;

    /**
     * card auth request.
     *
     * @var mixed
     */
    protected $pareq;

    /**
     * Cardinal transid.
     *
     * @var mixed
     */
    protected $cctransid;

    /**
     * Error message if result is an error.
     *
     * @var mixed
     */
    protected $error = 'Transaction not processed yet.';

    /**
     * Numerical error code.
     *
     * @var mixed
     */
    protected $errorcode;

    protected ConfigFactory $configFactory;

    /**
     * @var PaymentConfig
     */
    protected $paymentConfig;

    /**
     * @var bool
     */
    protected $achStatus = false;

    /**
     * Ebiz Version var.
     *
     * @var string
     */
    protected $ebiz_version;

    /**
     * @var ClientFactory
     */
    protected $soapClientFactory;

    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollection;

    /**
     * Store Id var.
     *
     * @var null|int
     */
    protected $storeId = 0;

    /**
     * State.
     *
     * @var State
     */
    protected $appState;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Session
     */
    protected $sessionManager;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var RecurringFactory
     */
    protected $recurringFactory;

    /**
     * @var LocaleResolver
     */
    protected $localeResolver;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepositoryInterface;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * Shipping address for webform.
     */
    protected array $billingAddress;

    /**
     * Billing address for webform.
     */
    protected array $shippingAddress;

    /**
     * Batch_number.
     */
    protected string $batch_num = '';

    /**
     * Batch reference Number.
     */
    protected string $batch_ref_num = '';

    /**
     * Is Duplicate var.
     */
    protected bool $is_duplicate;

    /**
     * Card Level Result code var.
     */
    protected string $card_level_result_code = '';

    /**
     * Card Level Result var.
     */
    protected string $card_level_result = '';

    /**
     * Card Code Result var.
     */
    protected string $card_code_result = '';

    /**
     * Card Code Result Code var.
     */
    protected string $card_code_result_code = '';

    /**
     * Auth Code var.
     */
    protected string $auth_code = '';

    protected string $auth_amount = '0';

    /**
     * @var string
     */
    protected $allowPartialAuth = false;

    /**
     * @var bool
     */
    protected $custReceipt = false;

    protected string $custReceiptTemplate = '';

    protected string $custReceiptEmail = '';

    /**
     * @var bool
     */
    protected $merchReceipt = false;

    protected string $merchReceiptEmail = '';

    protected string $merchReceiptTemplate = '';

    protected bool $nonTax = false;

    /**
     * Duty Amount var.
     */
    // phpcs:ignore
    protected float|int $dutyAmount = 0;

    /**
     * Tip Amount var.
     */
    // phpcs:ignore
    protected float|int $tipAmount = 0;

    /**
     * Is Recurring var.
     */
    protected bool $isRecurring = false;

    /**
     * Ignore Duplicate var.
     */
    protected bool $ignoreDuplicate = false;

    /**
     * Ship Amount.
     */
    // phpcs:ignore
    protected float|int $shippingAmount = 0;

    /**
     * Payment Status var.
     */
    protected string $payment_status = '';

    /**
     * Transaction type.
     */
    protected string $transtype = '';

    /**
     * Payment Status code var.
     */
    protected string $payment_status_code = '';

    /**
     * Merchant supported transaction/payment types.
     */
    protected array $merchantTransactionInfo = [];

    /**
     * Refferred URL var.
     */
    protected ?string $refferedUrl = '';

    // phpcs:ignore
    protected float|int $totalOrderedQty = 0;

    /**
     * @var string
     */
    // protected string $soapWsdlUrl;

    protected SessionManagerInterface $coreSession;

    protected CheckoutSession $checkoutSession;

    protected ConvertOrder $orderConverter;

    protected InvoiceRepositoryInterface $invoiceRepository;

    protected OrderFactory $orderFactory;

    protected InvoiceService $invoiceService;

    protected Http $httpRequest;

    protected JsonFactory $jsonFactory;

    /**
     * @var null
     */
    protected $transactionResponseData;

    protected BackendSessionQuote $backendSessionQuote;

    protected Registry $registry;
    protected LoggerInterface $logger;

    protected PriceHelper $priceHelper;

    public function __construct(
        ResourceConnection          $resourceConnection,
        ClientFactory               $soapClientFactory,
        ConfigFactory               $configFactory,
        CustomerCollectionFactory   $customerCollection,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CustomerFactory             $customerFactory,
        LocaleResolver              $localeResolver,
        StoreManagerInterface       $storeManager,
        PaymentConfig               $paymentConfig,
        RedirectFactory             $redirectFactory,
        RecurringFactory            $recurringFactory,
        UrlInterface                $url,
        TimezoneInterface           $timezoneInterface,
        ResponseFactory             $responseFactory,
        ManagerInterface            $messageManager,
        CartRepositoryInterface     $cartRepositoryInterface,
        QuoteFactory                $quoteFactory,
        Json                        $json,
        Session                     $sessionManager,
        CustomerRegistry            $customerRegistry,
        CheckoutSession             $checkoutSession,
        RemoteAddress               $remoteAddress,
        EbizchargeLogger            $ebizchargeLogger,
        SessionManagerInterface     $coreSession,
        ConvertOrder                $orderConverter,
        OrderFactory                $orderFactory,
        InvoiceRepositoryInterface  $invoiceRepository,
        Cart                        $cart,
        JsonFactory                 $jsonFactory,
        Http                        $httpRequest,
        InvoiceService              $invoiceService,
        BackendSessionQuote         $backendSessionQuote,
        Registry                    $registry,
        PriceHelper                 $priceHelper
    )
    {
        // @var $soapClientFactory
        $this->soapClientFactory = $soapClientFactory;
        // @var $configFactory
        $this->configFactory = $configFactory;
        // @var $ebizchargeLogger
        $this->ebizchargeLogger = $ebizchargeLogger;
        // @var $paymentConfig
        $this->paymentConfig = $paymentConfig;
        // @var $ebiz_version
        $this->ebiz_version = 'Ebizcharge_Ebizcharge_' . self::EBIZCHARGE_VERSION;

        // phpcs:ignore
        if (isset($_SERVER['REMOTE_ADDR'])) {
            // phpcs:ignore
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }

        // @var $customerCollection
        $this->customerCollection = $customerCollection;
        // @var $storeManager
        $this->storeManager = $storeManager;

        // set Request Store Id
        // phpcs:ignore
        // $this->setRequestStoreId();

        // @var $customerRepositoryInterface
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        // @var $json
        $this->json = $json;
        // @var $resourceConnection
        $this->resourceConnection = $resourceConnection;
        // @var $customerRegistry
        $this->customerRegistry = $customerRegistry;
        // @var $redirectFactory
        $this->redirectFactory = $redirectFactory;
        // @var $urlInterface
        $this->urlInterface = $url;
        // @var $responseFactory
        $this->responseFactory = $responseFactory;
        // @var $recurringFactory
        $this->recurringFactory = $recurringFactory;
        // @var $messageManager
        $this->messageManager = $messageManager;
        // @var $sessionManager
        $this->sessionManager = $sessionManager;
        // @var $customerFactory
        $this->customerFactory = $customerFactory;
        // @var $timezoneInterface
        $this->timezoneInterface = $timezoneInterface;
        // @var $localeResolver
        $this->localeResolver = $localeResolver;
        // @var $remoteAddress
        $this->remoteAddress = $remoteAddress;

        // @var $cart
        $this->cart = $cart;
        // @var  cartRepositoryInterface
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        // @var $quoteFactory
        $this->quoteFactory = $quoteFactory;
        // @var $coreSession
        $this->coreSession = $coreSession;
        // @var $checkoutSession
        $this->checkoutSession = $checkoutSession;
        // @var $orderConverter
        $this->orderConverter = $orderConverter;
        // invoice Repository Interface
        // @var $invoiceRepository
        $this->invoiceRepository = $invoiceRepository;
        // @var $orderFactory
        $this->orderFactory = $orderFactory;
        // @var $invoiceService
        $this->invoiceService = $invoiceService;
        // @var $httpRequest
        $this->httpRequest = $httpRequest;
        // @var $jsonFactory
        $this->jsonFactory = $jsonFactory;
        // @var $backendSessionQuote
        $this->backendSessionQuote = $backendSessionQuote;
        // @var $registry
        $this->registry = $registry;

        // To Set SOAP WSDL URL
        // $this->setWsdlUrl();

        // @var $priceHelper
        $this->priceHelper = $priceHelper;

        // Bank Account Types
        $this->bankAccountTypes = [
            self::EBIZCHARGE_PAYMENT_ACH_TYPE_CHECKING => __('Checking'),
            self::EBIZCHARGE_PAYMENT_ACH_TYPE_SAVINGS => __('Savings'),
        ];
    }

    /**
     * Gateway Base URL
     *
     * @return string
     */
    public function getGatewayBaseUrl(): string
    {
        return self::EBIZCHARGE_SOAP_API_GATEWAY_URL;
    }

    /**
     * Get Division Id.
     *
     * @param null|mixed $storeId
     * @throws NoSuchEntityException
     */
    public function getDivisionId(mixed $storeId = "0"): string
    {
        $storeId = null !== $storeId ? $storeId : $this->getStoreId();
        // $instancePrefix = self::EBIZCHARGE_DIVISION_ID;
        // return $instancePrefix;
        return $this->configFactory->create()->getDivisionID($storeId);
    }

    /**
     * EBizCharge Logger.
     *
     * @param mixed $message
     * @param mixed $level
     * @return bool
     */
    public function cronlog(mixed $message = "", mixed $level = "0")
    {
        // logging to the logger
        return $this->ebizchargeLogger->addInfo($message);
    }

    /**
     * Get Store ID
     *
     * @return int|string
     * @throws NoSuchEntityException
     */
    public function getStoreId(){
        return $this->configFactory->create()->getStoreId() ?? "0";
    }

    /**
     * Run Customer payment transaction for One ResultCode.
     *
     * @throws LocalizedException
     */
    public function validateSecurityKey()
    {
        try {
            $storeId = $this->getStoreId();
            $transaction = $this->getClient($storeId)->SearchCustomers(
                [
                    'securityToken' => $this->getUeSecurityToken($storeId),
                    'start' => self::EBIZCHARGE_DEFAULT_REQUEST_START_LIMIT,
                    'limit' => 1,
                ]
            );

            if (!empty($transaction->SearchCustomersResult)) {
                return 'valid';
            }

            return 'invalid';
        } catch (Exception $ex) {
            // logging the error to the logs
            $this->ebizchargeLogger->addError('SoapFault occurred : ' . $ex->getMessage());

            throw new LocalizedException(__('SoapFault occurred : ' . $ex->getMessage()));
        }
    }

    /**
     * Search customer by local magento id.
     *
     * @param mixed $mageCustomerId
     * @return string 'Found' or 'Not Found'
     * @throws LocalizedException
     */
    public function searchCustomers(mixed $mageCustomerId = "")
    {
        $mappedCustomerId = $this->getMappedCustomerId($mageCustomerId);
        $storeId = $this->getStoreId();

        try {
            $searchCustomer = $this->getClient($storeId)->SearchCustomers(
                [
                    'securityToken' => $this->getUeSecurityToken($this->getCustomerStoreId($mageCustomerId)),
                    'customerId' => $mappedCustomerId,
                    'start' => self::EBIZCHARGE_DEFAULT_REQUEST_START_LIMIT,
                    'limit' => self::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT,
                ]
            );

            if (
                !isset($searchCustomer->SearchCustomersResult->Customer)
                || empty($searchCustomer->SearchCustomersResult->Customer)
            ) {
                $ebzcCustomer = 'Not Found';
                $this->ebizLog('Customer ' . $mappedCustomerId . ' Not Found');
            } else {
                $ebzcCustomer = $searchCustomer->SearchCustomersResult->Customer;
            }
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__('Error in search customer ' . $ex->getMessage()));
            throw new LocalizedException(__('searchCustomers ' . $ex->getMessage()));
        }

        return $ebzcCustomer;
    }

    /**
     * Get Mapped Customer Id.
     *
     * Return Econnect customer id if found in
     * mapping else return local magento customer id
     *
     * @param mixed $magCustomerId
     * @return mixed
     */
    public function getMappedCustomerId(mixed $magCustomerId = "")
    {
        try {
            $customerData = $this->customerRegistry->retrieve($magCustomerId)->getData();
            $eConnectId = $customerData['ec_cust_id'] ?? null;
            return !empty($eConnectId) ? $eConnectId : $magCustomerId;
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(__('Exception occured ' . $e->getMessage()));

            return $magCustomerId;
        }
    }

    /**
     * Get Data.
     *
     * @return $this|void
     * @throws Exception
     */
    public function getData(?string $property = null)
    {
        try {
            if (property_exists($this, $property)) {
                return $this->{$property};
            }
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(__('Exception occurred fetching the property. Error: '
                . $e->getMessage()));
            throw new Exception('Requested property do not exists.');
        }
    }

    /**
     * Get SOAP Client
     *
     * @param mixed $callFor
     * @param array $params
     * @return SoapClient|null
     * @throws NoSuchEntityException
     */
    public function getClient(mixed $callFor = '', array $params = []): ?SoapClient
    {
        $configFactory = $this->configFactory->create();
        $store = $configFactory->getStore();
        $storeId = $this->httpRequest->getParam("store") ?? $store->getId();

        $isEbizchargeEnabled = $configFactory->isActive($storeId);

        $ebizGatewayKey = $configFactory->getSourceKey($storeId);
        $ebizUsername = $configFactory->getSourceId($storeId);
        $ebizUserPin = $configFactory->getSourcePin($storeId);

        if ($configFactory->isPostFixExits($ebizGatewayKey)) {
            $ebizGatewayKey = $configFactory->decryptWithPostFix($ebizGatewayKey);
        }
        if ($configFactory->isPostFixExits($ebizUsername)) {
            $ebizUsername = $configFactory->decryptWithPostFix($ebizUsername);
        }
        if ($configFactory->isPostFixExits($ebizUserPin)) {
            $ebizUserPin = $configFactory->decryptWithPostFix($ebizUserPin);
        }

        if (count($params) > 0) {
            $isEbizchargeEnabled = $params["isActive"] ?? $configFactory->isActive($storeId);
            $ebizGatewayKey = $params["SecurityId"] ?? $configFactory->getSourceKey($storeId);
            $ebizUsername = $params["UserId"] ?? $configFactory->getSourceId($storeId);
            $ebizUserPin = $params["Password"] ?? $configFactory->getSourcePin($storeId);
        }

        if (!$ebizGatewayKey || !$ebizUsername || !$ebizUserPin) {
            $this->ebizchargeLogger->addInfo(__('EBizCharge payment hub is not active.'));
            return null;
        }

        if ('3dSecure' === $callFor) {
            try {
                // adding transactions to API
                $this->initTransactionAPI();

                /** Soap Gateway URL */
                $wsdlUrl = SoapApiModelInterface::EBIZCHARGE_SOAP_WSDL_3DSECURE_URL;
                $soapParams = $this->soapParams();

                $requestParams = $this->soapParams();

                // unsetting the stream resource
                unset($requestParams['stream_context']);
                // logging the request with the params
                $this->ebizchargeLogger->addInfo(
                    __('Preparing SoapClient url: ' . $wsdlUrl),
                    100,
                    $requestParams
                );

                /** Getting the ebizSoapClient */
                $ebizSoapClient = $this->soapClientFactory->create($wsdlUrl, $soapParams);

                // if soap client found
                if ($ebizSoapClient) {
                    $this->ebizchargeLogger->addInfo(__('Created SOAP client. '));
                } else {
                    $ebizSoapClient = null;
                }
            } catch (SoapFault $soapFault) {
                // phpcs:ignore
                $this->ebizchargeLogger->addCritical(
                    __('Error for WSDL URL:   Please check with your Internet connection OR Connection with
                    EBizCharge Payment Gateway, Error : ' . $soapFault->getMessage())
                );

                $ebizSoapClient = null;
            }

            return $ebizSoapClient;
        }

        /* checking if SOAP client is not
         * set to class create Soap Client
         * From Magento SOAP Api Client Factory
         */
        if ($this->getSoapClient() && $this->ebizSoapClient) {
            // phpcs:ignore
            $referredUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
            //  debug_print_backtrace();exit;

            if ($this->refferedUrl !== $referredUrl) {
                $this->ebizchargeLogger->addInfo('urL: ' . $referredUrl);
            }
            $this->refferedUrl = $referredUrl;

            return $this->ebizSoapClient;
        }

        if (!$this->ebizSoapClient) {
            try {
                // adding transactions to API
                $this->initTransactionAPI();

                $wsdlUrl = $this->getWsdlUrl();
                $soapParams = $this->soapParams();

                $requestParams = $this->soapParams();

                // unsetting the stream resource
                unset($requestParams['stream_context']);

                // @var ebizSoapClient
                $this->ebizSoapClient = $this->soapClientFactory->create($wsdlUrl, $soapParams);

                if (!$this->getSoapClient()) {
                    $this->setSoapClient($this->ebizSoapClient);
                    // logging the request with the params
                    $this->ebizchargeLogger->addInfo(
                        __('SOAP client: ' . $wsdlUrl),
                        100,
                        $requestParams
                    );
                }

                // if soap client found
                // phpcs:ignore
                if ($this->ebizSoapClient) {
                    // $this->ebizchargeLogger->addInfo(__('Created SOAP client. '));
                } else {
                    $this->ebizSoapClient = null;
                    $this->ebizchargeLogger->addInfo(__('Error creating SOAP client. '));
                }

                return $this->ebizSoapClient;
            } catch (SoapFault $soapFault) {
                // phpcs:ignore
                $this->ebizchargeLogger->addCritical(__('Error for WSDL URL:   Please check with your Internet connection OR Connection with EBizCharge Payment Gateway, Error : ' . $soapFault->getMessage()));
                //  $this->messageManager->getMessages(true);
                // $this->messageManager->addErrorMessage(__('Error Occurred during fetching WSDL URL: "' . $wsdlUrl .
                // '" Please check with your Internet connection OR Connection with EBizCharge Payment Gateway'));

                $this->ebizSoapClient = null;
            }
        }

        if (!$this->ebizSoapClient) {
            $errorMsg = __('503-Exception occurred during creating connection with EBizCharge Gateway');
            $this->messageManager->addErrorMessage($errorMsg);
        }
        return $this->ebizSoapClient;
    }


    /**
     * Init Transaction API.
     *
     * @param mixed $storeId
     * @return $this
     */
    public function initTransactionAPI(mixed $storeId = "0")
    {
        if (!$this->key) {
            // @var  key
            $this->key = $this->configFactory->create()
                ->getSourceKey($storeId ?? $this->getRequestStoreId($storeId));
            // @var  userid
            $this->userid = $this->configFactory->create()
                ->getSourceId($storeId ?? $this->getRequestStoreId($storeId));
            // @var  pin
            $this->pin = $this->configFactory->create()
                ->getSourcePin($storeId ?? $this->getRequestStoreId($storeId));

            // Transaction is intialized and logged to logger
            // $this->ebizchargeLogger->addInfo(__("Initialized EBizCharge keys for token."));
        }

        return $this;
    }

    /**
     * Get Request Store Id.
     *
     * This method returns the current request store id
     * Only useful in the context of payment transaction
     *
     * @return null|int
     */
    private function getRequestStoreId()
    {
        return $this->storeId;
    }

    /**
     * SOAP Parmas.
     *
     * @return array
     */
    public function soapParams()
    {
        // @var creating stream $context
        if (PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(false);
        }

        return [
            'trace' => self::EBIZCHARGE_SOAP_WSDL_TRACE,
            'exceptions' => self::EBIZCHARGE_SOAP_WSDL_EXCEPTIONS,
            'cache_wsdl' => self::EBIZCHARGE_SOAP_WSDL_CACHE,
            'connection_timeout' => self::EBIZCHARGE_SOAP_WSDL_CONNECTION_TIMEOUT,
            // phpcs:ignore
            'stream_context' => stream_context_create(
                [
                    'ssl' => [
                        'verify_peer' => self::EBIZCHARGE_SOAP_WSDL_SSL_VERIFY_PEER,
                        'verify_peer_name' => self::EBIZCHARGE_SOAP_WSDL_SSL_VERIFY_PEER_NAME,
                        'allow_self_signed' => self::EBIZCHARGE_SOAP_WSDL_SSL_ALLOW_SELF_SIGNED,
                    ],
                ]
            ),
        ];
    }

    /**
     * Set SOAP Wsdl Url.
     */
    /*public function setWsdlUrl($callFor = '')
    {
        if ($callFor == '3dSecure') {
            $this->soapWsdlUrl = SoapApiModelInterface::EBIZCHARGE_SOAP_WSDL_3DSECURE_URL;
        } else {
            $this->soapWsdlUrl = SoapApiModelInterface::EBIZCHARGE_SOAP_WSDL_API_GATEAY_PRODUCTION_URL;
        }
    }*/

    /**
     * Get Soap Client.
     *
     * @return mixed
     */
    public function getSoapClient()
    {
        $this->coreSession->start();

        return $this->coreSession->getSoapClient();
    }

    /**
     * Get Wsdl URL.
     *
     * @return string
     */
    public function getWsdlUrl()
    {
        // return $this->soapWsdlUrl;
        return self::EBIZCHARGE_SOAP_WSDL_API_GATEAY_PRODUCTION_URL; // production
        // return 'https://ebizsoapapidev1.azurewebsites.net/eBizService.svc?singleWsdl'; // dev
    }

    /**
     * Set Soap Client
     *
     * @param mixed $paramValues
     * @return void
     */
    public function setSoapClient(mixed $paramValues = "")
    {
        $this->coreSession->start();
        $this->coreSession->setSoapClient($paramValues);
    }

    /**
     * Get UE Security Token
     *
     * @param mixed $storeId
     * @return array
     */
    public function getUeSecurityToken(mixed $storeId = "0"): array
    {
        $this->initTransactionAPI($storeId);

        return [
            'SecurityId' => $this->key,
            'UserId' => $this->userid,
            'Password' => $this->pin,
        ];
    }

    /**
     * Get Customer Store Id
     *
     * @param mixed $customerId
     * @return int|null
     */
    public function getCustomerStoreId(mixed $customerId = "")
    {
        try {
            return $this->customerRepositoryInterface->getById($customerId)->getStoreId();
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(__('Exception occurred ' . $e->getMessage()));

            return null;
        }
    }

    /**
     * EBizCharge Logger.
     *
     * @param mixed $logString
     * @return bool
     */
    public function ebizLog(mixed $logString = "")
    {
        return $this->ebizchargeLogger->addInfo($logString);
    }

    /**
     * Get Total Ordered Qty
     *
     * @return float|int
     */
    public function getTotalOrderedQty()
    {
        return $this->totalOrderedQty;
    }

    /**
     * Get Email Templates
     *
     * @param mixed $totalOrderedQty
     * @return void
     */
    public function setTotalOrderedQty(mixed $totalOrderedQty = "0")
    {
        $this->totalOrderedQty = $totalOrderedQty;
    }

    /**
     * UnsSoap Client.
     *
     * @return mixed
     */
    public function unsSoapClient()
    {
        $this->coreSession->start();
        return $this->coreSession->unsSoapClient();
    }

    /**
     * Get Default From Date
     *
     * @param mixed $variation
     * @param mixed $format
     * @return string
     * @throws DateMalformedStringException
     */
    public function getDefaultFromDate(mixed $variation = '', mixed $format = 'Y-m-d')
    {
        return $this->getStoreDefaultDateTime($variation, $format);
    }

    /**
     * Get Store Default Date Time
     *
     * @param mixed $variation
     * @param mixed $format
     * @return string
     * @throws DateMalformedStringException
     */
    public function getStoreDefaultDateTime(mixed $variation = '', mixed $format = 'Y-m-d')
    {
        $variation = '' != $variation ? $variation : '+0 minutes';
        $currentDateTime = date($format);
        $currentDateTime = date($format, strtotime($variation, strtotime($currentDateTime)));
        // For convert date time According to Magento
        return $this->timezoneInterface
            ->date(new DateTime($currentDateTime))
            ->format($format);
    }

    /**
     * Get Default To Date
     *
     * @param mixed $variation
     * @param mixed $format
     * @return string
     * @throws Exception
     */
    public function getDefaultToDate(mixed $variation = '', mixed $format = 'Y-m-d')
    {
        // @var $variation
        return $this->getStoreDefaultDateTime($variation, $format);
    }

    /**
     * Get Email Templates
     *
     * @param array $soapPrams
     * @return array
     * @throws NoSuchEntityException
     */
    public function getEmailTemplates(array $soapPrams = [])
    {
        $response = [
            'error' => true,
            'templates' => [],
            'message' => false,
        ];

        try {
            $storeId = $this->getStoreId();
            $soapClient = $this->getClient($storeId);

            $emailTemplatesResponse = $soapClient->GetEmailTemplates($soapPrams);

            if ($emailTemplatesResponse->GetEmailTemplatesResponse) {
                $response = [
                    'error' => false,
                    'templates' => $emailTemplatesResponse->GetEmailTemplatesResponse->GetEmailTemplatesResult,
                    'message' => 'Success, email templates found',
                ];

                return $response;
            }
            $response['message'] = __('Error occurred during fetching Email Templates');

            return $response;
        } catch (SoapFault $soapFault) {
            $soapMessage = __('Exception occurred during fetching email templates Exception: '
                . $soapFault->getMessage());
            $this->ebizchargeLogger->addCritical($soapMessage);
            $response['message'] = $soapMessage;

            return $response;
        }
    }

    /**
     * Get EBizCharge Customer By Customer Id.
     *
     * @param mixed $ebizchargeCustomerId
     * @return array
     */
    public function getEbizchargeCustomerById(mixed $ebizchargeCustomerId = "0")
    {
        $ebizOrder = [];
        if (!$ebizchargeCustomerId) {
            return $ebizOrder;
        }

        try {
            $ebizchargeCustomerParams = [
                'customer_id' => $ebizchargeCustomerId,
                'customer_internal_id' => '',
            ];

            /** Get Customer by Id */
            $ebizOrder = (array)$this->getEbizchargeCustomerByParams($ebizchargeCustomerParams);
        } catch (NoSuchEntityException $exception) {
            $this->ebizchargeLogger->addCritical(__('Exception occurred during fetching customer Error: '
                . $exception->getMessage()));
        }

        return $ebizOrder;
    }

    /**
     * Get EBizCharge Customer By Params
     *
     * @param array $ebizchargeCustomerParams
     * @return false
     * @throws NoSuchEntityException
     */
    public function getEbizchargeCustomerByParams(array $ebizchargeCustomerParams = [])
    {
        if (!is_array($ebizchargeCustomerParams) || 0 == count($ebizchargeCustomerParams)) {
            return false;
        }
        $customer = false;


        try {
            $ebizchargeCustomerId = $ebizchargeCustomerParams['customer_id'] ?? '';
            $ebizchargeCustomerInternalId = $ebizchargeCustomerParams['customer_internal_id'] ?? '';

            $storeId = $this->storeManager->getStore()->getId();
            $securityToken = $this->getUeSecurityToken($storeId);

            if ('' !== $ebizchargeCustomerId) {
                $this->ebizchargeLogger->addInfo(__(
                    'Fetching customer from EBizCharge Gateway with Customer Id: ' . $ebizchargeCustomerId
                ));
            } else {
                $this->ebizchargeLogger->addInfo(__(
                    'Fetching customer from EBizCharge Gateway with Customer Internal Id: '
                    . $ebizchargeCustomerInternalId
                ));
            }

            /** load customer Data from Ebizcharge Gateway */
            $customerData = [
                'securityToken' => $securityToken,
                'customerInternalId' => $ebizchargeCustomerInternalId,
                'customerId' => $ebizchargeCustomerId,
            ];

            $ebizchargeCustomer = $this->getClient($storeId)->GetCustomer($customerData);

            // if customer
            if ($ebizchargeCustomer) {
                $this->ebizchargeLogger->addInfo(__('Success Customer loaded from EBizCharge Gateway'));
                $customer = $ebizchargeCustomer->GetCustomerResult;
            }

            return $customer;
        } catch (SoapFault $soapFault) {
            $this->ebizchargeLogger->addCritical(__(
                'Exception occurred during loading Customer from EBizCharge By Id ' . $ebizchargeCustomerId
                . ' Error: ' . $soapFault->getMessage()
            ));

            return $customer = false;
        }
    }

    /**
     * Get Customer
     *
     * @param mixed $magCustomerId
     * @return null
     */
    public function getCustomer(mixed $magCustomerId = "")
    {
        try {
            $mappedCustomerId = $this->getMappedCustomerId($magCustomerId);
            $storeId = $this->getStoreId();
            $soapClient = $this->getClient($storeId);

            if ($soapClient) {
                $customer = $soapClient->GetCustomer(
                    [
                        'securityToken' => $this->getUeSecurityToken($this->getCustomerStoreId($magCustomerId)),
                        'customerId' => $mappedCustomerId,
                    ]
                );

                if (isset($customer->GetCustomerResult)) {
                    $this->ebizchargeLogger->addInfo(__(
                        'Success Customer found with EBizCharge Payment Gateway.'
                    ));

                    return $customer->GetCustomerResult;
                }
            } else {
                // logging to logger
                $this->ebizchargeLogger->addError(__('Method: ' . __METHOD__ . ' Error in getting customer '));
            }

            return null;
        } catch (Exception $ex) {
            // logging to logger
            $this->ebizchargeLogger->addError(__('Method: ' . __METHOD__
                . ' Error in getting customer ' . $ex->getMessage()));

            return null;
        }
    }

    /**
     * Get EBizCharge Customer By Internal Id.
     *
     * @param mixed $ebizchargeCustomerInternalId
     * @return bool
     */
    public function getEbizchargeCustomerByInternalId(mixed $ebizchargeCustomerInternalId = 0)
    {
        if (!$ebizchargeCustomerInternalId) {
            return false;
        }

        try {
            $ebizchargeCustomerParams = [
                'customer_internal_id' => $ebizchargeCustomerInternalId,
                'customer_id' => '',
            ];

            return $this->getEbizchargeCustomerByParams($ebizchargeCustomerParams);
        } catch (NoSuchEntityException $exception) {
            $this->ebizchargeLogger->addCritical(__('Exception occurred during fetching customer Error: '
                . $exception->getMessage()));

            return false;
        }
    }

    /**
     * Get Orders From EBizCharge By Customer
     *
     * @param array $soapParams
     * @return array
     * @throws NoSuchEntityException
     */
    public function getOrdersFromEbizchargeByCustomer(array $soapParams = []): array
    {
        $ordersObj = [];
        $storeId = $this->getStoreId();
        $securityToken = $this->getUeSecurityToken($storeId);

        // If no customer Id or Security Token empty array will be resulted
        if (!$securityToken) {
            return $ordersObj;
        }
        $storeId = $this->getStoreId();
        $customerId = isset($soapParams['customerId']) ? $soapParams['customerId'] : '';
        $maxSize = isset($soapParams['maxSize']) ? $soapParams['maxSize'] : self::EBIZCHARGE_DEFAULT_MAX_SIZE;
        $position = isset($soapParams['position']) ? $soapParams['position'] : self::EBIZCHARGE_DEFAULT_POSITION;
        $limit = isset($soapParams['limit']) ? $soapParams['limit'] : self::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT;

        /* Do Loop for MAX requests
         *  to fetch All orders of
         *  a customer from EBizCharge Gateway
         */
        do {
            /**
             * SOAP
             * Search Criteria request to Gateway.
             */
            $searchOrders = [
                'securityToken' => $securityToken,
                'customerId' => $customerId,
                'salesOrderNumber' => isset($soapParams['salesOrderNumber']) ? $soapParams['salesOrderNumber'] : '',
                'salesOrderInternalId' => $soapParams['salesOrderInternalId'] ?? '',
                'start' => $position,
                'limit' => $limit,
                'sort' => isset($soapParams['sort']) ? $soapParams['sort'] : self::EBIZCHARGE_DEFAULT_SORT_COLUMN,
                'includeItems' => $soapParams['includeItems'] ?? self::EBIZCHARGE_DEFAULT_INCLUDE_ITEMS,
                'filters' => [
                    'SearchFilter' => [
                        'FieldName' => $soapParams['FieldName'] ?? self::EBIZCHARGE_DEFAULT_FIELD_TYPE,
                        'ComparisonOperator' => $soapParams['ComparisonOperator']
                            ?? self::EBIZCHARGE_DEFAULT_EQUAL_NOT_OPERATOR,
                        'FieldValue' => $soapParams['ComparisonOperator'] ?? $this->getSoftwareId(),
                    ],
                ],
            ];

            /** search Orders $SearchSalesOrders */
            $SearchSalesOrders = $this->getClient($storeId)->SearchSalesOrders($searchOrders);

            // if search Orders at EBizCharge API Gateway
            if (!isset($SearchSalesOrders->SearchSalesOrdersResult->SalesOrder)) {
                $ordersObj = [];
                $resultCount = 0;
            } elseif (
                is_array($SearchSalesOrders->SearchSalesOrdersResult->SalesOrder)
                && count($SearchSalesOrders->SearchSalesOrdersResult->SalesOrder) > 1
            ) {
                $ebzcOrders = $SearchSalesOrders->SearchSalesOrdersResult->SalesOrder;
                $resultCount = count($SearchSalesOrders->SearchSalesOrdersResult->SalesOrder);
                $ordersObj = array_merge($ordersObj, $ebzcOrders);
            } else {
                $ordersObj = $SearchSalesOrders->SearchSalesOrdersResult;
                $resultCount = 1;
            }

            if ($resultCount < self::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT) {
                $maxSize = 1;
            }
            $position = $position + self::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT;
        } while (0 == $maxSize);

        // returning the Array of Orders
        $this->ebizchargeLogger->addInfo(__(
            'Total Orders found from EBizCharge Api Gateway Total Remote Orders: ' . count((array)$ordersObj)
        ));

        return (array)$ordersObj;
    }

    /**
     * Get Software Id
     *
     * @return string
     */
    public function getSoftwareId(): string
    {
        return self::EBIZCHARGE_MAGENTO_SOFTWARE;
    }

    /**
     * Clear lines.
     */
    public function clearLines()
    {
        $this->ebizchargeLogger->addInfo(__('Line items are cleared'));
        $this->lineItems = [];
    }

    /**
     * Verify that all required data has been set.
     *
     * @return string
     */
    public function checkData()
    {
        if (!$this->key) {
            $this->ebizchargeLogger->addInfo(__('Source Key is required'));

            return __('Source Key is required');
        }

        if (
            in_array(strtolower($this->command), [
                'quickcredit',
                'quicksale',
                'cc:capture',
                'cc:refund',
                'refund',
                'check:refund',
                'capture',
                'creditvoid',
            ])
        ) {
            if (!$this->refnum) {
                $this->ebizchargeLogger->addInfo(__('Reference Number is required'));

                return __('Reference Number is required');
            }
        } elseif ('svp' == strtolower($this->command)) {
            if (!$this->svpbank) {
                $this->ebizchargeLogger->addInfo(__('Bank ID is required'));

                return __('Bank ID is required');
            }

            if (!$this->svpreturnurl) {
                $this->ebizchargeLogger->addInfo(__('Return URL is required'));

                return __('Return URL is required');
            }

            if (!$this->svpcancelurl) {
                $this->ebizchargeLogger->addInfo(__('Cancel URL is required'));

                return __('Cancel URL is required');
            }
        } else {
            if (
                in_array(
                    strtolower($this->command),
                    ['check:sale', 'check:credit', 'check', 'checkcredit', 'reverseach']
                )
            ) {
                if (!$this->account) {
                    $this->ebizchargeLogger->addInfo(__('Account Number is required'));

                    return __('Account Number is required');
                }

                if (!$this->achroute) {
                    $this->ebizchargeLogger->addInfo(__('Routing Number is required'));

                    return __('Routing Number is required');
                }
            } else {
                if (!$this->magstripe) {
                    if (!$this->card) {
                        $this->ebizchargeLogger->addInfo(__('Credit Card Number is required ('
                            . $this->command . ')'));

                        return __('Credit Card Number is required (' . $this->command . ')');
                    }

                    if (!$this->exp) {
                        $this->ebizchargeLogger->addInfo(__('Expiration Date is required'));

                        return __('Expiration Date is required');
                    }
                }
            }

            $this->amount = preg_replace('/[^\d.]+/', '', $this->amount);

            if (!$this->amount) {
                $this->ebizchargeLogger->addInfo(__('Amount is required'));

                return __('Amount is required');
            }

            if (!$this->invoice && !$this->orderid) {
                $this->ebizchargeLogger->addInfo(__('Invoice number or Order ID is required'));

                return __('Invoice number or Order ID is required');
            }
        }

        return 0;
    }

    /**
     * Prepare Ordered Item Price.
     */
    // public function prepareOrderedItemPrice($orderItem = null)
    // {
    // $itemPriceDetail = Item
    // }

    /**
     * Set Transaction Data for Void, Cancel and Refund.
     *
     * @param InfoInterface $payment
     * @param bool $isRefund
     * @param array $paymentInfoParams
     * @throws NoSuchEntityException
     */
    public function setTransactionData(InfoInterface $payment, bool $isRefund = false, array $paymentInfoParams = [])
    {
        $order = $payment->getOrder();

        $this->refnum = $payment->getCcTransId();
        $storeId = $this->configFactory->create()->getStoreId() ?? 0;

        /*
         * if we refund
         * we will use last TransactionId
         *  to refund the amount
         */
        if ($isRefund && !empty($tnxId = $payment->getTransactionId())) {
            $tnxId = str_replace(
                '-' . TransactionInterface::TYPE_CAPTURE,
                '',
                $tnxId
            );
            $this->refnum = str_replace(
                '-' . TransactionInterface::TYPE_REFUND,
                '',
                $tnxId
            );
        }
        $this->orderComments = $order->getOrderComments() ?? '';
        $this->ip = $order->getRemoteIp() ?? $_SERVER['REMOTE_ADDR'];
        $this->grandtotal = (float)$order->getGrandTotal();
        $this->cardholder = $payment->getCcOwner() ?? '';
        $this->card = $payment->getCcNumber() ?? '';
        $this->routing = $payment->getAchRoute() ?? '';
        $this->achtype = isset($paymentInfoParams['ach_type']) ? $paymentInfoParams['ach_type'] : '';
        $this->exp = $payment->getCcExpMonth() . substr((string)($payment->getCcExpYear() ?? ''), 2, 2);
        $this->cvv2 = $payment->getCcCid() ?? '';
        $this->currency = $order->getOrderCurrencyCode()
            ?? $this->configFactory->create()->getStoreCurrency($storeId);
        $this->subtotal = (string)(str_replace(',', '', (string)$order->getSubtotal()) ?? '0');
        $this->discount = (float)($order->getDiscountAmount() ?? 0);
        $this->shippingAmount = (float)($order->getShippingAmount() ?? 0);
        $this->shipping = (float)($order->getShippingAmount() ?? 0);
        $this->tax = (float)($order->getTaxAmount() ?? 0);
        $this->merchantTransactionInfo = $payment->getAdditionalInformation() ?? [];
        $this->savedMethodId = $payment->getAdditionalInformation('ebzc_method_id') ?? '';
        $this->nontaxable = (float)$order->getTaxAmount() > 0 ? true : false;
        $this->auth_amount = (string)(float)($order->getGrandTotal() ?? '0');

        $this->shippingRouteFees = (float)($order->getRouteFee() ?? 0);

        if ($this->shippingRouteFees > 0) {
            $this->shipping = (float)$order->getShippingAmount() + (float)$order->getRouteFee() ?? 0;
            $this->shippingAmount = (float)$order->getShippingAmount() + (float)$order->getRouteFee() ?? 0;
        }

        $orderShippingAddress = $order->getShippingAddress();
        $orderBillingAddress = $order->getBillingAddress();
        $customerEmail = $order->getCustomerEmail() ? $order->getCustomerEmail() :
            $orderBillingAddress->getEmail() ?? '';
        $merchantEmail = $this->configFactory->create()->getMerchantEmail($storeId);
        $emailTemplates = $this->getCutomerReceiptTemplate() ?? null;

        $customerEmailTemplate = $emailTemplates ? $emailTemplates->TemplateName : '';
        $merchEmailTemplate = $emailTemplates ? $emailTemplates->TemplateName : '';

        $this->custReceiptEmail = $customerEmail;
        $this->custReceipt = true;
        $this->custReceiptTemplate = $customerEmailTemplate;

        $this->merchReceiptEmail = $merchantEmail;
        $this->merchReceipt = true;
        $this->merchReceiptTemplate = $merchEmailTemplate;

        /** setting customer receipt template $customerEmailTemplates */
        $customerEmailTemplates = $this->getCutomerReceiptTemplate();

        if (
            PaymentInterface::EBIZCHARGE_COMMAND_AUTHONLY === $this->command
            || PaymentInterface::EBIZCHARGE_COMMAND_SALE === $this->command
        ) {
            $this->subtotal = (string)(str_replace(',', '', (string)$order->getSubtotal()) ?? '0');
            $this->grandtotal = (float)$order->getGrandTotal();
            $this->amount = (string)(str_replace(',', '', (string)(float)$order->getTotalDue()) ?? '0');
            $this->totalOrderedQty = (float)($order->getTotalQtyOrdered() ?? 0);
            $this->discount = (float)$order->getDiscountAmount();
            $this->shippingAmount = (float)($order->getShippingAmount() ?? 0);
            $this->shipping = (float)($order->getShippingAmount() ?? 0);

            $this->shippingRouteFees = (float)($order->getRouteFee() ?? 0);

            if ($this->shippingRouteFees > 0) {
                $this->shipping = (float)$order->getShippingAmount() + (float)$order->getRouteFee() ?? 0;
                $this->shippingAmount = (float)$order->getShippingAmount() + (float)$order->getRouteFee() ?? 0;
            }
        } elseif (PaymentInterface::EBIZCHARGE_COMMAND_REFUND === $this->command) {
            $creditMemo = $payment->getCreditmemo();

            $this->subtotal = (string)(str_replace(',', '', (string)$creditMemo->getSubTotal()) ?? '0');
            $this->grandtotal = (float)$creditMemo->getGrandTotal();
            $this->amount = (float)$creditMemo->getGrandTotal();
            $this->totalOrderedQty = (float)$creditMemo->getTotalQty();
            $this->discount = (float)$creditMemo->getDiscountAmount();
            $this->shippingAmount = (float)($creditMemo->getShippingAmount() ?? 0);
            $this->shipping = (float)($creditMemo->getShippingAmount() ?? 0);
            $this->shippingRouteFees = (float)($creditMemo->getRouteFee() ?? 0);

            if ($this->shippingRouteFees > 0) {
                $this->shipping = (float)$creditMemo->getShippingAmount() + (float)$creditMemo->getRouteFee() ?? 0;
                $this->shippingAmount =
                    (float)$creditMemo->getShippingAmount() + (float)$creditMemo->getRouteFee() ?? 0;
            }
        } elseif (
            PaymentInterface::EBIZCHARGE_COMMAND_CAPTURE === $this->command
            || PaymentInterface::EBIZCHARGE_COMMAND_QUICK_SALE === $this->command
        ) {
            /** @var Invoice $currentInvoice */
            $currentInvoice = $this->registry->registry('current_invoice');
            if (!$currentInvoice) {
                $currentInvoice = $this->convertToInvoice($order);
            }

            if ($currentInvoice) {
                $surchargeAmount = floatval($currentInvoice->getEcSurchargeAmount());
                $totalAmount = (float)$currentInvoice->getGrandTotal() - $surchargeAmount;
                $this->subtotal = (string)(str_replace(',', '', (string)$currentInvoice->getSubTotal()) ?? '0');
                $this->grandtotal = (string)(str_replace(',', '', (string)$totalAmount) ?? '0');
                $this->amount = (string)(str_replace(',', '', (string)$totalAmount) ?? '0');
                $this->totalOrderedQty = (float)$currentInvoice->getTotalQty();
                $this->shippingAmount = (float)($currentInvoice->getShippingAmount() ?? 0);
                $this->shipping = (float)($currentInvoice->getShippingAmount() ?? 0);
                $this->discount = (float)($currentInvoice->getDiscountAmount() ?? 0);
                $this->tax = (float)$currentInvoice->getTaxAmount() > 0 ? (float)$currentInvoice->getTaxAmount() : 0;
                $this->shippingRouteFees = (float)($currentInvoice->getRouteFee() ?? 0);

                if ($this->shippingRouteFees > 0) {
                    $this->shipping =
                        (float)$currentInvoice->getShippingAmount() + (float)$currentInvoice->getRouteFee() ?? 0;
                    $this->shippingAmount =
                        (float)$currentInvoice->getShippingAmount() + (float)$currentInvoice->getRouteFee() ?? 0;
                }
            }
        }

        // Order detail
        if (!empty($order)) {
            $incrementId = $order->getIncrementId();
            $poNumber = $incrementId;
            $invoiceCollections = $order->getInvoiceCollection();
            $invoiceIncrementID = $incrementId;

            // Order get Invoice Collection
            if (count($invoiceCollections) > 0) {
                foreach ($invoiceCollections as $invoice) {
                    $invoiceIncrementID = $invoice->getIncrementId();
                }
            }

            $this->tax = (float)$order->getTaxAmount() > 0 ? (float)$order->getTaxAmount() : 0;

            /**
             * invoice Increment ID.
             */
            $invoiceIncrementID = $invoiceIncrementID ? $invoiceIncrementID : $incrementId;

            $this->orderid = $incrementId;
            $this->invoice = $invoiceIncrementID;
            $this->ponum = $poNumber;

            $this->custid = $order->getCustomerId() ?? '';
            $this->email = $order->getCustomerEmail() ?? '';

            $this->storeId = $order->getStoreId();

            // avs data
            list($avsStreet) = $order->getBillingAddress()->getStreet();

            $this->street = $avsStreet;
            $this->zip = $order->getBillingAddress()->getPostcode();
            $this->subtotal = (string)(str_replace(',', '', (string)$order->getSubTotal()) ?? '0');
            $this->discount = (float)($order->getDiscountAmount() ?? 0);
            $this->shippingAmount = (float)($order->getShippingAmount() ?? 0);
            $this->shipping = (float)($order->getShippingAmount() ?? 0);
            $this->shippingRouteFees = (float)($order->getRouteFee() ?? 0);

            if ($this->shippingRouteFees > 0) {
                $this->shipping = (float)$order->getShippingAmount() + (float)$order->getRouteFee() ?? 0;
                $this->shippingAmount = (float)$order->getShippingAmount() + (float)$order->getRouteFee() ?? 0;
            }

            if (
                isset($paymentInfoParams['ebzc_option_type'])
                && strtolower($paymentInfoParams['ebzc_option_type']) === strtolower(SoapApiModelInterface::ACH)
            ) {
                //  $this->subtotal = 0;
            }

            $this->setOrderBilling($order);
            $this->setOrderShipping($order);

            // Clear Line Items
            $this->clearLineItems();

            /**
             * All visible items.
             */
            $orderedItems = $order->getAllVisibleItems();

            // adding line items
            $this->addLineItem($orderedItems);
        }

        //  dump($this, $payment);
    }

    /**
     * Get order shipping address
     *
     * @return array
     */
    private function getShippingAddress(): array
    {
        return [
            'FirstName' => $this->shipfname,
            'LastName' => $this->shiplname,
            'Company' => $this->shipcompany,
            'Street' => $this->shipstreet,
            'Street2' => $this->shipstreet2,
            'City' => $this->shipcity,
            'State' => $this->shipstate,
            'Zip' => $this->shipzip,
            'Country' => $this->shipcountry,
            'Phone' => $this->shipphone,
            'Fax' => $this->fax,
            'Email' => $this->email,
        ];
    }

    /**
     * Get order billing address
     *
     * @return array
     */
    private function getBillingAddress(): array
    {
        return [
            'FirstName' => $this->billfname,
            'LastName' => $this->billlname,
            'Company' => $this->billcompany,
            'Street' => $this->billstreet,
            'Street2' => $this->billstreet2,
            'City' => $this->billcity,
            'State' => $this->billstate,
            'Zip' => $this->billzip,
            'Country' => $this->billcountry,
            'Phone' => $this->billphone,
            'Fax' => $this->fax,
            'Email' => $this->email,
        ];
    }

    /**
     * Get Customer Receipt Templates
     *
     * @param string $emailTemplate
     * @return mixed
     */
    public function getCutomerReceiptTemplate(string $emailTemplate = ''): mixed
    {
        if ('' === $emailTemplate) {
            $emailTemplate = $this->configFactory->create()->getEmailCustomerReceiptTemplate();
        }
        $emailTemplates = $this->customerFactory->create()->getEbizCustomerReceiptEmailTemplates();
        $selectedEmailTemplate = false;
        if (count($emailTemplates) > 0) {
            foreach ($emailTemplates as $currEmailTemplate) {
                if ($emailTemplate === $currEmailTemplate) {
                    $selectedEmailTemplate = $currEmailTemplate;
                } else {
                    $selectedEmailTemplate = $currEmailTemplate;
                }
            }
        }

        if (isset($selectedEmailTemplate->TemplateSource)) {
            $this->custReceipt = (int)$selectedEmailTemplate->TemplateSource;
            $this->merchReceipt = (int)$selectedEmailTemplate->TemplateSource;
        }
        if (isset($selectedEmailTemplate->TemplateName)) {
            $this->custReceiptTemplate = $selectedEmailTemplate->TemplateName;
        }

        return $selectedEmailTemplate;
    }

    /**
     * Convert to Invoice
     *
     * @param null|mixed $order
     * @return false|mixed
     */
    public function convertToInvoice(Order $order = null)
    {
        $currInvoice = false;
        $invoiceCollection = $order->getInvoiceCollection();
        if (count($invoiceCollection) > 0) {
            foreach ($invoiceCollection as $invoice) {
                if (Invoice::STATE_OPEN === $invoice->getState()) {
                    $currInvoice = $invoice;

                    break;
                }
            }
        }

        return $currInvoice;
    }

    /**
     * Add order billing info
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    public function setOrderBilling(\Magento\Sales\Model\Order $order = null)
    {
        $billing = $order->getBillingAddress();
        if (!empty($billing)) {
            $street = $billing->getStreet();
            $streetBill = $street[0];

            if (empty($billing->getStreet(2))) {
                $street2Bill = '';
            } else {
                $Street2 = $billing->getStreet(2);
                $street2Bill = $Street2[0];
            }

            $this->billfname = $billing->getFirstname();
            $this->billlname = $billing->getLastname();
            $this->billcompany = $billing->getCompany();
            $this->billstreet = $streetBill;
            $this->billstreet2 = $street2Bill;
            $this->billcity = $billing->getCity();
            $this->billstate = $billing->getRegion();
            $this->billzip = $billing->getPostcode();
            $this->billcountry = $billing->getCountryId();
            $this->billphone = $billing->getTelephone();

            if (null == $this->custid) {
                $this->custid = $billing->getCustomerId();
            }
        }
    }

    /**
     * Set order shipping info
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    public function setOrderShipping(\Magento\Sales\Model\Order $order = null)
    {
        $shipping = $order->getShippingAddress();
        if (!empty($shipping)) {
            $shippingStreet = $shipping->getStreet();
            $streetShip = $shippingStreet[0];

            if (empty($shipping->getStreet(2))) {
                $street2Ship = '';
            } else {
                $shippingStreet2 = $shipping->getStreet(2);
                $street2Ship = $shippingStreet2[0];
            }

            $this->shipfname = $shipping->getFirstname();
            $this->shiplname = $shipping->getLastname();
            $this->shipcompany = $shipping->getCompany();
            $this->shipstreet = $streetShip;
            $this->shipstreet2 = $street2Ship;
            $this->shipcity = $shipping->getCity();
            $this->shipstate = $shipping->getRegion();
            $this->shipzip = $shipping->getPostcode();
            $this->shipcountry = $shipping->getCountryId();
            $this->shipphone = $shipping->getTelephone();
        }
    }

    /**
     * Clear Line Items.
     */
    public function clearLineItems()
    {
        $this->ebizchargeLogger->addInfo(__('Line items are cleared for Payment Transaction.'));
        $this->lineItems = [];
    }

    /**
     * Add line items to the transaction.
     *
     * @param mixed $orderedItems
     * @return array|mixed
     */
    public function addLineItem(mixed $orderedItems = [])
    {
        /**
         * Line Items.
         */
        $lineItems = [];

        if (count($orderedItems) > 0) {
            foreach ($orderedItems as $item) {
                if (
                    (float)$item->getQtyOrdered() > 0
                    && in_array($item->getProductType(), $this->allowedProductTypes)
                ) {
                    if (0 !== (float)$item->getQtyInvoiced()) {
                        continue;
                    }
                }
                $itemName = $item->getName() ?? '';
                $itemSku = $item->getSku() ?? '';
                $itemID = $item->getQuoteItemId();
                $itemPriceDetail = $this->orderFactory->create()->prepareOrderedItemPrice($itemID);
                $invoicedQty = (float)($item->getQtyInvoiced() ?? 0);
                $qtyToInvoice = (float)($item->getQtyToInvoiced() ?? 0);
                $totalOrderedQty = (float)($item->getQtyOrdered() ?? 0);
                $isInvoice = $this->isToInvoice($orderedItems);

                if ($invoicedQty > 0) {
                    $qtyToInvoice = $invoicedQty;
                }

                // if invoice and to be invoiced quantity does not exists skip it
                if ($isInvoice && ($qtyToInvoice < 1 || $invoicedQty < 1)) {
                    continue;
                }

                $isTaxable = 0;
                $taxAmount = isset($itemPriceDetail['tax_amount']) ? (float)$itemPriceDetail['tax_amount'] : 0;

                if ($taxAmount > 0) {
                    $isTaxable = 1;
                }
                $itemDescription = $itemPriceDetail['description'] ?? '';
                $itemShortDescription = $itemPriceDetail['short_description'] ?? '';
                $productRefNumber = $itemPriceDetail['product_ref_num'] ?? '';
                $unitOfmeaure = $itemPriceDetail['unit_of_measure'] ?? 'lbs';
                $productWeight = $itemPriceDetail['weight'] ?? '0';

                if ($qtyToInvoice > 0 && $totalOrderedQty !== $qtyToInvoice) {
                    $price = (float)($item->getPrice() ?? 0);
                    $itemPriceDetail['price'] = (float)$price;
                    $taxPercentage = (float)(($item->getTaxPercent() / 100) ?? 0);
                    $taxAmount = $taxPercentage * $price * $qtyToInvoice ?? 0;
                    $taxAmount = number_format($taxAmount, 2);
                    $itemPriceDetail['qty'] = $qtyToInvoice ?? 0;
                    $itemPriceDetail['discount_amount'] = $item->getDiscountInvoiced() ?? 0;
                    if ((float)$taxAmount > 0) {
                        $isTaxable = 1;
                    }
                }

                // @var Product $product
                $lineItems[] = [
                    'SKU' => (string)$itemSku ?? '',
                    'ProductName' => (string)$itemName ?? '',
                    'Description' => (string)$itemDescription ?? '',
                    'ShortDescription' => (string)$itemShortDescription ?? '',
                    'ProductRefNum' => (string)$productRefNumber ?? '',
                    'UnitPrice' => isset($itemPriceDetail['price']) ? (string)(float)$itemPriceDetail['price'] : '0',
                    'Taxable' => (string)$isTaxable ?? '0',
                    'TaxAmount' => (string)$taxAmount ?? '',
                    'Weight' => (string)$productWeight ?? '',
                    'UnitOfMeasure' => (string)$unitOfmeaure ?? '',
                    'Qty' => isset($itemPriceDetail['qty']) ? (string)$itemPriceDetail['qty'] : '0',
                    'Id' => isset($itemPriceDetail['product_id']) ? (string)$itemPriceDetail['product_id'] : '0',
                    'DiscountAmount' => isset($itemPriceDetail['discount_amount'])
                        ? (string)(float)$itemPriceDetail['discount_amount'] : '0',
                    'DiscountRate' => isset($itemPriceDetail['discount_percent'])
                        ? (string)(float)$itemPriceDetail['discount_percent'] : '0',
                    'DiscountInvoiced' => isset($itemPriceDetail['discount_amount'])
                        ? (string)(float)$itemPriceDetail['discount_amount'] : '0',
                ];
            }
        }
        $this->ebizchargeLogger->addInfo(__(count($lineItems)
            . '- Line items has been added to to the Payment Transactions. '));
        // Assigned line items
        $this->lineItems = $lineItems;
        $this->lineitems = $lineItems;

        return $this->lineItems;
    }

    /**
     * Is to Invoice
     *
     * @param mixed $orderedItems
     * @return bool
     */
    public function isToInvoice(mixed $orderedItems = [])
    {
        $isToInvoice = false;

        if (count($orderedItems) > 0) {
            foreach ($orderedItems as $item) {
                if (
                    (float)$item->getQtyOrdered() > 0
                    && in_array($item->getProductType(), $this->allowedProductTypes)
                ) {
                    if (0 !== (float)$item->getQtyInvoiced()) {
                        continue;
                    }
                }
                $invoicedQty = (float)($item->getQtyInvoiced() ?? 0);

                if ($invoicedQty > 0) {
                    $isToInvoice = true;
                }
            }
        }

        return $isToInvoice;
    }

    /**
     * Run Update customer
     *
     * @param mixed $tableName
     * @param mixed $mageCustomerId
     * @param mixed $ebzcCustomerId
     * @return bool
     */
    public function runUpdateCustomer(
        mixed $tableName = "",
        mixed $mageCustomerId = "",
        mixed $ebzcCustomerId = ""
    ): bool
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName($tableName);

        $dataToUpdate = [
            'ebzc_cust_id' => $ebzcCustomerId,
        ];
        $where = [
            'mage_cust_id = ?' => $mageCustomerId,
        ];

        try {
            $connection->beginTransaction();
            $connection->update($tableName, $dataToUpdate, $where);
            $connection->commit();

            return true;
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(__('Exception occurred during sql query ' . $e->getMessage()));

            $connection->rollBack();

            return false;
        }
    }

    /**
     * Token Process.
     *
     * Tokenization customer checkout: Add customer
     * to gateway and process the transaction
     * a user is logged in option is new customer
     * + add payment method
     *
     * @param mixed $customerId
     * @param InfoInterface|null $paymentObj
     * @return false
     * @throws LocalizedException
     */
    public function tokenProcess(mixed $customerId = "", InfoInterface $paymentObj = null)
    {
        try {
            $searchCustomerResult = $this->searchCustomers($customerId);
            // Case 1 Local = No, Live = No
            if (('Not Found' === $searchCustomerResult) || (null === $searchCustomerResult)) {
                $this->addCustomerAndRunCustomerTransaction($customerId, $paymentObj);
            } else {
                $isRecurring = false;
                $this->runTransaction($paymentObj, $isRecurring);
            }
            // Case 3 Local = No, Live = Yes
            // Added on Payment.php line #404
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(__('Soap Fault: ' . $e->getMessage()));
            throw new LocalizedException(__('SoapFault: ' . $e->getMessage()));
        }

        return false;
    }

    /**
     * Add Customer And Run Customer Transaction.
     *
     * @param mixed $customerId
     * @param InfoInterface $paymentObj
     * @return bool
     * @throws LocalizedException
     */
    public function addCustomerAndRunCustomerTransaction(
        mixed         $customerId = "",
        InfoInterface $paymentObj = null
    ): bool
    {
        try {
            $customer = $this->customerFactory->create()->load($customerId);
            $ebizCustomer = null;
            if (!$customer->getEcCustToken()) {
                $storeId = $customer->getStoreId() ?? $this->getStoreId();
                $customerResult = $this->getClient($storeId)->AddCustomer(
                    [
                        'securityToken' => $this->getUeSecurityToken($storeId),
                        'customer' => $this->getCustomerData($customerId),
                    ]
                );
                $ebizCustomer = $customerResult->AddCustomerResult;
            }
            // add customer payment method
            $paymentMethodId = $this->addCustomerPaymentMethod(
                $customer->getEcCustInternalId(),
                $this->getCustomerPayment()
            );
            $this->setPaymentMethodId($paymentMethodId, $paymentObj);

            // GetCustomerToken by calling GetCustomerToken function
            $ebizCustomerNumber = $this->getCustomerToken($ebizCustomer->CustomerId);
            $paymentObj->setAdditionalInformation('ebzc_cust_id', $ebizCustomerNumber);

            // run Customer Transaction using saved payment method
            $this->savedTransactionToEbizcharge($ebizCustomerNumber, $paymentMethodId, $paymentObj);
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(__('Soap Fault: Exception occurred ' . $e->getMessage()));
            throw new LocalizedException(__('Soap Fault: Exception occurred : ' . __METHOD__
                . $e->getMessage()));
        }
        return false;
    }

    /**
     * Get Customer Data for Add New Customer.
     *
     * @param mixed $customerId
     * @return array
     */
    public function getCustomerData(mixed $customerId = ""): array
    {
        $customer = $this->customerFactory->create()->load($customerId);

        return [
            'CustomerId' => $customerId,
            'FirstName' => $this->billfname,
            'LastName' => $this->billlname,
            'CompanyName' => $this->billcompany,
            'Phone' => $this->billphone,
            'CellPhone' => $this->billphone,
            'Fax' => $this->fax,
            'Email' => $this->email,
            'WebSite' => $this->website,
            'ShippingAddress' => $this->getShippingAddressAddCust(),
            'BillingAddress' => $this->getBillingAddressAddCust(),
            'SoftwareId' => self::EBIZCHARGE_MAGENTO_SOFTWARE,
        ];
    }

    /**
     * New shipping for add customer.
     *
     * @return array
     */
    private function getShippingAddressAddCust(): array
    {
        return [
            'FirstName' => $this->shipfname,
            'LastName' => $this->shiplname,
            'Company' => $this->shipcompany,
            'Address1' => $this->shipstreet,
            'Address2' => $this->shipstreet2,
            'City' => $this->shipcity,
            'State' => $this->shipstate,
            'ZipCode' => $this->shipzip,
            'Country' => $this->shipcountry,
            'Phone' => $this->shipphone,
            'Fax' => $this->fax,
            'Email' => $this->email,
        ];
    }

    /**
     * New billing address for add customer.
     *
     * @return array
     */
    private function getBillingAddressAddCust(): array
    {
        return [
            'FirstName' => $this->billfname,
            'LastName' => $this->billlname,
            'Company' => $this->billcompany,
            'Address1' => $this->billstreet,
            'Address2' => $this->billstreet2,
            'City' => $this->billcity,
            'State' => $this->billstate,
            'ZipCode' => $this->billzip,
            'Country' => $this->billcountry,
            'Phone' => $this->billphone,
            'Fax' => $this->fax,
            'Email' => $this->email,
        ];
    }

    /**
     * Add Customer Payment Method.
     *
     * @param mixed $customerInternalId
     * @param array $parameters
     * @return array
     * @throws NoSuchEntityException
     */
    public function addCustomerPaymentMethod(mixed $customerInternalId = "", array $parameters = []): array
    {
        $paymentMethodResponse = [
            'error' => true,
            'message' => __('Error occurred during adding customer Payment Method.'),
            'method_id' => 0,
        ];

        try {
            $storeId = $this->configFactory->create()->getStoreId();
            $paymentMethodParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'customerInternalId' => $customerInternalId,
                'paymentMethodProfile' => $parameters,
            ];
            /**  sending payment Method Request $paymentMethod */
            $paymentMethod = $this->getClient($storeId)->addCustomerPaymentMethodProfile($paymentMethodParams);

            if (isset($paymentMethod->AddCustomerPaymentMethodProfileResult)) {
                $paymentMethodId = $paymentMethod->AddCustomerPaymentMethodProfileResult;

                $paymentMethodResponse['error'] = false;
                $paymentMethodResponse['method_id'] = $paymentMethodId;
                $paymentMethodResponse['message'] = __(
                    'Success, the Payment method has been added with EBizCharge Gateway.'
                );
            }
        } catch (SoapFault $soapFault) {
            $paymentMethodResponse['message'] = __('Exception occurred during adding Payment Method Error: '
                . $soapFault->getMessage());
            $this->ebizchargeLogger->addCritical(__('Exception occurred during adding Payment Method Error: '
                . $soapFault->getMessage()));
        }

        return $paymentMethodResponse;
    }

    /**
     * Get Customer Payment data.
     *
     * @return array
     */
    public function getCustomerPayment(): array
    {
        $paymentTypes = $this->paymentConfig->getCcTypes();
        $cardType = $this->cardtype;

        if (strtolower($cardType) !== strtolower(SoapApiModelInterface::ACH)) {
            foreach ($paymentTypes as $code => $text) {
                if ($code == $this->cardtype) {
                    $cardType = $text;
                }
            }

            $paymentMethod = [
                'MethodName' => $cardType . ' ' . substr((string)$this->card, -4) . ' - ' . $this->cardholder,
                // . ' - Expires on: ' . $this->exp,
                'AccountHolderName' => $this->cardholder ?? '',
                'SecondarySort' => 1,
                'Created' => date('Y-m-d\TH:i:s'),
                'Modified' => date('Y-m-d\TH:i:s'),
                'AvsStreet' => $this->billstreet,
                'AvsZip' => $this->billzip,
                'CardCode' => $this->cvv2,
                'CardExpiration' => $this->exp,
                'CardNumber' => $this->card,
                'CardType' => $this->cardtype,
                'Balance' => $this->amount,
                'MaxBalance' => $this->amount,
            ];
        } else {
            $paymentMethod = [
                'MethodName' => ucwords($this->achtype) . ' ' . substr((string)$this->card, -4)
                    . ' - ' . $this->cardholder,
                'SecondarySort' => 1,
                'Created' => date('Y-m-d\TH:i:s'),
                'Modified' => date('Y-m-d\TH:i:s'),
                'Account' => $this->card,
                'AccountType' => $this->achtype,
                'AccountHolderName' => $this->cardholder ?? '',
                'Routing' => $this->achroute,
                'MethodType' => self::ACH,
                'Balance' => $this->amount,
                'MaxBalance' => $this->amount,
            ];
        }

        return $paymentMethod;
    }

    /**
     * Set customer payment id in.
     * Magento payment object
     *
     * @param mixed $methodId
     * @param InfoInterface|null $paymentObj
     * @return void
     */
    private function setPaymentMethodId(mixed $methodId = "", InfoInterface $paymentObj = null)
    {
        $paymentObj->setEbzcMethodId($methodId);
        $paymentObj->setAdditionalInformation('ebzc_method_id', $methodId);
        // Logging Method id
        $this->ebizchargeLogger->addInfo(__('Payment Method is saved to the customer : ' . $methodId));
        // @var  savedMethodId
        $this->savedMethodId = $methodId;
        $this->recurringMethodId = $methodId;
    }

    /**
     * Get customer token.
     *
     * @param mixed $magCustomerId
     * @return mixed
     */
    public function getCustomerToken(mixed $magCustomerId = "")
    {
        if ($customer = $this->getCustomer($magCustomerId)) {
            return $customer->CustomerToken;
        }

        return null;
    }

    /**
     * Saved Transaction To EBizCharge.
     * Customer Token and save Transactions
     *
     * @param mixed $ebzcCustomerToken
     * @param mixed $ebzcMethodId
     * @param InfoInterface $payment
     * @param bool $partialAuth
     * @return bool
     * @throws LocalizedException
     */
    public function savedTransactionToEbizcharge(
        mixed         $ebzcCustomerToken = "",
        mixed         $ebzcMethodId = "",
        InfoInterface $payment = null,
        bool          $partialAuth = false
    ): bool
    {
        try {
            $storeId = $this->getStoreId();
            $transactionParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'custNum' => $ebzcCustomerToken,
                'paymentMethodID' => $ebzcMethodId,
                'tran' => $this->getCustomerTransactionRequest($payment),
            ];
            $storeId = $this->getStoreId();
            $transactionResult = $this->getClient($storeId)->runCustomerTransaction($transactionParams);
            $transaction = $transactionResult->runCustomerTransactionResult;

            if (isset($transaction)) {
                $transactionApproved = $this->setTransactionResult($transaction);

                // running adding recurrings
                if (
                    $transactionApproved
                    && 1 == $this->configFactory->create()->isRecurringEnabled()
                    && !$partialAuth
                ) {
                    // saving recurring through Recurring Mobel Factory to Local Database
                    //  $this->recurringFactory->create()->prepareParamsaddRecurringOrder($payment);
                    // old method
                    $this->runRecurring($payment);
                }

                return $transactionApproved;
            }
        } catch (SoapFault $soapFault) {
            // logging error
            $this->ebizchargeLogger->addCritical(__(' Exception occurred during saving Transaction Error: '
                . $soapFault->getMessage()));

            throw new LocalizedException(__(
                ' Exception occurred during savedTransaction to Ebizcharge Error: ' . $soapFault->getMessage()
            ));
        }

        return false;
    }

    /**
     * Get Customer transaction request
     *
     * @param InfoInterface|null $payment
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCustomerTransactionRequest(InfoInterface $payment = null): array
    {
        $command = $payment ? $payment->getCcType() : 'credit';
        $command = $this->prepareTransactionCommand($payment);

        return [
            'isRecurring' => $this->isRecurring,
            'IgnoreDuplicate' => $this->ignoreDuplicate,
            'Details' => $this->getTransactionDetails(),
            'Software' => $this->getSoftwareId(),
            'MerchReceipt' => $this->merchReceipt,
            'MerchReceiptEmail' => $this->merchReceiptEmail,
            'MerchReceiptName' => $this->merchReceiptTemplate,
            'CustReceiptName' => $this->custReceiptTemplate,
            'CustReceiptEmail' => $this->custReceiptEmail,
            'CustReceipt' => $this->custReceipt,
            'ClientIP' => $this->ip,
            'CardCode' => $this->cvv2,
            'Command' => $command,
            'LineItems' => $this->lineItems,
        ];
    }

    /**
     * Prepare Transaction Command Params.
     *
     * @param array $paymentOptions
     * @param mixed $isRefund
     * @return string
     * @throws NoSuchEntityException
     */
    public function prepareTransactionCommand(array $paymentOptions = [], mixed $isRefund = false)
    {
        $selOptionType = strtolower($paymentOptions['ebzc_option_type'] ?? '');
        $selOptionType = strtolower($selOptionType);
        $store = $this->storeManager->getStore();
        $configSaleType = $this->configFactory->create()->getPaymentAction($store->getId());
        $command = PaymentInterface::EBIZCHARGE_COMMAND_AUTHONLY;

        // Config Sale type
        switch ($configSaleType) {
            case PaymentInterface::PAYMENT_TRANSACTION_TYPE_AUTHORIZE:
                $command = PaymentInterface::EBIZCHARGE_COMMAND_AUTHONLY;

                if ($selOptionType === strtolower(PaymentInterface::EBIZCHARGE_METHOD_TYPE_CREDIT_CARD)) {
                    $command = PaymentInterface::EBIZCHARGE_COMMAND_AUTHONLY;
                }
                if ($selOptionType === strtolower(PaymentInterface::EBIZCHARGE_METHOD_TYPE_ACH)) {
                    $command = PaymentInterface::EBIZCHARGE_METHOD_TYPE_CHECK;
                }

                break;

            case PaymentInterface::PAYMENT_TRANSACTION_TYPE_AUTHORIZE_CAPTURE:
                if ($selOptionType === strtolower(PaymentInterface::EBIZCHARGE_METHOD_TYPE_CREDIT_CARD)) {
                    $command = PaymentInterface::EBIZCHARGE_COMMAND_SALE;
                }
                if ($selOptionType === strtolower(PaymentInterface::EBIZCHARGE_METHOD_TYPE_ACH)) {
                    $command = PaymentInterface::EBIZCHARGE_METHOD_TYPE_CHECK;
                }
                break;
            default:
                $command = PaymentInterface::EBIZCHARGE_COMMAND_SALE;
                break;
        }

        if (PaymentInterface::EBIZCHARGE_COMMAND_QUICK_SALE === $this->getData('command')) {
            $command = PaymentInterface::EBIZCHARGE_COMMAND_QUICK_SALE;
        }
        if (PaymentInterface::EBIZCHARGE_COMMAND_CAPTURE === $this->getData('command')) {
            $command = PaymentInterface::EBIZCHARGE_COMMAND_CAPTURE;
        }
        // in case of refund
        if ($isRefund && !empty($paymentOptions['txn_id'])) {
            $command = PaymentInterface::EBIZCHARGE_COMMAND_REFUND;
        }
        // setting final transaction command
        $this->command = $command;

        return $command;
    }

    /**
     * Get order transaction details.
     *
     * @throws NoSuchEntityException
     */
    public function getTransactionDetails(): array
    {
        $configFactory = $this->configFactory->create();
        /**
         * Comments.
         */
        $comments = !empty($this->orderComments) ? $this->orderComments : 'N/A';
        if (!empty($this->achtype)) {
            $comments = !empty($this->orderComments) ? $this->orderComments : 'Order # ' . $this->orderid;
        }

        $transactionDataParams = [
            'OrderID' => (string)($this->orderid ?? ''),
            'Invoice' => (string)($this->invoice ?? ''),
            'PONum' => (string)($this->ponum ?? ''),
            'Comments' => (string)$comments,
            'Description' => (string)$this->description ?? '',
            'Amount' => (string)($this->formatAmount($this->amount) ?? '0'),
            'Tax' => (string)($this->formatAmount($this->tax) ?? '0'),
            'Currency' => (string)($this->currency ?? $configFactory->getStoreCurrency($configFactory->getStoreId())),
            'Shipping' => (string)($this->formatAmount($this->shippingAmount) ?? '0'),
            'ShipFromZip' => (string)($this->shipzip ?? ''),
            'Discount' => (string)($this->formatAmount(abs($this->discount)) ?? '0'),
            'Subtotal' => (string)($this->formatAmount($this->subtotal) ?? '0'),
            'AllowPartialAuth' => $this->allowPartialAuth ?? '0',
            'Tip' => (string)($this->formatAmount($this->tipAmount) ?? '0'),
            'NonTax' => $this->nonTax ?? false,
            'Duty' => (string)($this->formatAmount($this->dutyAmount) ?? '0'),
        ];

        $transDataGrandTotal = (float)$transactionDataParams['Amount'];

        $transDataSubtotal = (float)$transactionDataParams['Subtotal'];
        $transDataTax = (float)$transactionDataParams['Tax'];
        $transShipping = (float)$transactionDataParams['Shipping'];
        $transDiscount = (float)$transactionDataParams['Discount'];
        $transDataTotalAmount = ($transDataSubtotal + $transDataTax + $transShipping) - $transDiscount;

        if ($transDataGrandTotal !== $transDataTotalAmount) {
            // $transactionDataParams["Duty"] = $transDataGrandTotal - $transDataTotalAmount;
            $transactionDataParams['Subtotal'] = '0';
            // unset($transactionDataParams["Subtotal"]);
        }
        return $transactionDataParams;
    }

    /**
     * Format Amount according to Magento
     *
     * @param mixed $amount
     * @param mixed $format
     * @param mixed $includeContainer
     * @param mixed $precision
     * @return array|string
     */
    public function formatAmount(
        mixed $amount = "0",
        mixed $format = true,
        mixed $includeContainer = true,
        mixed $precision = 0
    ): array|string
    {
        $amount = $this->priceHelper->currency($amount, false, false);

        return (string)preg_replace('/[^\d\.-]/', '', (string)$amount);
    }

    /**
     * Run Customer Transaction.
     *
     * @param mixed $customerToken
     * @param mixed $paymentMethodId
     * @param bool $isRecurring
     * @param bool $isSavedPayment
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function runCustomerTransaction(
        $customerToken = "",
        $paymentMethodId = "",
        InfoInterface $payment = null,
        $isRecurring = false,
        $isSavedPayment = false
    ): bool
    {
        /**
         * Response.
         */
        $transactionResults = [];
        $transactionResponse = false;
        /*
         * if is recurring enabled and
         * is Save Payment not checked
         */
        if ($isRecurring && !$isSavedPayment) {
            // phpcs:ignore
            throw new LocalizedException(
                __('You have a product in your cart, so please check the save payment checkbox and try again.')
            );
        }

        /** in case of Saved Payment Method */
        // if ($isSavedPayment) {
        //  $this->currency = '';
        // }

        $customerTransactionParams = $this->prepareCustomerTransactionParams($payment, $isRecurring);
        $paymentObj = (array)$payment->getAdditionalInformation();
        $avsCvvCurrentSettings = $this->getEbizAvsCvvSettings();
        $storeId = $this->getStoreId();

        try {
            $order = $payment->getOrder();
            $mageOrderId = $order->getIncrementId();

            if ($isRecurring) {
                $isRecurringExists = $this->isRecurringExists($payment);
                if ($payment->getExcludeAmount() > 0) {
                    $customerTransactionParams['Details']['Description'] = 'This is a recurring order #'
                        . $customerTransactionParams['Details']['OrderID'] . ' Amount ['
                        . $payment->getExcludeAmount() . '] already paid. Remaining amount will be charge now.';
                    $customerTransactionParams['Details']['Amount']
                        = ($customerTransactionParams['Details']['Amount'] - $payment->getExcludeAmount());
                }
            }

            /**
             * Pre Auth Full Amount Transaction
             * before doing full Amount Transaction.
             */
            // phpcs:ignore
            // $this->preAuthFullAmountTransaction($payment);
            $ebizOption = isset($paymentObj['ebzc_option']) ? $paymentObj['ebzc_option'] : '';

            if ('update' === $ebizOption) {
                $paymentMethodId = isset($paymentObj['ebzc_method_id']) ? $paymentObj['ebzc_method_id'] : '';
                $avsStreet = isset($paymentObj['ebzc_avs_street']) ? $paymentObj['ebzc_avs_street'] : '';
                $avsZip = isset($paymentObj['ebzc_avs_zip']) ? $paymentObj['ebzc_avs_zip'] : '';
                $paymentMethodId = isset($paymentObj['ebzc_method_id']) ? $paymentObj['ebzc_method_id'] : '';
                $expMonth = isset($paymentObj['cc_exp_month']) ? $paymentObj['cc_exp_month'] : '';
                $expYear = isset($paymentObj['cc_exp_year']) ? $paymentObj['cc_exp_year'] : '';
                $customerPaymentMethodParams = $paymentObj;
                $customerPaymentMethodParams['is_update'] = true;
                $customerPaymentMethodParams['ebiz_customer_token'] = $customerToken;
                $customerPaymentMethodParams['method_id'] = $paymentMethodId;
                $customerPaymentMethodParams['avs_zip'] = $avsZip;
                $customerPaymentMethodParams['cc_exp_month'] = $expMonth;
                $customerPaymentMethodParams['cc_exp_year'] = $expYear;
                $customerPaymentMethodParams['avs_street'] = $avsStreet;
                $customerId = $payment->getOrder()->getCustomerId();
                $this->customerFactory->create()
                    ->updateCustomerPaymentMethod((string)$customerId, $customerPaymentMethodParams);
            }

            $transactionParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'custNum' => $customerToken,
                'paymentMethodID' => $paymentMethodId,
                'tran' => $customerTransactionParams,
            ];
            // dump($transactionParams); throw new LocalizedException(__("exception"));

            $transactionResult = $this->getClient($storeId)->runCustomerTransaction($transactionParams);

            $transaction = $transactionResult->runCustomerTransactionResult;

            if (!empty($transactionResult = $transaction)) {
                $transactionResults = (array)$transactionResult;
                $this->setData('refnum', $transactionResults['RefNum']);
                $this->setData('RefNum', $transactionResults['RefNum']);

                $customerFactory = $this->customerFactory->create();
                $avsResultCodeResponses = $customerFactory->getValidAvsCvvResponses();

                $cvvResultCode = $transactionResults['CardCodeResultCode'] ?? 'N';
                $avsResultCode = $transactionResults['AvsResultCode'] ?? 'YNN';

                if (
                    isset($transactionResults['ResultCode'])
                    && strtolower((string)$transactionResults['ResultCode'])
                    !== strtolower(PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED)
                ) {
                    if ($order && $order->getId()) {
                        $order->delete();
                    }
                }

                // when transaction results are there with A or E
                if (
                    isset($transactionResults['ResultCode'])
                    && PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED
                    === $transactionResults['ResultCode']
                ) {
                    $ebizOption = isset($paymentObj['ebzc_option']) ? $paymentObj['ebzc_option'] : '';
                    $ebizOptionType = isset($paymentObj['ebzc_option_type']) ? $paymentObj['ebzc_option_type'] : '';
                    $avsStreet = isset($paymentObj['ebzc_avs_street']) ? $paymentObj['ebzc_avs_street'] : '';
                    $avsZip = isset($paymentObj['ebzc_avs_zip']) ? $paymentObj['ebzc_avs_zip'] : '';
                    $paymentMethodId = isset($paymentObj['ebzc_method_id']) ? $paymentObj['ebzc_method_id'] : '';

                    if (strtolower($ebizOptionType) !== strtolower(PaymentInterface::ACH)) {
                        // IN Admin Side if AVS enabled and Cvv do not match
                        if (
                            $avsCvvCurrentSettings['is_cvv_avs_enabled']
                            && PaymentInterface::EBIZCHARGE_FRAMEWORK_STATE_TYPE_ADMINHTML
                            === EbizDataHelper::getAreaCode()
                            && (!in_array($avsResultCode, $avsResultCodeResponses)
                                || PaymentInterface::CVV2_CARD_CODE_RESULT_M !== $cvvResultCode)
                        ) {
                            if (isset($transactionResults['RefNum'])) {
                                $payment->setCcTransId($transactionResults['RefNum']);

                                /** voiding the transction */
                                $voidTransaction = $this->runVoidTransaction(
                                    $payment,
                                    $isRecurring,
                                    true,
                                    PaymentInterface::EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID
                                );
                            }

                            $transactionError = $transactionResults['Error'] ?? '';
                            $transactionErrorCode = $transactionResults['ErrorCode'] ?? '';
                            $avsResult = $transactionResults['AvsResult'] ?? '';

                            // if ($transactionError === "Approved") {
                            //   $transactionError = "AVS|CVV mismatched.";
                            // }

                            $errorMsg = 'Payment authorization Error: ' . $transactionError . ', Error code: '
                                . $transactionErrorCode;
                            $this->ebizchargeLogger->addCritical(__($errorMsg . ', AVS Code: '
                                . $avsResultCode . ' AVS Result: ' . $avsResult));

                            throw new LocalizedException(__($errorMsg));
                        }
                    }

                    $this->ebizchargeLogger->addInfo(__('Success, transaction got successful.'));
                    $this->setTransactionResult($transactionResult);
                    $transactionResponse = true;

                    /* push recurring in case of the
                     * Transaction, recurring
                     * enabled and Is recurring exits
                     */
                    if (true === $isRecurring) {
                        $transactionResults = (array)$transaction;
                        $orderRecurringParams = [
                            'payment' => $payment,
                            'transaction' => $transaction,
                            'method_id' => $paymentMethodId,
                            'customer_transaction_params' => $customerTransactionParams,
                        ];

                        if (
                            isset($transactionResults['ResultCode']) &&
                            PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED ===
                            $transactionResults['ResultCode']
                        ) {
                            /** @var create a $recurring */
                            $recurring = $this->creatRecurringOrder($orderRecurringParams);
                            $this->ebizchargeLogger->addInfo(__('Success, the recurring order has been added'));
                        }
                    }

                    $transactionResponse = true;
                } else {
                    if (
                        PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR
                        === $transactionResults['ResultCode']
                    ) {
                        $this->setData('resultcode', $transactionResults['ResultCode']);
                        if (isset($transactionResults['RefNum'])) {
                            $payment->setCcTransId($transactionResults['RefNum']);

                            /** voiding the transction */
                            $voidTransaction = $this->runVoidTransaction(
                                $payment,
                                $isRecurring,
                                true,
                                PaymentInterface::EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID
                            );
                        }

                        $transactionError = $transactionResults['Error'] ?? '';
                        $transactionErrorCode = $transactionResults['ErrorCode'] ?? '';
                        $avsResult = $transactionResults['AvsResult'] ?? '';

                        if ('Approved' === $transactionError) {
                            $transactionError = 'AVS|CVV mismatched.';
                        }
                        $errorMsg = 'Payment authorization Error: the Transaction has been declined,  '
                            . $transactionError . ', Error Code: ' . $transactionErrorCode;
                        $this->ebizchargeLogger->addCritical(__($errorMsg . ', AVS Code: ' . $avsResultCode
                            . ' AVS Result: ' . $avsResult));

                        throw new LocalizedException(__($errorMsg));
                    }
                }
            }
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__('Payment authorization Error: ' . $ex->getMessage()));
            throw new LocalizedException(__($ex->getMessage()));
        }

        return $transactionResponse;
    }

    /**
     * Prepare Customer Transaction Params.
     *
     * @param null|InfoInterface $payment
     * @param bool $isRecurring
     *
     * @throws NoSuchEntityException
     */
    public function prepareCustomerTransactionParams(InfoInterface $payment = null, bool $isRecurring = false): array
    {
        $paymentInfoParams = $this->prepareTransactionInfoParams($payment);
        $checkoutSession = $this->checkoutSession;
        $paymentOptionType = $paymentInfoParams['ebzc_option_type'] ?? '';

        if ('' === $paymentOptionType) {
            $paymentOptionType = $paymentInfoParams['cc_type'] ?? self::ACH;
        }
        $paymentOptionType = strtolower($paymentOptionType);
        $command = strtolower($this->prepareTransactionCommand($paymentInfoParams));
        $order = $payment->getOrder();
        $orderBillingAddress = $order->getBillingAddress();
        $customerId = $order->getCustomerId();
        $customerEmail = !(empty($customerId)) ? $order->getCustomerEmail() : $orderBillingAddress->getEmail();
        $customerFirstName = $orderBillingAddress->getFirstname();
        $customerLastName = $orderBillingAddress->getLastname();
        $customerName = $customerFirstName . ' ' . $customerLastName;
        $storeId = $this->configFactory->create()->getStoreId();
        $isGatewayEmailsEnabled = $this->configFactory->create()->isGatewayEmailsEnabled($storeId);
        $isGatewayMerchantEmailsEnabled = $this->configFactory->create()->isGatewayMerchantEmailsEnabled($storeId);

        // if Gateway Emails Enabled
        if (!$isGatewayEmailsEnabled) {
            $this->custReceiptEmail = '';
            $this->custReceiptTemplate = '';
            $this->custReceipt = false;
        }
        if (!$isGatewayMerchantEmailsEnabled) {
            $this->merchReceipt = false;
            $this->merchReceiptEmail = '';
            $this->merchReceiptTemplate = '';
        }
        $transactionParams = [
            'CustReceipt' => $this->custReceipt,
            'CustReceiptEmail' => $this->custReceiptEmail,
            'CustReceiptName' => $this->custReceiptTemplate,
            'MerchReceipt' => $this->merchReceipt,
            'MerchReceiptEmail' => $this->merchReceiptEmail,
            'MerchReceiptName' => $this->merchReceiptTemplate,
            'isRecurring' => $isRecurring,
            'IgnoreDuplicate' => true,
            'Details' => $this->getTransactionDetails(),
            'Software' => $this->getSoftwareId(),
            'ClientIP' => $this->getRemoteAddress(),
            'Command' => $command,
            'LineItems' => $this->lineItems,
        ];
        /*
         * if CVV2 code then add
         * CVV Code
         */
        if (null !== $this->cvv2) {
            $transactionParams['CardCode'] = $this->cvv2;
        }

        return $transactionParams;
    }

    /**
     * Prepare Transaction params
     *
     * @param InfoInterface $payment
     * @return array
     */
    public function prepareTransactionInfoParams(InfoInterface $payment): array
    {
        $paymentAdditionalInformations = $payment->getadditional_information();
        $paymentInfoParams = [];
        if (count($paymentAdditionalInformations) > 0) {
            foreach ($paymentAdditionalInformations as $key => $paymentAdditionalInformation) {
                if (is_array($paymentAdditionalInformation)) {
                    foreach ($paymentAdditionalInformation as $pKey => $payInfo) {
                        $paymentInfoParams[$pKey] = $payInfo;
                    }
                } else {
                    $paymentInfoParams[$key] = $paymentAdditionalInformation;
                }
            }
        }
        /**
         * In case of Transaction Id.
         */
        $tnxId = $payment->getTransactionId();
        // Payment Info Params
        $paymentInfoParams['txn_id'] = $tnxId;
        return $paymentInfoParams;
    }

    /**
     * Get Remote Address.
     *
     * @return bool|string
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress->getRemoteAddress(false);
    }

    /**
     * Get Ebiz Avs Cvv Settings.
     *
     * @return false[]
     *
     * @throws NoSuchEntityException
     */
    public function getEbizAvsCvvSettings()
    {
        $avsResponseSettings = [
            'verify_card_before_save' => false,
            'use_full_amount_for_avs' => false,
            'is_cvv_avs_enabled' => false,
        ];

        $storeId = $this->configFactory->create()->getStoreId();
        $merchantTransactionData = $this->getMerchantTransactionInfo($storeId);
        $isCreditCardEnabled = $merchantTransactionData['response']['enable_card'] ?? false;
        $isAvsWarningsEnabled = $merchantTransactionData['avsCvvSettings']['EnableAVSWarnings'] ?? false;
        $isCvvWarningsEnabled = $merchantTransactionData['avsCvvSettings']['EnableCVVWarnings'] ?? false;
        $verifyCardBeforeSaving = $merchantTransactionData['avsCvvSettings']['VerifyCreditCardBeforeSaving'] ?? false;
        $useFullAmountForAvs = $merchantTransactionData['avsCvvSettings']['UseFullAmountForAVS'] ?? false;
        $declineTransAvsDisabled
            = $merchantTransactionData['avsCvvSettings']['DeclineTransactionIfAVSWarningsAreDisabled'] ?? false;

        // @var temp $isAvsWarningsEnabled
        /*
        $isAvsWarningsEnabled = true;
          $isCvvWarningsEnabled = true;
          $useFullAmountForAvs = true;
        */
        //  $useFullAmountForAvs = false;

        // if credit card payment Method enabled
        if ($isCreditCardEnabled) {
            if ($isAvsWarningsEnabled || $isCvvWarningsEnabled) {
                $avsResponseSettings['is_cvv_avs_enabled'] = true;

                if ($useFullAmountForAvs) {
                    $avsResponseSettings['use_full_amount_for_avs'] = true;
                }
                if ($verifyCardBeforeSaving) {
                    $avsResponseSettings['verify_card_before_save'] = true;
                }
            }
        }

        return $avsResponseSettings;
    }

    /**
     * Get merchant supported transaction/payment types
     *
     * @param mixed $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getMerchantTransactionInfo(mixed $storeId = "0")
    {
        $configFactory = $this->configFactory->create();
        $successMessage = __('Merchant data fetched successfully.');
        $errorMessage = __('Error occurred during fetching merchant data.');
        $storeId = $this->httpRequest->getParam("store") ?? $storeId;
        /**
         * Prepare response.
         */
        $response = [
            'enable_card' => false,
            'enableAch' => false,
            'status' => false,
            'message' => __('Merchant data not found from EBizCharge Payment Gateway'),
        ];

        $isEbizchargeEnabled = $configFactory->isActive($storeId);
        $ebizGatewayKey = $configFactory->getSourceKey($storeId);
        $ebizUsername = $configFactory->getSourceId($storeId);
        $ebizUserPin = $configFactory->getSourcePin($storeId);

        if ($configFactory->isPostFixExits($ebizGatewayKey)) {
            $ebizGatewayKey = $configFactory->decryptWithPostFix($ebizGatewayKey);
        }
        if ($configFactory->isPostFixExits($ebizUsername)) {
            $ebizUsername = $configFactory->decryptWithPostFix($ebizUsername);
        }
        if ($configFactory->isPostFixExits($ebizUserPin)) {
            $ebizUserPin = $configFactory->decryptWithPostFix($ebizUserPin);
        }

        if (!$isEbizchargeEnabled || !$ebizGatewayKey || !$ebizUsername || !$ebizUserPin) {
            $this->ebizchargeLogger->addInfo(__('EBizCharge payment hub is not active.'));
            return $response;
        }
        /**
         * Surcharge Settings.
         */
        $surchargeSettings = [
            SurchargeInterface::EBIZ_SURCHARGE_ENABLED => false,
            SurchargeInterface::EBIZ_SURCHARGE_COUNTRY_ID => '',
            SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE => 0,
            SurchargeInterface::EBIZ_SURCHARGE_TERMS_NOTE => '',
            SurchargeInterface::EBIZ_SURCHARGE_CAPTION => '',
            SurchargeInterface::EBIZ_SURCHARGE_TYPE_ID => '',
            SurchargeInterface::EBIZ_SURCHARGE_SURCHARGE_AMOUNT => '0',
        ];

        /**
         * AVS CVV Data.
         */
        $avsCvvDataSettings = [
            SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_IS_EMV_ENABLED => false,
            SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_ENABLE_AVS_WARNINGS => false,
            SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_ENABLE_CVV_WARNINGS => false,
            SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_USE_FULL_AMOUNT_FOR_AVS => false,
            SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_DECLINE_TRANSACTION_IF_AVS_WARNINGS_DISABLED
            => false,
            SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_VERIFY_CREDIT_CARD_BEFORE_SAVING => false,
            SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_AUTO_DISCOUNT => false,
            SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_AUTO_ITEM_DISCOUNT => false,
            SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_DISCOUNT_PERCENTAGE => 0.0,
            SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_USE_CAPTURE_ENHANCEMENT => false,
            SurchargeInterface::EBIZ_SURCHARGE_SURCHARGE_AMOUNT => 0.0,
        ];

        /*
         * if Merchant transaction data already exists it only hists Gateway
         * When Merchant transaction data is not set to optimize process
         */
        if (!$this->merchantTransactionInfo || 0 === count($this->merchantTransactionInfo)) {
            try {

                $merchantParams = [
                    'securityToken' => $this->getUeSecurityToken($storeId),
                ];

                if ($this->getClient($storeId)) {
                    /** Fetching Result from EBizCharge */
                    $result = $this->getClient($storeId)->getMerchantTransactionData($merchantParams);
                    // Get Merchant Transaction Data Results
                    if (is_object($result->GetMerchantTransactionDataResult)) {
                        $this->merchantTransactionInfo = (array)$result->GetMerchantTransactionDataResult;
                        // Add Info
                        $this->ebizchargeLogger->addInfo($successMessage);
                        /**
                         * Response.
                         */
                        $response = [
                            'enable_card' => $this->merchantTransactionInfo['AllowCreditCardPayments'] ?? false,
                            'enableAch' => $this->merchantTransactionInfo['AllowACHPayments'] ?? false,
                            'status' => true,
                            'message' => $successMessage,
                        ];

                        // Surcharge Data
                        $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_ENABLED]
                            = $this->merchantTransactionInfo['IsSurchargeEnabled'] ?? false;
                        $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_COUNTRY_ID]
                            = $this->merchantTransactionInfo['SurchargeCountryId'] ?? '';
                        $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE]
                            = $this->merchantTransactionInfo['SurchargePercentage'] ?? 0;
                        $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_TERMS_NOTE]
                            = $this->merchantTransactionInfo['SurchargeTermsNote'] ?? 0;
                        $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_CAPTION]
                            = $this->merchantTransactionInfo['SurchargeCaption'] ?? '';
                        $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_TYPE_ID]
                            = $this->merchantTransactionInfo['SurchargeTypeId'] ?? '';
                        $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_SURCHARGE_AMOUNT] = '0';

                        // phpcs:disable
                        $isCreditCardEnabled = $this->merchantTransactionInfo[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_IS_CREDIT_CARD_ENABLED] ?? false;
                        $isAvsWarningEnabled = $this->merchantTransactionInfo[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_ENABLE_AVS_WARNINGS] ?? false;
                        $isCvvWarningsEnabled = $this->merchantTransactionInfo[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_ENABLE_CVV_WARNINGS] ?? false;

                        if (true === $isAvsWarningEnabled || true === $isCvvWarningsEnabled) {
                            $isAvsWarningEnabled = true;
                            $isCvvWarningsEnabled = true;
                        }

                        $avsCvvDataSettings[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_IS_EMV_ENABLED] =
                            $this->merchantTransactionInfo[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_IS_EMV_ENABLED] ?? false;
                        $avsCvvDataSettings[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_ENABLE_AVS_WARNINGS] =
                            $isAvsWarningEnabled;
                        $avsCvvDataSettings[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_ENABLE_CVV_WARNINGS] =
                            $isCvvWarningsEnabled;
                        $avsCvvDataSettings[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_USE_FULL_AMOUNT_FOR_AVS] =
                            $this->merchantTransactionInfo[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_USE_FULL_AMOUNT_FOR_AVS] ?? false;
                        $avsCvvDataSettings[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_DECLINE_TRANSACTION_IF_AVS_WARNINGS_DISABLED] =
                            $this->merchantTransactionInfo[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_DECLINE_TRANSACTION_IF_AVS_WARNINGS_DISABLED] ?? false;
                        $avsCvvDataSettings[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_VERIFY_CREDIT_CARD_BEFORE_SAVING] =
                            $this->merchantTransactionInfo[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_VERIFY_CREDIT_CARD_BEFORE_SAVING] ?? false;
                        $avsCvvDataSettings[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_AUTO_DISCOUNT] =
                            $this->merchantTransactionInfo[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_AUTO_DISCOUNT] ?? false;
                        $avsCvvDataSettings[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_AUTO_ITEM_DISCOUNT] =
                            $this->merchantTransactionInfo[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_AUTO_ITEM_DISCOUNT] ?? false;
                        $avsCvvDataSettings[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_DISCOUNT_PERCENTAGE] =
                            $this->merchantTransactionInfo[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_DISCOUNT_PERCENTAGE] ?? 0.0;
                        $avsCvvDataSettings[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_USE_CAPTURE_ENHANCEMENT] =
                            $this->merchantTransactionInfo[SurchargeInterface::EBIZCHARGE_CONFIG_TRANSACTION_DATA_USE_CAPTURE_ENHANCEMENT] ?? false;
                        // phpcs:enable
                    } else {
                        $this->ebizchargeLogger->addError($errorMessage);
                        $response['message'] = $errorMessage;
                    }
                }
            } catch (SoapFault $soapFault) {
                $this->ebizchargeLogger->addCritical($errorMessage . ' Error: '
                    . $soapFault->getMessage());
                $response['message'] = $errorMessage . ' Error: ' . $soapFault->getMessage();
                $this->ebizchargeLogger->addError($response['message']);
            }

            $this->merchantTransactionInfo['response'] = $response;
            $this->merchantTransactionInfo['surchargeSettings'] = $surchargeSettings;
            $this->merchantTransactionInfo['avsCvvSettings'] = $avsCvvDataSettings;
        }

        return $this->merchantTransactionInfo;
    }

    /**
     * Is Recurring Exists
     *
     * @param InfoInterface|null $payment
     * @return mixed
     */
    public function isRecurringExists(?InfoInterface $payment = null)
    {
        $order = $payment->getOrder();
        $mageOrderId = $order->getIncrementId();

        // @var  $recurringModel
        return $this->recurringFactory->create()->getRecurringByOrderId($mageOrderId);
    }

    /**
     * Overwrite data in the current object
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public function setData(string $property, mixed $value = "")
    {
        try {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(__('Exception occurred ' . $e->getMessage()));
        }
    }

    /**
     * Run Transaction
     *
     * @param InfoInterface|null $payment
     * @param bool $isRecurring
     * @param bool $isRefund
     * @param mixed $command
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function runVoidTransaction(
        ?InfoInterface $payment = null,
        bool           $isRecurring = false,
        bool           $isRefund = false,
        mixed          $command = PaymentInterface::EBIZCHARGE_METHOD_TYPE_VOID
    )
    {
        $isTransactionSuccess = false;

        /** preparing $tranReqestParams the Transaction Request Params */
        $tranReqestParams = $this->getTransactionRequest($payment, $isRecurring, $isRefund);
        // Command for transaction
        $tranReqestParams['Command'] = $command;

        $creditMemo = $payment->getCreditmemo();
        $storeId = $this->getStoreId();

        $transactionRequestParams = [
            'securityToken' => $this->getUeSecurityToken($storeId),
            'tran' => $tranReqestParams,
        ];

        try {
            /** @var transaction
             * $transaction
             */
            $transaction = $this->getClient($storeId)->runTransaction($transactionRequestParams);

            // response of the transaction
            if (!empty($transactionResult = $transaction->runTransactionResult)) {
                $transactionResults = (array)$transactionResult;

                if (
                    isset($transactionResults['ResultCode']) &&
                    PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED ===
                    (string)$transactionResults['ResultCode']
                ) {
                    // set transaction data
                    $this->setTransactionResult($transactionResult);
                    $isTransactionSuccess = true;
                } else {
                    if (
                        isset($transactionResults['ResultCode'])
                        && PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR
                        === (string)$transactionResults['ResultCode']
                        // phpcs:ignore
                        && strpos(strtolower((string)$transactionResults['Error']), 'already voided') > -1
                    ) {
                        $this->setTransactionResult($transactionResult);
                        $this->setData('resultcode', 'A');
                        $isTransactionSuccess = true;
                    } else {
                        $transactionError = __(
                            'This transaction is settled and cannot be voided. Issue a refund instead.'
                        );

                        if (isset($transactionResults['ErrorCode']) && '117' === $transactionResults['ErrorCode']) {
                            throw new LocalizedException(__($transactionResults['Error']));
                        }

                        throw new LocalizedException(__($transactionResults['Error']));
                    }
                }
            }
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__(
                'Exception occurred during running transactions with SoapFault: ' . $ex->getMessage()
            ));

            throw new LocalizedException(__('Payment Exception ' . $ex->getMessage()));
        }

        return $isTransactionSuccess;
    }

    /**
     * Get Transaction Request.
     *
     * @param null|InfoInterface $payment
     * @param bool $isRecurring
     * @param bool $isRefund
     *
     * @throws NoSuchEntityException
     */
    private function getTransactionRequest(
        InfoInterface $payment = null,
        bool          $isRecurring = false,
        bool          $isRefund = false
    ): array
    {
        $magCustomerId = empty($this->custid) ? CustomerInterface::GUEST_CUSTOMER_LAST_NAME : $this->custid;
        $paymentInfoParams = $this->prepareTransactionInfoParams($payment);
        $order = $payment->getOrder();

        /*
         * in case of Guest customer or
         * register User
         */
        if (CustomerInterface::GUEST_CUSTOMER_LAST_NAME !== $magCustomerId) {
            $customer = $this->customerFactory->create()->load($magCustomerId);
            $magCustomerId = $customer->getEcCustId();
        } else {
            $magCustomerId = $payment->getOrder()->getQuoteId() ?? '000000';
        }

        $command = strtolower($this->prepareTransactionCommand($paymentInfoParams, $isRefund));
        $this->command = $command;

        $paymentOptionType = isset($paymentInfoParams['ebzc_option_type']) ?
            strtolower($paymentInfoParams['ebzc_option_type']) : strtolower(self::EBIZCHARGE_TRANSACTION_TYPE_CREDIT);

        if (
            !$paymentOptionType || strtolower($paymentOptionType) ===
            strtolower(self::EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD)
        ) {
            $paymentOptionType = strtolower(self::EBIZCHARGE_TRANSACTION_TYPE_CREDIT);
        }

        if (
            PaymentInterface::EBIZCHARGE_COMMAND_CAPTURE === $command
            || PaymentInterface::PAYMENT_TRANSACTION_TYPE_AUTHORIZE_CAPTURE === $this->command
            || PaymentInterface::EBIZCHARGE_COMMAND_QUICK_SALE === $this->command
        ) {
            $orderInvoice = null;
            $invoiceCollections = $order->getInvoiceCollection() ?? [];
            $counter = 0;

            if (count($invoiceCollections) > 0) {
                foreach ($invoiceCollections as $invoice) {
                    ++$counter;
                    $caseToCapture = $invoice->getRequestedCaptureCase() ?? '';
                    $orderInvoice = $invoice;
                }
            }
            if ($orderInvoice) {
                $invoiceNumber = $this->orderFactory->create()->getProposedInvoiceIncrementId() ?? 0;

                $this->invoice = $invoiceNumber;

                $invoicedGrandTotal = floatval($orderInvoice->getGrandTotal()) ?? floatval($order->getGrandTotal());
                $invoicedBaseGrandTotal = floatval($orderInvoice->getBaseGrandTotal()) ??
                    floatval($order->getBaseGrandTotal());
                $invoicedSubTotal = floatval($orderInvoice->getSubtotal()) ?? floatval($order->getSubtotal());
                $invoicedShippingAmount = floatval($orderInvoice->getShippingAmount()) ??
                    floatval($order->getShippingAmount());

                $isTaxAble = false;
                $isNonTaxAble = true;
                $invoicedTaxAmount = floatval($orderInvoice->getTaxAmount()) ??
                    floatval($order->getTaxAmount());

                if ($invoicedTaxAmount > 0) {
                    $isTaxAble = true;
                    $isNonTaxAble = false;
                }
                $invoicedSurchargeAmount = floatval($orderInvoice->getEcSurchargeAmount()) ??
                    floatval($order->getEcSurchargeAmount());

                if ($invoicedSurchargeAmount > 0) {
                    $invoicedGrandTotal = floatval($invoicedGrandTotal - $invoicedSurchargeAmount);
                }

                $this->amount = $this->formatAmount($invoicedGrandTotal) ?? 0;
                $this->subtotal = $this->formatAmount($invoicedSubTotal) ?? 0;
                $this->tax = $this->formatAmount($invoicedTaxAmount) ?? 0;
                $this->nonTax = $isNonTaxAble;
                $this->nontaxable = $isNonTaxAble;
                $this->shippingAmount = (float)$this->formatAmount($invoicedShippingAmount) ?? 0;
                $this->shipping = (float)$this->formatAmount($invoicedShippingAmount) ?? 0;

                // $this->command = PaymentInterface::EBIZCHARGE_COMMAND_QUICK_SALE;
            }
        }

        $transactionalData = $this->getTransactionDetails();
        $orderStatus = $order->getStatus();
        $billingAddress = $order->getBillingAddress();
        $billStreet = $billingAddress->getStreet();
        $billZip = $billingAddress->getZip();
        $storeId = $this->configFactory->create()->getStoreId();
        $isGatewayEmailsEnabled = $this->configFactory->create()->isGatewayEmailsEnabled($storeId);
        $isGatewayMerchantEmailsEnabled = $this->configFactory->create()->isGatewayMerchantEmailsEnabled($storeId);

        // if Gateway Emails Enabled
        if (!$isGatewayEmailsEnabled) {
            $this->custReceiptEmail = '';
            $this->custReceiptTemplate = '';
            $this->custReceipt = false;
        }

        if (!$isGatewayMerchantEmailsEnabled) {
            $this->merchReceipt = false;
            $this->merchReceiptEmail = '';
            $this->merchReceiptTemplate = '';
        }

        $requestParams = [
            'CustReceipt' => $this->custReceipt,
            'CustReceiptEmail' => $this->custReceiptEmail,
            'CustReceiptName' => $this->custReceiptTemplate,
            'MerchReceipt' => $this->merchReceipt,
            'MerchReceiptEmail' => $this->merchReceiptEmail,
            'MerchReceiptName' => $this->merchReceiptTemplate,
            'Software' => $this->software,
            'LineItems' => $this->lineItems,
            'IsRecurring' => $isRecurring,
            'IgnoreDuplicate' => $this->ignoreDuplicate,
            'Details' => $transactionalData,
            'CustomerID' => $magCustomerId,
            'Command' => $this->command,
            'ClientIP' => $this->ip,
            'AccountHolder' => $this->cardholder,
            'RefNum' => (string)$payment->getCcTransId(),
            'BillingAddress' => $this->getBillingAddress(),
            'ShippingAddress' => $this->getShippingAddress(),
        ];

        $this->transtype = 'order';
        // if invoice type or order
        if ($payment->getCcTransId()) {
            $this->transtype = 'invoice';
            $this->billstreet = $paymentInfoParams['ebzc_avs_street'] ?? $this->billstreet;
            $this->billzip = $paymentInfoParams['ebzc_avs_zip'] ?? $this->billzip;

            $requestParams['CreditCardData'] = [
                'InternalCardAuth' => false,
                'CardPresent' => false,
                'AvsStreet' => $this->billstreet,
                'AvsZip' => $this->billzip,
            ];
        }

        if (!$payment->getCcTransId()) {
            if (strtolower($paymentOptionType) === strtolower(self::EBIZCHARGE_TRANSACTION_TYPE_CREDIT)) {
                if (null !== $orderStatus) {
                    $this->card = $paymentInfoParams['cc_number'] ?? $this->card;
                    $expiryDate = '';
                    if (
                        isset($paymentInfoParams['cc_exp_year'], $paymentInfoParams['cc_exp_month'])
                    ) {
                        $expiryDate .= $paymentInfoParams['cc_exp_month'];
                        $expiryDate .= substr(
                            (string)$paymentInfoParams['cc_exp_year'],
                            strlen((string)$paymentInfoParams['cc_exp_year']) - 2
                        );
                    }
                    $this->exp = $expiryDate;
                    $this->cvv2 = isset($paymentInfoParams['cc_cid']) ? $paymentInfoParams['cc_cid'] : $this->cvv2;
                    $this->billstreet = $billStreet[0] ? $billStreet[0] : $this->billstreet;
                    $this->billzip = $billZip ? $billZip : $this->billzip;
                    $requestParams['AuthCode'] = $payment->getLastTransId();
                } else {
                    $this->card = $paymentInfoParams['cc_number'] ?? $this->card;
                    $expiryDate = '';
                    if (isset($paymentInfoParams['cc_exp_year'], $paymentInfoParams['cc_exp_month'])) {
                        $expiryDate .= $paymentInfoParams['cc_exp_month'] ?? '';
                        $expiryDate .= substr(
                            (string)$paymentInfoParams['cc_exp_year'],
                            strlen((string)$paymentInfoParams['cc_exp_year']) - 2
                        );
                    }
                    $this->exp = $expiryDate;
                    $this->cvv2 = $paymentInfoParams['cc_cid'] ?? $this->cvv2;
                    $this->billstreet = $billStreet[0] ? $billStreet[0] : $this->billstreet;
                    $this->billzip = $billZip ? $billZip : $this->billzip;
                    $requestParams['AuthCode'] = $payment->getLastTransId();
                }

                $this->billstreet = $paymentInfoParams['ebzc_avs_street'] ?? $this->billstreet;
                $this->billzip = $paymentInfoParams['ebzc_avs_zip'] ?? $this->billzip;

                $requestParams['CreditCardData'] = [
                    'InternalCardAuth' => false,
                    'CardPresent' => false,
                    'CardNumber' => $this->card,
                    'CardExpiration' => $this->exp,
                    'CardCode' => $this->cvv2,
                    'AvsStreet' => $this->billstreet,
                    'AvsZip' => $this->billzip,
                ];
            }

            // request params
            if (strtolower($paymentOptionType) === strtolower(self::ACH)) {
                // if ($orderStatus !== null) {
                $this->card = $paymentInfoParams['cc_number'] ?? $this->card;
                $this->achtype = $paymentInfoParams['ach_type'] ?? $this->achtype;
                $this->achroute = $paymentInfoParams['ach_routing'] ?? $this->achroute;
                $requestParams['AuthCode'] = $payment->getLastTransId();
                // }else{
                //    $this->card = $paymentInfoParams['cc_number'] ?? $this->card;
                //    $this->achtype = $paymentInfoParams['ach_type'] ?? $this->achtype;
                //    $this->achroute = $paymentInfoParams['ach_routing'] ?? $this->achroute;
                //    $requestParams['AuthCode'] = $payment->getLastTransId();
                // }
                $requestParams['CheckData'] = [
                    'Account' => $this->card,
                    'AccountType' => $this->achtype,
                    'Routing' => $this->achroute,
                ];
            }
        }

        return $requestParams;
    }

    /**
     * Run Transaction
     *
     * @param InfoInterface|null $payment
     * @param bool $isRecurring
     * @param array $paymentMethodParams
     * @param bool $isRefund
     * @return true|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function runTransaction(
        ?InfoInterface $payment = null,
        bool           $isRecurring = false,
        array          $paymentMethodParams = [],
        bool           $isRefund = false
    )
    {
        $isTransactionSuccess = false;
        $order = $payment->getOrder();
        $incrementId = '';

        if ($order && $order->getId()) {
            $incrementId = $order->getIncrementId();
            $orderFactory = $this->orderFactory->create();
            $order = $orderFactory->loadByIncrementId($incrementId);
        }
        $storeId = $order->getStoreId() ?? $this->getStoreId();
        /**
         * preparing the Transaction Request Params.
         */
        $tranReqestParams = $this->getTransactionRequest($payment, $isRecurring, $isRefund);
        $command = $tranReqestParams['Command']
            ?? strtolower(PaymentInterface::EBIZCHARGE_COMMAND_TYPE_AUTHONLY);

        // Set 3D Secure Data In Transaction params
        if (
            Area::AREA_ADMINHTML !== EbizDataHelper::getAreaCode()
            && !$isRefund
        ) {
            $tranReqestParams = $this->set3DSecureDataInTransaction($tranReqestParams);
        }
        // $creditMemo = $payment->getCreditmemo();
        $transactionRequestParams = [
            'securityToken' => $this->getUeSecurityToken($storeId),
            'tran' => $tranReqestParams,
        ];
//dump($transactionRequestParams);throw new LocalizedException(__("Exception"));
        try {
            $tranRefNum = $transactionRequestParams['tran']['RefNum'] ?? false;

            $storeId = $this->configFactory->create()->getStoreId();
            $reqParams = $this->httpRequest->getParams();
            $isAjax = isset($reqParams['payment']['is_ajax']) ? $reqParams['payment']['is_ajax'] : 0;
            $userAction = isset($reqParams['payment']['user_action']) ? $reqParams['payment']['user_action'] : 0;

            $sessionTransactionResults = $this->coreSession->getTransactionResults();
            $preAuthResponse = $this->coreSession->getPreAuthResponse();
            $transRefId = $transactionRequestParams['tran']['RefNum'] ?? '';

            if (!$transRefId) {
                // if state is adminhtml
                if (
                    $isAjax && PaymentInterface::EBIZCHARGE_FRAMEWORK_STATE_TYPE_ADMINHTML
                    === EbizDataHelper::getAreaCode()
                ) {
                    if (
                        $sessionTransactionResults && PaymentInterface::EBIZCHARGE_USER_ACTION_TYPE_CANCELE
                        === $userAction
                    ) {
                        if (!empty($transactionResult = $sessionTransactionResults->runTransactionResult)) {
                            $sessionTransactionResults->runTransactionResult->ResultCode
                                = PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR;
                            $sessionTransactionResults->runTransactionResult->Error
                                = __('Could not authenticated AVS|CVV.');
                            $transaction = $sessionTransactionResults;
                            $refNum = $sessionTransactionResults->runTransactionResult->RefNum;
                            // transaction request params
                            $this->voidPreAuthTransaction($refNum, $transactionRequestParams);
                            $preAuthResponse['is_canceled'] = true;

                            $resultJsonFactory = $this->jsonFactory->create();
                            // phpcs:disable
                            echo json_encode($preAuthResponse);

                            exit;
                        }
                    }

                    /* To handle the case when from admin new customer plus order creation
                     * process cause customer registration error on proceeding order from modal
                     */
                    $this->backendSessionQuote->setCustomerId(
                        $this->backendSessionQuote->getQuote()->getCustomer()->getId()
                    );

                    $preAuthResponse = $this->renderPreAuthAjaxTransaction($transactionRequestParams);
                    $resultJsonFactory = $this->jsonFactory->create();
                    echo json_encode($preAuthResponse);

                    exit;
                    // phpcs:enable
                }

                if (!$sessionTransactionResults && PaymentInterface::EBIZCHARGE_METHOD_TYPE_CHECK !== $command) {
                    /**
                     * Render Pre Auth Transactions.
                     */
                    $preAuthResponse = $this->renderPreAuthTransaction($transactionRequestParams);

                    if (PaymentInterface::EBIZCHARGE_FRAMEWORK_STATE_TYPE_ADMINHTML !== $preAuthResponse['state']) {
                        // if pre auth is enabled
                        if (
                            false === $preAuthResponse['payment_authenticated']
                            && $preAuthResponse['avs_cvv_enabled']
                        ) {
                            $this->resetTransactionResults();

                            throw new LocalizedException(__($preAuthResponse['message']));
                        }
                    }
                }

                // if session transaction results

                if (
                    $sessionTransactionResults
                    && PaymentInterface::EBIZCHARGE_FRAMEWORK_STATE_TYPE_ADMINHTML === $preAuthResponse['state']
                ) {
                    if (PaymentInterface::EBIZCHARGE_USER_ACTION_TYPE_OK === $userAction) {
                        $authAmount = $sessionTransactionResults->runTransactionResult->AuthAmount;

                        if (PaymentInterface::EBIZCHARGE_TRANSACTION_PRE_AUTH_MIN_AMOUNT === $authAmount) {
                            $refNum = $sessionTransactionResults->runTransactionResult->RefNum;
                            // transaction request params
                            $this->voidPreAuthTransaction($refNum, $transactionRequestParams);

                            /**
                             * Run Full Amount Transaction.
                             */
                            $transaction = $this->getClient($storeId)->runTransaction($transactionRequestParams);
                        } else {
                            $transaction = $sessionTransactionResults;
                        }
                    }
                } else {
                    /**
                     * Run Transaction.
                     */
                    $transaction = $this->getClient($storeId)->runTransaction($transactionRequestParams);
                }
            }

            if (!isset($transaction)) {
                $transaction = $this->getClient($storeId)->runTransaction($transactionRequestParams);
            }

            // Transaction Results
            if (!empty($transactionResult = $transaction->runTransactionResult)) {
                $transactionResults = (array)$transactionResult;
                /**
                 * If Result code of transaction got failed then we need to delete previous order
                 * which presents in cache and session of Mage
                 */
                if (
                    isset($transactionResults['ResultCode'])
                    && strtolower((string)$transactionResults['ResultCode'])
                    !== strtolower(PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED)
                ) {
                    if ($order && $order->getId()) {
                        $order->delete();
                        $this->ebizchargeLogger->addInfo(
                            __("The transaction not successful, so deleting the Order in cache.")
                        );
                    }
                }

                if (
                    (isset($transactionResults['Error']) && 'Approved' === $transactionResults['Error'])
                    || 'Approve' === $transactionResults['Error']
                ) {
                    $transactionResults['Error'] = 'AVS|CVV mismatched.';
                }

                $validAvsResponses = $this->customerFactory->create()->getValidAvsCvvResponses();
                $transactionError = isset($transactionResults['Error']) ? $transactionResults['Error'] : '';
                $transactionErrorCode = $transactionResults['ErrorCode'] ?? '';
                $avsResult = isset($transactionResults['AvsResult']) ? $transactionResults['AvsResult'] : '';
                $avsResultCode = $transactionResults['AvsResultCode'] ?? '';

                $cvvCardCodeResultCode = $transactionResults['CardCodeResultCode'] ?? '';

                // if fraud module is ON and transaction is successful
                if (
                    isset($transactionResults['ResultCode'])
                    && PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED
                    === (string)$transactionResults['ResultCode']
                ) {
                    if (
                        !$tranRefNum
                        && isset($preAuthResponse['avs_cvv_enabled'], $preAuthResponse['state'])
                        && PaymentInterface::EBIZCHARGE_FRAMEWORK_STATE_TYPE_ADMINHTML
                        !== $preAuthResponse['state']
                    ) {
                        // AVS result code
                        if (
                            !in_array($avsResultCode, $validAvsResponses) || PaymentInterface::CVV2_CARD_CODE_RESULT_M
                            !== $cvvCardCodeResultCode
                        ) {
                            $refNum = $transactionResults['RefNum'] ?? '';

                            /** transaction request params */
                            //   $this->voidPreAuthTransaction($refNum, $transactionRequestParams);

                            $transactionError = $transactionResults['Error'] ?? '';
                            $transactionErrorCode = $transactionResults['ErrorCode'] ?? '';
                            $avsResult = $transactionResults['AvsResult'] ?? '';

                            // if ($transactionError === "Approved") {
                            // $transactionError = "AVS|CVV mismatched.";
                            // }

                            $errorMsg = 'Payment authorization Error: ' . $transactionError . ', Error Code: '
                                . $transactionErrorCode;
                            $this->ebizchargeLogger->addCritical(__($errorMsg . ' AVS Code: '
                                . $avsResultCode . ', AVS Result: ' . $avsResult));

                            // AVS is by passed as instructed
                            $this->resetTransactionResults();

                            // set transaction data
                            $this->setTransactionResult($transactionResult);
                            $isTransactionSuccess = true;
                        }
                    }

                    $this->resetTransactionResults();
                    // set transaction data
                    $this->setTransactionResult($transactionResult);
                    $isTransactionSuccess = true;
                } elseif (
                    isset($transactionResults['ResultCode']) &&
                    PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_DECLINED ===
                    (string)$transactionResults['ResultCode']
                ) {
                    $this->resetTransactionResults();

                    $errorMsg = 'Payment authorization transaction has been declined: '
                        . $transactionResults['Error'] . ', Error Code: ' . $transactionResults['ErrorCode'];
                    $this->ebizchargeLogger->addCritical(__($errorMsg . ' AVS Code: '
                        . $transactionResults['AvsResultCode'] . ', AVS Result: ' . $transactionResults['AvsResult']));

                    // if declined
                    throw new LocalizedException(__(
                        'Payment authorization Error: The transaction has been declined: '
                        . $transactionResults['Error']
                    ));
                } else {
                    $transactionError = $transactionResults['Error'] ?? '';

                    $transactionErrorCode = $transactionResults['ErrorCode'] ?? '';
                    $avsResult = $transactionResults['AvsResult'] ?? '';
                    $avsResultCode = $transactionResults['AvsResultCode'] ?? '';

                    if (
                        isset($transactionResults['RefNum']) && PaymentInterface::EBIZCHARGE_COMMAND_CAPTURE
                        !== $this->getData('command')
                    ) {
                        $refNum = $transactionResults['RefNum'];
                        // transaction request params
                        $this->voidPreAuthTransaction($refNum, $transactionRequestParams);

                        // if ($transactionError === "Approved") {
                        //  $transactionError = "Could not authenticated AVS|CVV.";
                        // }

                        $errorMsg = 'Payment authorization could not authenticate AVS. Error:' . $transactionError
                            . ', Error Code ' . $transactionErrorCode;
                        $this->ebizchargeLogger->addCritical(__($errorMsg . ', AVS Code: ' . $avsResultCode
                            . ', AVS Result: ' . $avsResult));

                        $this->resetTransactionResults();

                        throw new LocalizedException(__('Payment authorization error: ' . $transactionError));
                    }

                    $errorMsg = 'Payment authorization could not authenticate AVS. Error:' . $transactionError
                        . ', Error Code ' . $transactionErrorCode;
                    $this->ebizchargeLogger->addCritical(__($errorMsg . ', AVS Code: ' . $avsResultCode
                        . ', AVS Result: ' . $avsResult));

                    $this->resetTransactionResults();

                    throw new LocalizedException(__(
                        'Payment authorization Error: The transaction has been declined: '
                        . $transactionResults['Error']
                    ));
                }
            } else {
                $this->resetTransactionResults();
                throw new LocalizedException(
                    __('Payment authorization error: Some thing went wrong')
                );
            }
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__('Exception payment authorization error: ' . $ex->getMessage()));
            //  $voidTransaction = $this->runVoidTransaction($payment, $isRecurring, true,
            // PaymentInterface::EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID);

            $this->resetTransactionResults();

            throw new LocalizedException(__($ex->getMessage()));
        }
        return $isTransactionSuccess;
    }

    /**
     * Set 3D Secure Data In Transaction params.
     */
    private function set3DSecureDataInTransaction(array $tranRequestParams = []): array
    {
        try {
            $ebiz3DSecureData = $this->coreSession->getEbiz3DSecureData();
            $command = $tranRequestParams['Command'] ?? '';

            if (
                isset(
                    $ebiz3DSecureData['CreditCardData'],
                    $tranRequestParams['CreditCardData'],
                    $ebiz3DSecureData['DSTransactionId']
                ) && !empty(isset($ebiz3DSecureData['DSTransactionId']))
                && Area::AREA_ADMINHTML !== EbizDataHelper::getAreaCode()
            ) {
                // Set EBiz 3DSecure data in Quote
                $this->checkoutSession->getQuote()->setData(
                    'ebiz_3d_secure_data',
                    json_encode($ebiz3DSecureData)
                );
                $this->coreSession->unsEbiz3DSecureData();

                $tranRequestParams['CreditCardData']['CAVV'] = $ebiz3DSecureData['CreditCardData']['CAVV'] ?? '';
                $tranRequestParams['CreditCardData']['XID'] = $ebiz3DSecureData['CreditCardData']['XID'] ?? '';
                $tranRequestParams['CreditCardData']['ECI'] = $ebiz3DSecureData['CreditCardData']['ECI'] ?? '';
                $tranRequestParams['CreditCardData']['Pares'] = $ebiz3DSecureData['CreditCardData']['Pares'] ?? '';

                $customFields[] = [
                    'Field' => 'DSTransactionId',
                    'Value' => $ebiz3DSecureData['DSTransactionId'],
                ];

                $tranRequestParams['CustomFields'] = $customFields;
            }
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(
                __('Exception while setting 3D Secure data in transaction: ') . $e->getMessage()
            );
        }

        return $tranRequestParams;
    }

    /**
     * Void Pre Auth Transaction.
     *
     * @param mixed $refNumber
     * @param mixed $transactionPayLoad
     *
     * @throws LocalizedException
     */
    public function voidPreAuthTransaction($refNumber = '', $transactionPayLoad = []): array
    {
        $voidTransactionResult = [];

        try {
            $storeId = $customer->getStoreId() ?? $this->getStoreId();
            $transactionPayLoad['tran']['RefNum'] = $refNumber;
            $transactionPayLoad['tran']['Command'] = PaymentInterface::EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID;

            $voidTransaction = $this->getClient($storeId)->runTransaction($transactionPayLoad);

            if (!empty($voidTransactionResult = $voidTransaction->runTransactionResult)) {
                $voidTransactionResult = (array)$voidTransactionResult;

                // if (isset($voidTransactionResult["ResultCode"]) &&
                // (string)$voidTransactionResult["ResultCode"] !==
                // PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED) {
                // throw new LocalizedException(__(
                // "Payment authorization error occurred during void of Payment. "
                // ));
                // }
            }
        } catch (Exception $exception) {
            $this->ebizchargeLogger->addCritical(__('Payment authorization error: ' . $exception->getMessage()));
            //  throw new LocalizedException(__("Payment authorization error: " . $exception->getMessage()));
        }

        return $voidTransactionResult;
    }

    /**
     * Render Pre Auth Ajax Transaction.
     *
     * @param mixed $transactionPayLoad
     *
     * @throws LocalizedException
     */
    public function renderPreAuthAjaxTransaction($transactionPayLoad = null): array
    {
        $paymentAuthenticatedResponse = [
            'payment_authenticated' => false,
            'trans_result' => 'E',
            'status' => false,
            'state' => EbizDataHelper::getAreaCode(),
            'avs_cvv_enabled' => false,
            'message' => __('Payment authorization error: AVS|CVV mismatched. '),
        ];
        $storeId = $customer->getStoreId() ?? $this->getStoreId();
        // unset session transaction results
        $this->coreSession->unsTransactionResults();
        $this->coreSession->unsPreAuthResponse();

        $tranRefNum = isset($transactionPayLoad['tran']['RefNum']) ? $transactionPayLoad['tran']['RefNum'] : false;

        if ($tranRefNum) {
            return $paymentAuthenticatedResponse;
        }

        try {
            $avsCvvCurrentSettings = $this->getEbizAvsCvvSettings();

            if ($avsCvvCurrentSettings['is_cvv_avs_enabled']) {
                if (!$avsCvvCurrentSettings['use_full_amount_for_avs']) {
                    // Avs CVV Use full amount for AVS
                    $transactionPayLoad['tran']['Details']['Subtotal'] = 0;
                    $transactionPayLoad['tran']['Details']['Amount'] = 0.05;
                    $transactionPayLoad['tran']['Details']['Tax'] = 0;
                    $transactionPayLoad['tran']['Details']['NonTax'] = true;
                    $transactionPayLoad['tran']['Details']['Duty'] = 0;
                    $transactionPayLoad['tran']['Details']['Shipping'] = 0;
                    $transactionPayLoad['tran']['LineItems'] = [];
                    $preAuthTransactionsPayLoad = $transactionPayLoad;
                }

                $transaction = $this->getClient($storeId)->runTransaction($transactionPayLoad);
                // set transaction results
                $this->coreSession->setTransactionResults($transaction);

                // Transaction Results
                if (!empty($transactionResult = $transaction->runTransactionResult)) {
                    $transactionResults = (array)$transactionResult;

                    $resultCode = isset($transactionResults['ResultCode'])
                        ? (string)$transactionResults['ResultCode'] : '';

                    // if fraud module is ON and transaction is successful
                    if (PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED === $resultCode) {
                        $paymentAuthenticatedResponse['trans_result'] = $resultCode;
                        $paymentAuthenticatedResponse['status'] = true;
                        $paymentAuthenticatedResponse['payment_authenticated'] = true;
                        $paymentAuthenticatedResponse['message'] = __('Success, the AVS|Cvv validated.');
                        $avsCvvAuthResponse = $this->validateAvsCvvResponse($transactionResults);

                        if (!$avsCvvAuthResponse['cvv_authenticated']) {
                            $paymentAuthenticatedResponse['trans_result'] = $resultCode;
                            $paymentAuthenticatedResponse['status'] = false;
                            $paymentAuthenticatedResponse['payment_authenticated'] = false;
                            $paymentAuthenticatedResponse['message'] = $avsCvvAuthResponse['cvv_message'];
                        }

                        if (!$avsCvvAuthResponse['avs_authenticated']) {
                            $paymentAuthenticatedResponse['trans_result'] = $resultCode;
                            $paymentAuthenticatedResponse['status'] = false;
                            $paymentAuthenticatedResponse['payment_authenticated'] = false;
                            $paymentAuthenticatedResponse['message'] = $avsCvvAuthResponse['avs_message'];
                        }
                    } elseif (PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_DECLINED === $resultCode) {
                        $paymentAuthenticatedResponse['status'] = false;
                        $paymentAuthenticatedResponse['message'] = __(
                            'Payment authorization Error: payment declined. '
                        );

                        $refNumber = $transactionResults['RefNum'];
                        $this->voidPreAuthTransaction($refNumber, $transactionPayLoad);
                    } else {
                        $paymentAuthenticatedResponse['trans_result'] = $resultCode;
                        $paymentAuthenticatedResponse['status'] = false;
                        $paymentAuthenticatedResponse['message'] = __(
                            'Payment authorization Error: AVS address mismatched '
                        );
                        $refNumber = $transactionResults['RefNum'];
                        $this->voidPreAuthTransaction($refNumber, $transactionPayLoad);
                    }
                }
            } else {
                $paymentAuthenticatedResponse = [
                    'payment_authenticated' => true,
                    'trans_result' => 'A',
                    'status' => true,
                    'state' => EbizDataHelper::getAreaCode(),
                    'message' => __('Payment authorization success, AVS|CVV matched. '),
                ];
            }

            if (!$avsCvvCurrentSettings['is_cvv_avs_enabled']) {
                $paymentAuthenticatedResponse['payment_authenticated'] = false;
                $paymentAuthenticatedResponse['status'] = false;
                $paymentAuthenticatedResponse['message'] = __(
                    'Payment authorization error: AVS|CVV mismatched.'
                );
            }
            // payment authenticated response
            $paymentAuthenticatedResponse['avs_cvv_enabled'] = $avsCvvCurrentSettings['is_cvv_avs_enabled'];
        } catch (Exception $exception) {
            $this->ebizchargeLogger->addError(__('Payment Authorization Error: ' . $exception->getMessage()));
            $paymentAuthenticatedResponse['message'] = __('Payment authorization error: '
                . $exception->getMessage());
            // throw new LocalizedException(__("Payment authorization Error: " . $exception->getMessage()));
        }

        $preAuthResponse = $this->coreSession->setPreAuthResponse($paymentAuthenticatedResponse);

        return $paymentAuthenticatedResponse;
    }

    /**
     * Validate Avs Cvv Response.
     *
     * @param array $transactionResults
     */
    public function validateAvsCvvResponse(array $transactionResults = []): array
    {
        $paymentAuthenticatedResponse = [
            'avs_authenticated' => false,
            'cvv_authenticated' => false,
            'status' => false,
            'avs_message' => __('Payment authorization error: avs mismatched. '),
            'cvv_message' => __('Payment authorization error: cvv mismatched. '),
        ];

        $resultCode = isset($transactionResults['ResultCode'])
            ? (string)$transactionResults['ResultCode'] : '';
        $cardCodeResultCode = isset($transactionResults['CardCodeResultCode'])
            ? (string)$transactionResults['CardCodeResultCode'] : '';
        $cardCodeResult = isset($transactionResults['CardCodeResult'])
            ? (string)$transactionResults['CardCodeResult'] : '';

        $avsResultCode = isset($transactionResults['AvsResultCode'])
            ? (string)$transactionResults['AvsResultCode'] : '';
        $avsResult = isset($transactionResults['AvsResult'])
            ? (string)$transactionResults['AvsResult'] : '';

        /**
         * avs validation response.
         */
        $avsValidationResponses = $this->customerFactory->create()->getValidAvsCvvResponses();

        $isPaymentAuthenticated = true;
        $paymentAuthenticatedResponse['avs_authenticated'] = true;
        $paymentAuthenticatedResponse['cvv_authenticated'] = true;
        $paymentAuthenticatedResponse['status'] = true;
        $paymentAuthenticatedResponse['avs_message'] = $avsResult;
        $paymentAuthenticatedResponse['cvv_message'] = $cardCodeResult;

        $cardCodeResults = [
            PaymentInterface::CVV2_CARD_CODE_RESULT_M,
            PaymentInterface::CVV2_CARD_CODE_RESULT_P,
        ];

        if (!in_array($cardCodeResultCode, $cardCodeResults) && 'A' !== $resultCode) {
            $isPaymentAuthenticated = false;
            $paymentAuthenticatedResponse['cvv_authenticated'] = false;
            $paymentAuthenticatedResponse['status'] = false;
            $paymentAuthenticatedResponse['cvv_message'] = __('Payment authorization error : '
                . $cardCodeResult);
        }

        if (!in_array($avsResultCode, $avsValidationResponses)) {
            $isPaymentAuthenticated = false;
            $paymentAuthenticatedResponse['avs_authenticated'] = false;
            $paymentAuthenticatedResponse['status'] = false;
            $paymentAuthenticatedResponse['avs_message'] = __('Payment authorization avs error : '
                . $avsResult);
        }

        return $paymentAuthenticatedResponse;
    }

    /**
     * Render Pre Auth Transaction.
     *
     * @param mixed $transactionPayLoad
     * @throws LocalizedException
     */
    public function renderPreAuthTransaction(mixed $transactionPayLoad = null): array
    {
        $paymentAuthenticatedResponse = [
            'payment_authenticated' => false,
            'status' => false,
            'state' => EbizDataHelper::getAreaCode(),
            'avs_cvv_enabled' => false,
            'message' => __('Payment authorization error: AVS|CVV mismatched. '),
        ];
        $storeId = $this->getStoreId();
        $tranRefNum = $transactionPayLoad['tran']['RefNum'] ?? false;

        if ($tranRefNum) {
            return $paymentAuthenticatedResponse;
        }

        try {
            $avsCvvCurrentSettings = $this->getEbizAvsCvvSettings();
            if ($avsCvvCurrentSettings['is_cvv_avs_enabled'] && !$avsCvvCurrentSettings['use_full_amount_for_avs']) {
                // Avs CVV Use full amount for AVS
                $transactionPayLoad['tran']['Details']['Subtotal'] = 0;
                $transactionPayLoad['tran']['Details']['Amount'] = 0.05;
                $transactionPayLoad['tran']['Details']['Tax'] = 0;
                $transactionPayLoad['tran']['Details']['NonTax'] = true;
                $transactionPayLoad['tran']['Details']['Duty'] = 0;
                $transactionPayLoad['tran']['Details']['Shipping'] = 0;
                $transactionPayLoad['tran']['LineItems'] = [];
                $preAuthTransactionsPayLoad = $transactionPayLoad;

                $transaction = $this->getClient($storeId)->runTransaction($preAuthTransactionsPayLoad);

                // Transaction Results
                if (!empty($transactionResult = $transaction->runTransactionResult)) {
                    $transactionResults = (array)$transactionResult;

                    //  $this->coreSession->setTransactionResults();

                    $resultCode = isset($transactionResults['ResultCode'])
                        ? (string)$transactionResults['ResultCode'] : '';

                    // if fraud module is ON and transaction is successfull
                    if (PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED === $resultCode) {
                        $paymentAuthenticatedResponse['status'] = true;
                        $paymentAuthenticatedResponse['payment_authenticated'] = true;
                        $paymentAuthenticatedResponse['message'] = __('Success, the AVS|Cvv validated.');
                        $avsCvvAuthResponse = $this->validateAvsCvvResponse($transactionResults);

                        if (!$avsCvvAuthResponse['cvv_authenticated']) {
                            $paymentAuthenticatedResponse['status'] = false;
                            $paymentAuthenticatedResponse['payment_authenticated'] = false;
                            $paymentAuthenticatedResponse['message'] = $avsCvvAuthResponse['cvv_message'];
                        }

                        if (!$avsCvvAuthResponse['avs_authenticated']) {
                            $paymentAuthenticatedResponse['status'] = false;
                            $paymentAuthenticatedResponse['payment_authenticated'] = false;
                            $paymentAuthenticatedResponse['message'] = $avsCvvAuthResponse['avs_message'];
                        }
                    } elseif (PaymentInterface::EBIZCHARGE_RESPONSE_RESULT_CODE_DECLINED === $resultCode) {
                        $paymentAuthenticatedResponse['status'] = false;
                        $paymentAuthenticatedResponse['message'] = __(
                            'Payment authorization Error: payment declined.'
                        );
                    } else {
                        $paymentAuthenticatedResponse['status'] = false;
                        $paymentAuthenticatedResponse['message'] = __(
                            'Payment authorization Error: AVS address mismatched'
                        );
                    }

                    $refNumber = $transactionResults['RefNum'];
                    $this->voidPreAuthTransaction($refNumber, $transactionPayLoad);
                }
            } else {
                $paymentAuthenticatedResponse = [
                    'payment_authenticated' => true,
                    'status' => true,
                    'state' => EbizDataHelper::getAreaCode(),
                    'message' => __('Payment authorization error: AVS|CVV mismatched. '),
                ];
            }

            if (!$avsCvvCurrentSettings['is_cvv_avs_enabled']) {
                $paymentAuthenticatedResponse['payment_authenticated'] = false;
                $paymentAuthenticatedResponse['status'] = false;
                $paymentAuthenticatedResponse['message'] = __(
                    'Payment authorization error: AVS|CVV mismatched.'
                );
            }
            // payment authenticated response
            $paymentAuthenticatedResponse['avs_cvv_enabled'] = $avsCvvCurrentSettings['is_cvv_avs_enabled'];
        } catch (Exception $exception) {
            $this->ebizchargeLogger->addError(__('Payment Authorization Error: ' . $exception->getMessage()));
            $paymentAuthenticatedResponse['message'] = __('Payment authorization error: '
                . $exception->getMessage());
            // throw new LocalizedException(__("Payment authorization Error: " . $exception->getMessage()));
        }

        return $paymentAuthenticatedResponse;
    }

    /**
     * Reset Transaction Results.
     */
    public function resetTransactionResults()
    {
        $this->coreSession->unsTransactionResults();
        $this->coreSession->unsPreAuthResponse();
    }

    /**
     * Set Transactional Data
     *
     * @param mixed|null $transaction
     * @return void
     */
    public function setTransactionResult(mixed $transaction = null)
    {
        $this->ebizchargeLogger->addInfo(__('Setting transaction results to payment object.'));
        if (isset($transaction->Error) && 'Approved' !== $transaction->Error) {
            $this->ebizchargeLogger->addError(__('Transaction Failed with Error: ' . $transaction->Error));
        }

        $this->transactionResponseData = $transaction;

        $this->result = $transaction->Result;
        $this->resultcode = $transaction->ResultCode;
        $this->authcode = $transaction->AuthCode ?? '';
        $this->refnum = $transaction->RefNum;
        $this->batch = $transaction->BatchNum;
        $this->avs_result = $transaction->AvsResult;
        $this->avs_result_code = $transaction->AvsResultCode ?? '';
        $this->cvv2_result = $transaction->CardCodeResult ?? '';
        $this->cvv2_result_code = $transaction->CardCodeResultCode ?? '';
        $this->vpas_result_code = $transaction->VpasResultCode;
        $this->convertedamount = $transaction->ConvertedAmount;
        $this->convertedamountcurrency = $transaction->ConvertedAmountCurrency;
        $this->conversionrate = $transaction->ConversionRate;
        $this->error = $transaction->Error ?? '';
        $this->errorcode = $transaction->ErrorCode;
        $this->custnum = $transaction->CustNum;
        $this->avs = '';
        $this->cvv2 = '';
        $this->acsurl = $transaction->AcsUrl;
        $this->pareq = $transaction->Payload;
        $this->batch_num = $transaction->BatchNum;
        $this->batch_ref_num = $transaction->BatchRefNum;
        $this->is_duplicate = $transaction->isDuplicate;
        $this->card_level_result_code = $transaction->CardLevelResultCode ?? '';
        $this->card_level_result = $transaction->CardLevelResult ?? '';
        $this->card_code_result = $transaction->CardCodeResult ?? '';
        $this->card_code_result_code = $transaction->CardCodeResultCode ?? '';
        $this->auth_code = $transaction->AuthCode ?? '';
        $this->auth_amount = (string)$transaction->AuthAmount ?? '';
        $this->payment_status = $transaction->Status ?? '';
        $this->payment_status_code = $transaction->StatusCode ?? '';
    }

    /**
     * Creat Recurring Order.
     *
     * @return array
     */
    public function creatRecurringOrder(mixed $recurringOrderParams = null)
    {
        return $this->recurringFactory->create()->addRecurringOrderItems($recurringOrderParams);
    }

    /**
     * Run recurring payments for EBizCharge
     *
     * @param InfoInterface|null $payment
     * @return $this
     * @throws LocalizedException
     */
    public function runRecurring(InfoInterface $payment = null)
    {
        // phpcs:ignore
        sleep(5);

        $order = $payment->getOrder();

        foreach ($order->getAllVisibleItems() as $item) {
            $itemOptions = $item->getProductOptions();

            if (
                isset($itemOptions['info_buyRequest']['recurring'])
                && !empty($itemOptions['info_buyRequest']['recurring']['rec_activate'])
            ) {
                $itemRecurringBuyRequest = $itemOptions['info_buyRequest'];
                $itemRecurringOptions = $itemOptions['info_buyRequest']['recurring'];
                $itemRecurringQty = $itemOptions['info_buyRequest']['qty'];
                $getMainProductId = $item->getProductId();
                $getParentProductId = (!empty($itemRecurringBuyRequest['product']))
                    ? $itemRecurringBuyRequest['product'] : $getMainProductId;
                $getChildProductId = (!empty($itemRecurringBuyRequest['currentpid']))
                    ? $itemRecurringBuyRequest['currentpid'] : $getMainProductId;
                $getProductSimpleName = (!empty($itemOptions['simple_name']))
                    ? $itemOptions['simple_name'] : $item->getName();
                $recActivateValue = $itemRecurringOptions['rec_activate'];
                $recSdate = $this->strToTime($itemRecurringOptions['sdate']);
                $recEdate = $itemRecurringOptions['edate'] ?? null;
                $itemPrice = (!empty($item->getSpecialPrice())) ? $item->getSpecialPrice() : $item->getPrice();
                // apply coupon discount .. we need to apply current price in recurring orders,
                // how this work in recurring orders
                $itemDiscountAmount = (!empty($item->getDiscountAmount())) ? $item->getDiscountAmount() : 0;
                $singleItemDiscountAmount = ($itemDiscountAmount / $item->getQtyOrdered());
                $recurringItemTotalDiscount = ($singleItemDiscountAmount * $itemRecurringQty);
                $recurringItemAmountBeforeDisc = ($itemPrice * $itemRecurringQty);
                $recurringItemFinalAmount = ($recurringItemAmountBeforeDisc - $recurringItemTotalDiscount);

                if (
                    (1 == $recActivateValue) && ($recurringItemFinalAmount > 0)
                    || ('virtual' == $item->getProductType())
                ) {
                    if (!empty($itemRecurringOptions['rec_frequency'])) {
                        $recurringBilling = [
                            'Amount' => $recurringItemFinalAmount,
                            'Tax' => ($item->getTaxAmount() * $itemRecurringQty),
                            'Enabled' => true,
                            'Start' => $recSdate,
                            'Schedule' => $itemRecurringOptions['rec_frequency'],
                            'ScheduleName' => $getChildProductId . '-' . $this->orderid . '-'
                                . $order->getCustomerId() . '-' . $itemRecurringOptions['rec_frequency'] . '-'
                                . $this->recurringMethodId,
                            'ReceiptNote' => 'Item [' . $getChildProductId . '-' . $getProductSimpleName
                                . '] recurring payment added.',
                            'ReceiptTemplateName' => false,
                            'SendCustomerReceipt' => true,
                        ];
                    }

                    if (!empty($recEdate)) {
                        $recurringBilling['Expire'] = $this->strToTime($recEdate);
                        $recurringBilling['Next'] = $recSdate;
                        $recIndefinitely = isset($itemRecurringOptions['rec_indefinitely']) ? 1 : 0;
                    } else {
                        $recurringBilling['Expire'] = $this->strToTime($itemRecurringOptions['sdate'], '10');
                        $recurringBilling['Next'] = $recSdate;
                        $recIndefinitely = '1';
                    }

                    $this->addRecurring(
                        $getChildProductId,
                        $getParentProductId,
                        $getProductSimpleName,
                        $itemRecurringQty,
                        $recurringBilling,
                        $recIndefinitely,
                        $order
                    );
                } else {
                    // logging info
                    $this->ebizchargeLogger->addInfo(__('Subscription not added because product('
                        . $getProductSimpleName . ') price is: ' . $recurringItemFinalAmount));
                }
            }
        }

        return $this;
    }

    /**
     * Str To Time
     *
     * @param mixed $data
     * @param mixed $plusYears
     * @return string
     */
    public function strToTime(mixed $data = "", mixed $plusYears = '')
    {
        if (!empty($plusYears)) {
            $date = date('Y-m-d', strtotime('+' . $plusYears . ' years', strtotime($data)));
        } else {
            $date = date('Y-m-d', strtotime($data));
        }

        return $date;
    }

    /**
     * Adding recurring to EBizCharge
     *
     * @param mixed $getChildProductId
     * @param mixed $getParentProductId
     * @param mixed $getProductSimpleName
     * @param mixed $itemRecurringQty
     * @param mixed $recurringBilling
     * @param mixed $recIndefinitely
     * @param Order|null $order
     * @return $this|false
     * @throws LocalizedException
     */
    public function addRecurring(
        mixed $getChildProductId = "",
        mixed $getParentProductId = "",
        mixed $getProductSimpleName = "",
        mixed $itemRecurringQty = "",
        mixed $recurringBilling = "",
        mixed $recIndefinitely = "",
        Order $order = null
    )
    {
        $customer = $this->getCustomer($order->getCustomerId());
        if (null == $customer) {
            $this->ebizchargeLogger->addInfo(__('Recurring not added because customer Not found. Id: '
                . $order->getCustomerId()));

            return false;
        }

        try {
            $storeId = $customer->getStoreId() ?? $this->getStoreId();
            $paymentMethodName = $this->getRecurringPaymentMethodName($customer);

            $addRecurringParameters = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'customerInternalId' => $customer->CustomerInternalId,
                'paymentMethodProfileId' => $this->recurringMethodId,
                'recurringBilling' => $recurringBilling,
            ];

            $transaction = $this->getClient($storeId)->ScheduleRecurringPayment($addRecurringParameters);

            if (!empty($scheduledPaymentInternalId = $transaction->ScheduleRecurringPaymentResult)) {
                $customerShippingAddressId = '';
                $customerBillingAddressId = '';

                if (!empty($order->getShippingAddress())) {
                    $customerShippingAddressId = $order->getShippingAddress()->getCustomerAddressId();
                }
                if (!empty($order->getBillingAddress())) {
                    $customerBillingAddressId = $order->getBillingAddress()->getCustomerAddressId();
                }

                $amount = $recurringBilling['Amount'];
                $shippingMethodName = $order->getShippingMethod();

                $recurringDates = $this->getRecurringScheduledDates($scheduledPaymentInternalId);

                $insertQueryParameters = [
                    'option' => 'insert',
                    'tableName' => 'ebizcharge_recurring',
                    'data' => [
                        'rec_status' => 0,
                        'rec_indefinitely' => $recIndefinitely,
                        'mage_cust_id' => $this->custid,
                        'mage_order_id' => $this->orderid,
                        'mage_item_id' => $getChildProductId,
                        'mage_parent_item_id' => $getParentProductId,
                        'mage_item_name' => $getProductSimpleName,
                        'qty_ordered' => $itemRecurringQty,
                        'eb_rec_start_date' => $recurringBilling['Start'],
                        'eb_rec_end_date' => $recurringBilling['Expire'],
                        'eb_rec_frequency' => $recurringBilling['Schedule'],
                        'eb_rec_method_id' => $this->recurringMethodId,
                        'eb_rec_scheduled_payment_internal_id' => $scheduledPaymentInternalId,
                        'eb_rec_total' => count($recurringDates),
                        'eb_rec_processed' => 0,
                        'eb_rec_next' => $this->getNextRecurringDate($recurringDates),
                        'eb_rec_remaining' => count($recurringDates),
                        // phpcs:ignore
                        'eb_rec_due_dates' => serialize($recurringDates),
                        'shipping_address_id' => $customerShippingAddressId,
                        'billing_address_id' => $customerBillingAddressId,
                        'amount' => $amount,
                        'payment_method_name' => $paymentMethodName,
                        'shipping_method' => $shippingMethodName,
                    ],
                ];

                $recurringId = $this->runInsertQuery($insertQueryParameters);
                $this->insertScheduleDates($recurringId, $recurringDates);
            }
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__('Exception occured AddRecurring ' . $ex->getMessage()));
            throw new LocalizedException(__('Exception occured AddRecurring ' . $ex->getMessage()));
        }

        return $this;
    }

    /**
     * Get Recurring Payment Method Name
     *
     * @param Customer|null $ebizCustomer
     * @return string
     */
    public function getRecurringPaymentMethodName(Customer $ebizCustomer = null)
    {
        // get recurring payment method name
        $paymentMethodName = '';
        $profiles = $ebizCustomer->PaymentMethodProfiles->PaymentMethodProfile;

        if (is_object($profiles)) {
            $paymentMethods[] = $profiles;
        } else {
            $paymentMethods = $profiles;
        }

        foreach ($paymentMethods as $paymentMethod) {
            if ($paymentMethod->MethodID == $this->recurringMethodId) {
                $paymentMethodName = $paymentMethod->MethodName;

                break;
            }
        }

        return $paymentMethodName;
    }

    /**
     * Get Recurring Scheduled Dates
     *
     * @param mixed $schedulePaymentId
     * @return array|bool|float|int|mixed|string|null
     * @throws LocalizedException
     */
    public function getRecurringScheduledDates(mixed $schedulePaymentId = "")
    {
        try {
            $storeId = $this->getStoreId();
            $transaction = $this->getClient($storeId)->GetScheduledDates(
                [
                    'securityToken' => $this->getUeSecurityToken($storeId),
                    'scheduledPaymentInternalId' => $schedulePaymentId,
                ]
            );

            if (!empty($transaction->GetScheduledDatesResult->ScheduledDates)) {
                $dates = $transaction->GetScheduledDatesResult->ScheduledDates;

                return $this->json->unserialize($dates);
            }
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__(
                'Exception Occurred during getting recurring scheduled dates : '
                . __(__METHOD__ . $ex->getMessage())
            ));

            throw new LocalizedException(__(
                'Exception Occurred during getting recurring scheduled dates : '
                . __(__METHOD__ . $ex->getMessage())
            ));
        }

        return [];
    }

    /**
     * Get Next Recurring Date
     *
     * @param array $recurringDates
     * @return mixed|string|null
     */
    public function getNextRecurringDate(array $recurringDates = [])
    {
        if (isset($recurringDates[0]) && $recurringDates[0] == date('Y-m-d')) {
            return $recurringDates[1] ?? null;
        }
        return $recurringDates[0] ?? null;
    }

    /**
     * Run Insert Query
     *
     * @param array $queryParameters
     * @return string
     */
    public function runInsertQuery(array $queryParameters = [])
    {
        $resource = $this->resourceConnection;
        $tableName = $resource->getTableName($queryParameters['tableName']);
        $connection = $resource->getConnection();
        $connection->insert($tableName, $queryParameters['data']);

        return $connection->lastInsertId($tableName);
    }

    /**
     * Insert Schedule Dates
     *
     * @param mixed $recurringId
     * @param mixed $recurringDates
     * @return void
     */
    public function insertScheduleDates(mixed $recurringId = "", mixed $recurringDates = "")
    {
        if (is_array($recurringDates) && !empty($recurringDates)) {
            $dates = [];
            foreach ($recurringDates as $date) {
                $dates[] = [
                    'recurring_id' => $recurringId,
                    'recurring_date' => $date,
                ];
            }
            // delete all existing dates first
            $this->deleteRecurringScheduleDates($recurringId);

            $this->runBulkInsertQuery('ebizcharge_recurring_dates', $dates);
        }
    }

    /**
     * Delete Recurring Schedule Dates
     *
     * @param mixed $recurringId
     * @return int
     */
    public function deleteRecurringScheduleDates(mixed $recurringId = "")
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('ebizcharge_recurring_dates');
        return $connection->delete($tableName, ['recurring_id = ?' => $recurringId]);
    }

    /**
     * Run Bulk Insert Query
     *
     * @param mixed $tableName
     * @param mixed $data
     * @return void
     */
    public function runBulkInsertQuery(mixed $tableName = "", mixed $data = [])
    {
        $resource = $this->resourceConnection;
        $tableName = $resource->getTableName($tableName);
        $resource->getConnection()->insertMultiple($tableName, $data);
    }

    /**
     * Get Transaction Data.
     *
     * @return null|mixed
     */
    public function getTransactionData()
    {
        return $this->transactionResponseData;
    }

    /**
     * Pre-Auth Full Amount Transaction
     *
     * @param InfoInterface|null $payment
     * @param bool $isRefund
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function preAuthFullAmountTransaction(?InfoInterface $payment = null, bool $isRefund = false)
    {
        $storeId = (int)$payment->getOrder()->getStoreId();
        $isAvsFullAmountEnabled = $this->configFactory->create()->isAvsFullAmountEnabled($storeId);

        if (!$isAvsFullAmountEnabled || $isRefund) {
            return;
        }

        $paymentInfoParams = $this->prepareTransactionInfoParams($payment);
        $paymentOptionType = $paymentInfoParams['ebzc_option_type'] ?? '';
        $paymentOptionType = strtolower($paymentOptionType);
        $creditCardPayment = strtolower(PaymentInterface::EBIZCHARGE_METHOD_TYPE_CREDIT_CARD);
        $ebizPaymentOption = $paymentInfoParams['ebzc_option'] ?? '';
        $ebizPaymentOption = strtolower($ebizPaymentOption);
        $newPaymentOption = strtolower(PaymentInterface::EBIZCHARGE_PAYMENT_OPTION_TYPE_NEW);

        if (
            $paymentOptionType === $creditCardPayment
            && $ebizPaymentOption === $newPaymentOption
        ) {
            $transactionParams['payment'] = $paymentInfoParams;
            $preAuthTransactionResp = $this->customerFactory->create()
                ->runPreAuthTransaction($transactionParams, '', true);

            $resultCode = $preAuthTransactionResp['response']['ResultCode'] ?? '';
            $errorCode = $preAuthTransactionResp['response']['ErrorCode'] ?? '';
            $errorMessage = $preAuthTransactionResp['response']['Error'] ?? $preAuthTransactionResp['message'];

            // Void pre-auth transaction
            if ($resultCode && 'D' !== $resultCode) {
                $voidTransactionParams = $transactionParams;
                $voidTransactionParams['RefNum'] = $preAuthTransactionResp['response']['RefNum'];
                $command = PaymentInterface::EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID;
                $voidResponse = $this->customerFactory->create()
                    ->voidPreAuthTransaction($voidTransactionParams, $command, true);
            }

            if ($preAuthTransactionResp['error'] || 'A' !== $resultCode) {
                throw new LocalizedException(
                    __('Payment authorization transaction has been declined: ' . $errorMessage
                        . ($errorCode ? ' [' . $errorCode . ']' : ''))
                );
            }
        }
    }

    /**
     * Get Recurring Frequencies At Ebizcharge.
     *
     * @return array
     */
    public function getRecurringFrequenciesAtEbizCharge()
    {
        /**
         * Frequencies.
         */
        $recurringFrequencies = [];

        try {
            $storeId = $this->getStoreId();
            $frequenciesParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
            ];

            if ($this->getClient($storeId)) {
                $frequenciesResults = $this->getClient($storeId)->GetRecurringFrequencyList($frequenciesParams);

                if (is_object($frequenciesResults->GetRecurringFrequencyListResult)) {
                    $frequenciesResultSet = (array)$frequenciesResults->GetRecurringFrequencyListResult;

                    // if we have frequencies at EBizCharge
                    if (
                        isset($frequenciesResultSet['RecurringFrequency'])
                        && count($frequenciesResultSet['RecurringFrequency']) > 0
                    ) {
                        $recFrequencies = $frequenciesResultSet['RecurringFrequency'];
                        // if (count($recFrequencies) > 0) {
                        foreach ($recFrequencies as $recFrequency) {
                            $recurringFrequencies[] = (array)$recFrequency;
                        }
                        // }
                    }
                }
            }
        } catch (Exception $exception) {
            $this->ebizchargeLogger->addCritical(__('Error occurred during fetching frequencies.'
                . $exception->getMessage()));
        }

        return $recurringFrequencies;
    }

    /**
     * Is Card Type ACH.
     *
     * @return bool
     */
    public function isCardTypeACH()
    {
        return self::ACH === $this->cardtype;
    }

    /**
     * Run Update Query Customer
     *
     * @param mixed $tableName
     * @param mixed $column
     * @param mixed $ecItemSyncStatus
     * @param mixed $ecItemInternalId
     * @param mixed $ecCustomerId
     * @param mixed $ebizCustomerToken
     * @param mixed $entityId
     * @return bool
     */
    public function runUpdateQueryCustomer(
        mixed $tableName = "",
        mixed $column = "",
        mixed $ecItemSyncStatus = "",
        mixed $ecItemInternalId = "",
        mixed $ecCustomerId = "",
        mixed $ebizCustomerToken = "",
        mixed $entityId = ""
    )
    {
        $timeNow = date('Y-m-d h:i:s');
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName($tableName);

        $dataToUpdate = [
            'ec_' . $column . '_sync_status' => $ecItemSyncStatus,
            'ec_' . $column . '_internalid' => $ecItemInternalId,
            'ec_' . $column . '_id' => $ecCustomerId,
            'ec_' . $column . '_token' => $ebizCustomerToken,
            'ec_' . $column . '_lastsyncdate' => $timeNow,
        ];
        $where = ['entity_id = ?' => $entityId];

        try {
            $connection->beginTransaction();
            $connection->update($tableName, $dataToUpdate, $where);

            $this->ebizchargeLogger->addInfo(__('Record updated to the table ' . $tableName));
            $connection->commit();

            return true;
        } catch (Exception $e) {
            $this->ebizchargeLogger->addCritical(__('Exception occured ' . $e->getMessage()));
            $connection->rollBack();

            return false;
        }
    }

    /**
     * Prepare Envoirenment Prefix
     *
     * @param mixed $ebizId
     * @return string
     */
    public function prepareEnvoirnmentPrefix(mixed $ebizId = '')
    {
        $configFactory = $this->configFactory->create();
        $envoirnmentPrefix = $configFactory->getEnvoirnmentPrefix() ?
            $configFactory->getEnvoirnmentPrefix() : self::ENVOIRNMENT_PREFIX;
        $ebizId = !empty($ebizId) ? (string)$ebizId : '';
        $newEbizId = (string)$ebizId;
        if (!empty($ebizId)) {
            if (false === strpos($ebizId, $envoirnmentPrefix)) {
                $newEbizId = $envoirnmentPrefix . '-' . $ebizId;
            }
        }
        return (string)$newEbizId;
    }

    /**
     * Delete Customer Payment Method.
     *
     * @param mixed $customerToken
     * @param mixed $paymentMethodId
     * @return array
     */
    public function deleteCustomerPaymentMethod(mixed $customerToken = "", mixed $paymentMethodId = "")
    {
        $deletePaymentMethodResp = [
            'error' => true,
            'message' => __('Error occurred during deleting the Payment Method: ' . $paymentMethodId),
        ];
        $storeId = $this->getStoreId();

        try {
            $params = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'customerToken' => $customerToken,
                'paymentMethodId' => $paymentMethodId,
            ];
            $storeId = $this->getStoreId();
            $paymentMethodResp = $this->getClient($storeId)->deleteCustomerPaymentMethodProfile($params);

            if ($paymentMethodResp->DeleteCustomerPaymentMethodProfileResult) {
                $deletePaymentMethodResp = [
                    'error' => false,
                    'message' => __('Payment Method: ' . $paymentMethodId . ' has been successfully deleted.'),
                ];
                $this->ebizchargeLogger->addInfo(__('Payment Method: ' . $paymentMethodId
                    . ' has been successfully deleted.'));
            }
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__(
                'Exception occurred during deleting the Payment Method Error: ' . $ex->getMessage()
            ));
            $deletePaymentMethodResp = [
                'error' => true,
                'message' => __('Exception occurred during deleting the Payment Method Error: '
                    . $ex->getMessage()),
            ];
        }

        return $deletePaymentMethodResp;
    }

    /**
     * Add new payment method and process the transaction.
     *
     * A user is logged in option is existing customer only
     * AddCustomerPaymentMethodProfile
     *
     * @param mixed $customerId
     * @param mixed $ebzMethodId
     * @param InfoInterface|null $paymentObj
     * @return bool
     * @throws LocalizedException
     */
    public function addNewPaymentProcess(
        mixed         $customerId = "",
        mixed         $ebzMethodId = "",
        InfoInterface $paymentObj = null
    ): bool
    {
        try {
            $customer = $this->customerFactory->create()->load($customerId);
            $customerToken = $customer->getEcCustToken();
            $ebizPaymentMethodId = $ebzMethodId;

            // Case 2 Local = Yes, Live = No
            if (!$customer->getEcCustToken() && !$ebizPaymentMethodId) {
                $this->ebizchargeLogger->addInfo(__(__METHOD__
                    . ': Customer does not exists, so adding customer and run transaction.'));

                // saving the customer to EBizCharge Gateway and run customer transactions
                return $this->addCustomerAndRunCustomerTransaction($customerId, $paymentObj);
            }
            $this->ebizchargeLogger->addInfo(__(__METHOD__
                . ': Customer already exist and running transaction.'));

            // Case 5 Local = Yes, Live = Yes , Token = Same
            // run customer transaction
            return $this->savedTransactionToEbizcharge($customerToken, $ebizPaymentMethodId, $paymentObj);
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__(
                'SoapFault: Exception occurred during adding Payment Method: Error: ' . __METHOD__
                . $ex->getMessage()
            ));

            throw new LocalizedException(__(
                'SoapFault: Exception occurred during adding Payment Method: Error: ' . __METHOD__
                . $ex->getMessage()
            ));
        }
    }

    /**
     * Get Saved Accounts
     *
     * @param mixed $customerToken
     * @param mixed $methodType
     * @return array
     */
    public function getSavedAccounts(mixed $customerToken = "", mixed $methodType = 'check'): array
    {
        if (!empty($customerToken)) {
            $paymentMethods = $this->getCustomerPaymentMethods($customerToken);
            $accounts = [];
            if (count($paymentMethods) > 0) {
                foreach ($paymentMethods as $payment) {
                    if ($payment->MethodType == $methodType) {
                        $accounts[] = $payment;
                    }
                }
            }
            return $accounts;
        }
        return [];
    }

    /**
     * Get Customer Payment Methods
     *
     * @param mixed $customerToken
     * @return array
     */
    public function getCustomerPaymentMethods(mixed $customerToken = '')
    {
        if (!empty($customerToken)) {
            try {
                $storeId = $this->getStoreId();
                $soapClient = $this->getClient($storeId);

                if ($soapClient) {
                    $methodProfiles = $soapClient->getCustomerPaymentMethodProfiles(
                        [
                            'securityToken' => $this->getUeSecurityToken($storeId),
                            'customerToken' => $customerToken,
                        ]
                    );

                    if (!isset($methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile)) {
                        $paymentMethods = [];
                    } elseif (
                        is_array($methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile)
                        && count($methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile)
                        > 1
                    ) {
                        $paymentMethods
                            = $methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile;
                    } else {
                        $paymentMethods[]
                            = $methodProfiles->GetCustomerPaymentMethodProfilesResult->PaymentMethodProfile;
                    }
                } else {
                    return [];
                }

                return $paymentMethods;
            } catch (Exception $ex) {
                $this->ebizchargeLogger->addError(__('Exception occurred during getting Payment Methods '
                    . $ex->getMessage()));

                return [];
                // throw new \Magento\Framework\Exception\LocalizedException(__('SoapFault: ' . $ex->getMessage()));
            }
        }

        return [];
    }

    /**
     * Get Saved Accounts
     *
     * @param mixed $customerToken
     * @param mixed $methodType
     * @return array
     */
    public function getSavedBankAccounts(mixed $customerToken = "", mixed $methodType = 'check'): array
    {
        if (!empty($customerToken)) {
            $paymentMethods = $this->getCustomerPaymentMethods($customerToken);
            $accounts = [];
            if (count($paymentMethods) > 0) {
                foreach ($paymentMethods as $payment) {
                    if (isset($payment->MethodType) && $payment->MethodType === $methodType) {
                        $accounts[] = $payment;
                    }
                }
            }
            return $accounts;
        }
        return [];
    }

    /**
     * Set Default Payment Method
     *
     * @param mixed $customerToken
     * @param mixed $methodId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function setDefaultPaymentMethod(mixed $customerToken = "", mixed $methodId = ""): bool
    {
        $storeId = $this->getStoreId();
        $setDefaultMethod = $this->getClient($storeId)->SetDefaultCustomerPaymentMethodProfile(
            [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'customerToken' => $customerToken,
                'paymentMethodId' => $methodId,
            ]
        );

        if (isset($setDefaultMethod->SetDefaultCustomerPaymentMethodProfileResult)) {
            return true;
        }

        return false;
    }

    /**
     * Update Process.
     *
     * Function Change #5 Ebiz Method Senario #5
     * a user is logged in option is existing customer pay
     * from saved payment methods and update card details
     *
     * @param mixed $ebzcCustomerId
     * @param mixed $ebzcMethodId
     * @param InfoInterface|null $payment
     * @return bool
     * @throws LocalizedException
     */
    public function updateProcess(
        mixed         $ebzcCustomerId = "",
        mixed         $ebzcMethodId = "",
        InfoInterface $payment = null
    ): bool
    {
        $storeId = $this->getStoreId();
        $ueSecurityToken = $this->getUeSecurityToken($storeId);
        $storeId = $this->getStoreId();

        try {
            $paymentMethodProfile = $this->getCustomerPaymentMethodProfile($ebzcCustomerId, $ebzcMethodId);

            $paymentMethodProfile->AccountHolderName = !empty($paymentMethodProfile->AccountHolderName)
                ? $paymentMethodProfile->AccountHolderName
                : $payment->getCcOwner();
            $paymentMethodProfile->CardNumber = 'XXXXXX' . substr((string)$paymentMethodProfile->CardNumber, 6);
            $paymentMethodProfile->CardExpiration = $payment->getCcExpYear() . '-' . $payment->getCcExpMonth();

            if (null != $payment->getEbzcAvsStreet()) {
                $paymentMethodProfile->AvsStreet = $payment->getEbzcAvsStreet();
            } else {
                if (null != $payment->getAdditionalInformation('ebzc_avs_street')) {
                    $paymentMethodProfile->AvsStreet = $payment->getAdditionalInformation('ebzc_avs_street');
                } else {
                    $paymentMethodProfile->AvsStreet = $this->billstreet;
                }
            }

            if (null != $payment->getEbzcAvsZip()) {
                $paymentMethodProfile->AvsZip = $payment->getEbzcAvsZip();
            } else {
                if (null != $payment->getAdditionalInformation('ebzc_avs_zip')) {
                    $paymentMethodProfile->AvsZip = $payment->getAdditionalInformation('ebzc_avs_zip');
                } else {
                    $paymentMethodProfile->AvsZip = $this->billzip;
                }
            }

            $updatedMethodProfile = $this->getClient($storeId)->updateCustomerPaymentMethodProfile(
                [
                    'securityToken' => $ueSecurityToken,
                    'customerToken' => $ebzcCustomerId,
                    'paymentMethodProfile' => $paymentMethodProfile,
                ]
            );

            if (isset($updatedMethodProfile->UpdateCustomerPaymentMethodProfileResult)) {
                return $this->savedTransactionToEbizcharge($ebzcCustomerId, $ebzcMethodId, $payment);
            }

            throw new LocalizedException(__('Unable to update card.'));
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__('Exception occured of SoapFault: ' . $ex->getMessage()));

            throw new LocalizedException(__('Exception occured of SoapFault: ' . $ex->getMessage()));
        }
    }

    /**
     * Get Customer Payment Method Profile
     *
     * @param mixed $customerEbizToken
     * @param mixed|null $ebizPaymentMethodId
     * @return null
     */
    public function getCustomerPaymentMethodProfile(
        mixed $customerEbizToken = "",
        mixed $ebizPaymentMethodId = null
    )
    {
        try {
            $storeId = $this->getStoreId();
            if ($this->getClient($storeId)) {
                $paymentMethod = $this->getClient($storeId)->GetCustomerPaymentMethodProfile(
                    [
                        'securityToken' => $this->getUeSecurityToken($storeId),
                        'customerToken' => $customerEbizToken,
                        'paymentMethodId' => $ebizPaymentMethodId,
                    ]
                );

                if (isset($paymentMethod->GetCustomerPaymentMethodProfileResult)) {
                    return $paymentMethod->GetCustomerPaymentMethodProfileResult;
                }
            }

            return null;
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__(
                'Exceptoin occurred during fetching the Customer payment Method : ' . $ex->getMessage()
            ));

            return null;
        }
    }

    /**
     * Get Default Limits.
     *
     * @return int[]
     */
    public function getDefaultLimits()
    {
        // limits
        return [
            10 => 10,
            15 => 15,
            20 => 20,
            50 => 50,
            100 => 100,
        ];
    }

    /**
     * Format TZ Date Time.
     *
     * @param mixed $currentDateTime
     * @return string
     */
    public function formatTZDateTime(mixed $currentDateTime = '')
    {
        return date('Y-m-d\TH:i:s', strtotime((string)$currentDateTime));
    }

    /**
     * Add Application Transaction Data.
     *
     * @return array
     */
    public function addApplicationTransactionData(array $applicationDataParams = [])
    {
        $applicationDataResponse = [
            'error' => true,
            'status' => false,
            'response' => [],
            'message' => __('Payment transaction error.'),
        ];
        $storeId = $this->getStoreId();

        try {
            $addTransactionApplicationDataParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'applicationTransactionRequest' => [
                    'CustomerInternalId' => $applicationDataParams['CustomerInternalId'] ?? '',
                    'TransactionId' => $applicationDataParams['TransactionId'] ?? '',
                    'TransactionTypeId' => $applicationDataParams['TransactionTypeId'] ?? '',
                    'LinkedToInternalId' => $applicationDataParams['LinkedToInternalId'] ?? '',
                    'SoftwareId' => $this->getSoftwareId(),
                    'TransactionDate' => $this->getCurrentDateTime('Y-m-d H:i:s'),
                    'TransactionNotes' => $applicationDataParams['TransactionNotes'] ?? '',
                    'LinkedToTypeId' => $applicationDataParams['LinkedToTypeId'] ?? '',
                    'LinkedToExternalUniqueId' => $applicationDataParams['LinkedToExternalUniqueId'] ?? '',
                    'TransactionCustomFields' => $applicationDataParams['TransactionCustomFields'] ?? [],
                ],
            ];

            $appTransactionResponse = $this->getClient($storeId)
                ->AddApplicationTransaction($addTransactionApplicationDataParams);
            $transactionResults = (array)$appTransactionResponse->AddApplicationTransactionResult;

            if (isset($transactionResults['StatusCode']) && 1 === (int)$transactionResults['StatusCode']) {
                $applicationDataResponse['status'] = true;
                $applicationDataResponse['error'] = false;
                $applicationDataResponse['response'] = (array)$transactionResults;
                $applicationDataResponse['message'] = __(
                    'Success, add application transaction data response added. '
                );
            } else {
                $applicationDataResponse['message'] = __(
                    'Error occurred during adding Payment transaction data.'
                );
            }
        } catch (SoapFault $soapFault) {
            $this->ebizchargeLogger->addCritical(__('Application payment transaction Data Error:'
                . $soapFault->getMessage()));
            $applicationDataResponse['message'] = __('Error occurred Exception:' . $soapFault->getMessage());
        }

        return $applicationDataResponse;
    }

    /**
     * Get Current Date Time.
     *
     * @param string $format
     * @return string
     * @throws Exception
     */
    public function getCurrentDateTime(mixed $format = 'Y-m-d H:i:s')
    {
        return $this->prepareDateTime('', $format);
    }

    /**
     * Prepare Current Date Time.
     *
     * @param mixed $dateTimeParam
     * @param mixed $format
     * @return string
     * @throws Exception
     */
    public function prepareDateTime(mixed $dateTimeParam = "", mixed $format = 'Y-m-d H:i:s'): string
    {
        $timeZone = $this->timezoneInterface->getDefaultTimezone();
        $dateTime = new DateTime($timeZone);

        if (!$dateTimeParam) {
            $dateTimeParam = date('Y-m-d H:i:s');
        }
        $dateTimeParam = date('Y-m-d H:i:s', strtotime($dateTimeParam));

        return $dateTime->createFromFormat('Y-m-d H:i:s', $dateTimeParam)->format($format);
    }

    /**
     * Format Date Time.
     *
     * @param mixed $dateTimeParam
     * @param mixed $format
     * @return string
     * @throws Exception
     */
    public function formateDateTime(mixed $dateTimeParam = '', mixed $format = 'Y-m-d H:i:s'): string
    {
        return $this->prepareDateTime($dateTimeParam, $format);
    }

    /**
     * Add Recurring Item At Gateway
     *
     * @param array $orderRecurringParams
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addRecurringItemAtGateway(array $orderRecurringParams = [])
    {
        $recurring = null;

        /**  create a $recurring */
        $recurring = $this->recurringFactory->create()->addRecurringOrderItems($orderRecurringParams);
        $this->ebizchargeLogger->addInfo(__('Success, the recurring order has been added '));

        return $recurring;
    }

    /**
     * Get Client IP.
     *
     * @return bool|string
     */
    public function getClientIp()
    {
        return $this->getRemoteAddress();
    }

    /**
     * Refund previous transaction.
     *
     * @return bool
     *
     * @throws LocalizedException
     */
    public function refundTransaction()
    {
        try {
            $storeId = $this->getStoreId();
            $transaction = $this->getClient($storeId)->runTransaction(
                [
                    'securityToken' => $this->getUeSecurityToken($storeId),
                    'tran' => $this->getTransactionRequest(),
                ]
            );

            $transaction = $transaction->runTransactionResult;

            $this->result = $transaction->Result;
            $this->resultcode = $transaction->ResultCode;
            $this->authcode = $transaction->AuthCode;

            // Caused refund issue.
            $this->refnum = $transaction->RefNum;
            $this->batch = $transaction->BatchNum;
            $this->avs_result = $transaction->AvsResult;
            $this->avs_result_code = $transaction->AvsResultCode;
            $this->cvv2_result = $transaction->CardCodeResult;
            $this->cvv2_result_code = $transaction->CardCodeResultCode;
            $this->vpas_result_code = $transaction->VpasResultCode;
            $this->convertedamount = $transaction->ConvertedAmount;
            $this->convertedamountcurrency = $transaction->ConvertedAmountCurrency;
            $this->conversionrate = $transaction->ConversionRate;
            $this->error = $transaction->Error;
            $this->errorcode = $transaction->ErrorCode;
            $this->custnum = $transaction->CustNum;

            // Obsolete variable (for backward compatibility)
            // At some point they will no longer be set.
            $this->avs = $transaction->AvsResult;
            $this->cvv2 = $transaction->CardCodeResult;

            $this->cctransid = $transaction->RefNum;
            $this->acsurl = $transaction->AcsUrl;
            $this->pareq = $transaction->Payload;

            if ('A' == $this->resultcode) {
                return true;
            }

            return false;
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__(
                'Exception occurred during preparing transaction for refund SoapFault: ' . $ex->getMessage()
            ));

            throw new LocalizedException(__(
                'Exception occurred during preparing transaction for refund SoapFault:: ' . $ex->getMessage()
            ));
        }
    }

    /**
     * Run Update Query
     *
     * @param array $queryParameters
     * @return int
     */
    public function runUpdateQuery(array $queryParameters = [])
    {
        $resource = $this->resourceConnection;
        $tableNamef = $resource->getTableName($queryParameters['tableName']);
        return $resource->getConnection()->update($tableNamef, $queryParameters['data'], $queryParameters['where']);
    }

    /**
     * Get magento customer details.
     *
     * @param mixed $customerId
     * @return null|array|mixed
     */
    public function getMagentoCustomer(mixed $customerId = "")
    {
        try {
            $customerData = $this->customerRegistry->retrieve($customerId);
            return $customerData->getData();
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(__('Exception occurred ' . $e->getMessage()));
        }

        return null;
    }

    /**
     * Modify Recurring Payment Method.
     *
     * @param mixed $methodId
     * @param mixed $schedulePaymentInternalId
     * @return bool
     */
    public function modifyRecurringPaymentMethod(mixed $methodId = "", mixed $schedulePaymentInternalId = "")
    {
        $storeId = $this->getStoreId();
        $paymentMethodProfile = [
            'securityToken' => $this->getUeSecurityToken($storeId),
            'scheduledPaymentInternalId' => trim($schedulePaymentInternalId),
            'paymentMethodProfileId' => $methodId,
        ];
        $storeId = $this->getStoreId();

        $paymentMethodProfileResponse = $this->getClient($storeId)
            ->ModifyScheduledRecurringPayment_PaymentMethodProfile($paymentMethodProfile);
        $paymentMethodProfileResult
            = $paymentMethodProfileResponse->ModifyScheduledRecurringPayment_PaymentMethodProfileResult;
        if (isset($paymentMethodProfileResult)) {
            return $paymentMethodProfileResult->StatusCode;
        }

        return false;
    }

    /**
     * Get Search Scheduled Recurring Payments.
     *
     * @param mixed $customerInternalId
     * @param mixed $schedulePaymentId
     * @return null|mixed
     */
    public function getSearchScheduledRecurringPayments(
        mixed $customerInternalId = "",
        mixed $schedulePaymentId = ""
    )
    {
        try {
            $storeId = $this->getStoreId();
            $soapClient = $this->getClient($storeId);

            if ($soapClient) {
                $response = $this->getClient($storeId)->SearchScheduledRecurringPayments(
                    [
                        'securityToken' => $this->getUeSecurityToken($storeId),
                        'customerInternalId' => $customerInternalId,
                        'start' => self::EBIZCHARGE_DEFAULT_REQUEST_START_LIMIT,
                        'limit' => self::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT,
                    ]
                );

                if (!isset($response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails)) {
                    $recurringDetail = [];
                } elseif (
                    is_array($response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails)
                    && count($response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails)
                    > 1
                ) {
                    $recurringDetail = $response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails;
                } else {
                    $recurringDetail[] = $response->SearchScheduledRecurringPaymentsResult->RecurringBillingDetails;
                }

                if (!empty($recurringDetail)) {
                    $key = array_search(
                        $schedulePaymentId,
                        array_column($recurringDetail, 'ScheduledPaymentInternalId')
                    );

                    return $paymentMethods = $recurringDetail[$key] ?? null;
                }
            }

            return null;
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__('Exception occurred ' . __METHOD__ . $ex->getMessage()));

            return null;
        }
    }

    /**
     * Get Search Transactions
     *
     * @param mixed $customerId
     * @param mixed $start
     * @param mixed $limit
     * @param mixed $createdDate
     * @param bool $countOnly
     * @return array|int
     */
    public function getSearchTransactions(
        mixed $customerId = "",
        mixed $start = "0",
        mixed $limit = self::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT,
        mixed $createdDate = "",
        bool  $countOnly = false
    )
    {
        // $customerId = 5026;
        $date = $createdDate ? date('Y-m-d', strtotime($createdDate)) : '2021-01-15';
        $storeId = $this->getStoreId();

        if ($countOnly) {
            $start = self::EBIZCHARGE_DEFAULT_REQUEST_START_LIMIT;
            $limit = self::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT;
        }

        try {
            $filterClerk = [
                'FieldName' => 'Clerk',
                'ComparisonOperator' => 'eq',
                'FieldValue' => 'Recurring',
            ];
            $filterStart = [
                'FieldName' => 'created',
                'ComparisonOperator' => 'gt',
                'FieldValue' => $date,
            ];
            $searchFilters['SearchFilter'][0] = $filterClerk;
            $searchFilters['SearchFilter'][1] = $filterStart;

            if (!empty($customerId)) {
                $filterCustomer = [
                    'FieldName' => 'CustID',
                    'ComparisonOperator' => 'eq',
                    'FieldValue' => $customerId,
                ];

                $searchFilters['SearchFilter'][2] = $filterCustomer;
            }

            $searchTransactionsReq = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'filters' => $searchFilters,
                'matchAll' => 1,
                'countOnly' => $countOnly,
                'start' => $start,
                'limit' => $limit,
                'sort' => 'DateTime DESC',
            ];

            // check if Get client is null
            if ($this->getClient($storeId)) {
                $response = $this->getClient($storeId)->SearchTransactions($searchTransactionsReq);

                if ($countOnly && isset($response->SearchTransactionsResult->TransactionsMatched)) {
                    return (int)$response->SearchTransactionsResult->TransactionsMatched;
                }

                if (!isset($response->SearchTransactionsResult->Transactions->TransactionObject)) {
                    $recurringDetail = [];
                } elseif (
                    is_array($response->SearchTransactionsResult->Transactions->TransactionObject)
                    && count($response->SearchTransactionsResult->Transactions->TransactionObject)
                    > 1
                ) {
                    $recurringDetail[] = $response->SearchTransactionsResult->Transactions->TransactionObject;
                } else {
                    $recurringDetail[] = $response->SearchTransactionsResult->Transactions->TransactionObject;
                }

                return $recurringDetail;
            }

            return [];
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__('Exception occurred during getting Search Listings '
                . __METHOD__ . $ex->getMessage()));

            return [];
        }
    }

    /**
     * Get Receipt Ref Number.
     *
     * @return string
     */
    public function getReceiptRefNumber()
    {
        $receiptRefNum = '';

        try {
            $storeId = $this->getStoreId();
            $receiptsList = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'receiptType' => 'email',
            ];

            $receiptsList = $this->getClient($storeId)->GetReceiptsList($receiptsList);
            $getReceiptsListResult = $receiptsList->GetReceiptsListResult;
            $needle = 'Transaction API and Payment Form (Customer)';

            if (isset($getReceiptsListResult)) {
                foreach ($getReceiptsListResult as $array) {
                    foreach ($array as $item) {
                        if ($item->Name == $needle) {
                            $receiptRefNum = $item->ReceiptRefNum;

                            break;
                        }
                    }
                }
            }

            return $receiptRefNum;
        } catch (Exception $exception) {
            $this->ebizchargeLogger->addCritical(__('Error occurred during fetching Receipt Number.'));

            return $receiptRefNum;
        }
    }

    /**
     * Suspend Scheduled Recurring Payment Status.
     *
     * @param mixed $recurringPaymentInterId
     * @param mixed $status
     *
     * @return array
     */
    public function suspendScheduledRecurringPaymentStatus(
        mixed $recurringPaymentInterId = "",
        mixed $status = ""
    )
    {
        $scheduledPaymentStatusResp = [
            'error' => true,
            'status' => __('Error'),
            'message' => __(),
        ];

        try {
            $storeId = $this->getStoreId();
            $scheduledPaymentInternalId = '' !== $recurringPaymentInterId ? $recurringPaymentInterId : '';
            $status = (self::RECURRING_PAYMENT_STATUS_SUSPENDED == $status) ? $status
                : self::RECURRING_PAYMENT_STATUS_UNSUSPENDED;

            $scheduledPaymentParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'scheduledPaymentInternalId' => $scheduledPaymentInternalId,
                'statusId' => $status,
            ];

            $scheduledRecurringPaymentStatus = $this->getClient($storeId)
                ->ModifyScheduledRecurringPaymentStatus($scheduledPaymentParams);

            $scheduledRecurringStatusResult
                = $scheduledRecurringPaymentStatus->ModifyScheduledRecurringPaymentStatusResult;

            if (!empty($scheduledRecurringStatusResult) && 1 == $scheduledRecurringStatusResult->StatusCode) {
                $scheduledPaymentStatusResp['error'] = false;
                $scheduledPaymentStatusResp['status'] = $scheduledRecurringStatusResult->Status;
                $scheduledPaymentStatusResp['message'] = __(
                    'Success, the recurring payment has been suspended at EBizCharge Gateway'
                );
            } else {
                $scheduledPaymentStatusResp['message'] = __(
                    'Error, occurred during changing the status of recurring payment at EBizCharge Gateway'
                );
            }

            $this->ebizchargeLogger->addInfo($scheduledPaymentInternalId . ': '
                . $scheduledPaymentStatusResp['message']);

            return $scheduledPaymentStatusResp;
        } catch (Exception $ex) {
            $errorMessage = __('Exception occurred during suspending the Recurring Payment Status Error:'
                . $ex->getMessage());

            $scheduledPaymentStatusResp['message'] = $errorMessage;
            $this->ebizchargeLogger->addCritical($errorMessage);

            return $scheduledPaymentStatusResp;
        }
    }

    /**
     * Search Recurring Payment.
     *
     * @param mixed $customerId
     * @param mixed $scheduledPaymentInternalId
     * @param mixed $orderDate
     *
     * @return null|string
     */
    public function searchRecurringPayment(
        mixed $customerId,
        mixed $scheduledPaymentInternalId,
        mixed $orderDate
    )
    {
        try {
            $storeId = $this->getStoreId();
            // Get full schedule
            $parametersSearch = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'scheduledPaymentInternalId' => $scheduledPaymentInternalId,
                'customerId' => $customerId,
                'fromDateTime' => '2020-11-01',
                'toDateTime' => date('Y-m-d'),
                'start' => self::EBIZCHARGE_DEFAULT_REQUEST_START_LIMIT,
                'limit' => self::EBIZCHARGE_DEFAULT_REQUEST_MAX_LIMIT,
            ];
            $searchRecurringPayments = $this->getClient($storeId)->SearchRecurringPayments($parametersSearch);

            $recurringPaymentsResult = $searchRecurringPayments->SearchRecurringPaymentsResult;
            if (!empty($recurringPaymentsResult)) {
                if (isset($recurringPaymentsResult->Payment)) {
                    $payments = $recurringPaymentsResult->Payment;

                    if (is_object($payments)) {
                        $paymentData[] = $payments;
                    } else {
                        $paymentData = $payments;
                    }

                    foreach ($paymentData as $payment) {
                        if ($orderDate == date('Y-m-d', strtotime($payment->DatePaid))) {
                            return $payment->PaymentInternalId;
                        }
                    }
                } else {
                    $this->ebizchargeLogger->addInfo(__(
                        'No payment found against scheduledPaymentInternalId = ' . $scheduledPaymentInternalId
                    ));
                }
            } else {
                $this->ebizchargeLogger->addInfo(__(
                    'No payment found against scheduledPaymentInternalId = ' . $scheduledPaymentInternalId
                ));
            }

            return null;
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addCritical(__(
                'Exception occurred searching recurring payment . Method: ' . __METHOD__ . $ex->getMessage()
            ));
        }

        return null;
    }

    /**
     * Mark Recurring Payment As Applied.
     *
     * @param mixed $paymentInternalId
     */
    public function markRecurringPaymentAsApplied(mixed $paymentInternalId = "")
    {
        try {
            $storeId = $this->getStoreId();
            // Get full schedule
            $parametersPayment = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'paymentInternalId' => $paymentInternalId,
            ];

            $markRecurringPaymentAsApplied = $this->getClient($storeId)->MarkRecurringPaymentAsApplied($parametersPayment);
            $markRecurringPaymentAsAppliedResult = $markRecurringPaymentAsApplied->MarkRecurringPaymentAsAppliedResult;

            if (!empty($markRecurringPaymentAsAppliedResult)) {
                if (1 == $markRecurringPaymentAsAppliedResult->StatusCode) {
                    $this->ebizchargeLogger->addInfo(__(
                        'Payment is marked as applied against PaymentInternalId = ' . $paymentInternalId
                    ));
                } else {
                    $this->ebizchargeLogger->addInfo(__(
                        'Payment is not marked as applied against PaymentInternalId = ' . $paymentInternalId
                    ));
                }
            } else {
                $this->ebizchargeLogger->addInfo(__(
                    'Payment is not marked as applied against PaymentInternalId = ' . $paymentInternalId
                ));
            }
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addError(__(
                'Exception occurred, There is an error in mark as applied process. ' . $ex->getMessage()
            ));
        }
    }

    /**
     * This method sets authorization data for gateway request
     *
     * @param array $newAuthData
     * @return void
     */
    public function setAuthorizeData(array $newAuthData = [])
    {
        $this->authcode = $newAuthData['AuthCode'] ?? null;
        $this->refnum = $newAuthData['RefNum'] ?? null;
        $this->avs_result_code = $newAuthData['AvsResultCode'] ?? null;
        $this->cvv2_result_code = $newAuthData['CardCodeResultCode'] ?? null;
        $this->resultcode = $newAuthData['ResultCode'] ?? null;
        $this->result = $newAuthData['Result'] ?? null;
        $this->command = $newAuthData['TransactionType'] ?? null;
    }

    /**
     * Get Transaction Authorize Data
     *
     * @return array
     */
    public function getAuthorizeData(): array
    {
        // Transaction Auth Data
        return [
            'batch_num' => $this->batch_num,
            'avs_result' => $this->avs_result,
            'is_duplicate' => $this->isDuplicate ?? '',
            'cvv2_result' => $this->cvv2_result,
            'vpas_result_code' => $this->vpas_result_code,
            'convertedamount' => $this->convertedamount,
            'convertedamountcurrency' => $this->convertedamountcurrency,
            'conversionrate' => $this->conversionrate,
            'error' => $this->error,
            'errorcode' => $this->errorcode,
            'custnum' => $this->custnum,
            'authcode' => $this->authcode,
            'refnum' => $this->refnum,
            'avs_result_code' => $this->avs_result_code,
            'cvv2_result_code' => $this->cvv2_result_code,
            'resultcode' => $this->resultcode,
            'result' => $this->result,
            'command' => $this->command,
            'avs' => $this->avs,
            'cvv2' => $this->cvv2,
            'acsurl' => $this->acsurl,
            'pareq' => $this->pareq,
            'card_level_result_code' => $this->card_level_result_code,
            'card_level_result' => $this->card_level_result,
            'card_code_result' => $this->card_code_result,
            'card_code_result_code' => $this->card_code_result_code,
            'batch_ref_num' => $this->batch_ref_num,
            'auth_code' => $this->auth_code,
            'auth_amount' => $this->auth_amount,
            'payment_status' => $this->payment_status,
            'payment_status_code' => $this->payment_status_code,
        ];
    }

    /**
     * Set Payment Data
     *
     * @param InfoInterface $payment
     * @param mixed $amount
     * @return void
     */
    public function setPaymentData(InfoInterface $payment, mixed $amount = "0")
    {
        /**
         * Order of this Payment.
         */
        $order = $payment->getOrder();
        $amount = $order->getTotalDue();

        $this->cardholder = $payment->getCcOwner();
        $this->card = $payment->getCcNumber();
        $this->cardtype = $payment->getCcType();
        $expiryYear = $payment->getCcExpYear() ?: '';
        $this->exp = $payment->getCcExpMonth() . substr((string)$expiryYear, 2, 2);
        $this->cvv2 = $payment->getCcCid();
        $this->amount = (string)(str_replace(',', '', (string)$amount) ?? '0');
        $this->discount = abs((float)$order->getDiscountAmount());

        $this->achtype = $payment->getAdditionalInformation('ach_type');
        $this->achroute = $payment->getAdditionalInformation('ach_route');
    }

    /**
     * Set order data
     *
     * @param InfoInterface $payment
     * @return void
     */
    public function setOrderData(InfoInterface $payment)
    {
        $order = $payment->getOrder();
        $orderId = $order->getIncrementId();
        $this->invoice = $orderId;
        $this->orderid = $orderId;
        $this->ponum = $orderId;
        $this->ip = $order->getRemoteIp();
        $this->custid = $order->getCustomerId();
        $this->email = $order->getCustomerEmail();
        $this->tax = $order->getTaxAmount();
        $this->shipping = $order->getShippingAmount();

        // avs data
        if ($billingAddress = $order->getBillingAddress()) {
            $this->street = $billingAddress->getStreet();
            $this->zip = $billingAddress->getPostcode();
        }

        $this->description = 'Magento Order #' . $orderId;
        if ($description = $this->configFactory->create()->getPaymentDescription()) {
            $this->description = str_replace('[orderid]', $orderId, $description);
        }

        // Set Recurring Values
        $this->recurringMethodId = $payment->getAdditionalInformation('ebzc_method_id');
    }

    /**
     * Get order general data
     *
     * @return array
     */
    public function getOrderData(): array
    {
        return [
            'orderId' => $this->orderid,
            'invoiceId' => $this->invoice,
            'customerId' => $this->custid,
            'customerEmail' => $this->email,
            'recurringMethodId' => $this->recurringMethodId,
            'ip' => $this->ip,
        ];
    }

    /**
     * Set Guest Customer.
     */
    public function setGuestCustomer()
    {
        $this->custid = 'Guest';
    }

    /**
     * Set command for payment gateway
     *
     * @param mixed $command
     * @return void
     */
    public function setCommand(mixed $command = "")
    {
        if (!empty($command)) {
            $this->command = $command;
        }
    }

    /**
     * Get payment error.
     */
    public function getPaymentError(): array
    {
        return [
            'error' => $this->error,
            'errorcode' => $this->errorcode,
        ];
    }

    /**
     * Get current ach status.
     */
    public function getAchStatus(): bool
    {
        return $this->achStatus;
    }

    /**
     * Set ach status.
     */
    public function setAchStatus(bool $achStatus)
    {
        $this->achStatus = $achStatus;
    }

    /**
     * Validate Merchant APIC Credentials
     *
     * @param array $merchantParams
     * @return bool
     * @throws NoSuchEntityException
     */
    public function validateMerchantAPICredentials(array $merchantParams): bool
    {
        $isValid = false;
        try {
            $merchantCredentials = [
                'isActive' => isset($merchantParams['is_active']) ? $merchantParams['is_active'] : '',
                'SecurityId' => isset($merchantParams['merchant_key']) ? $merchantParams['merchant_key'] : '',
                'UserId' => isset($merchantParams['merchant_id']) ? $merchantParams['merchant_id'] : '',
                'Password' => isset($merchantParams['merchant_pin']) ? $merchantParams['merchant_pin'] : '',
            ];
            // logging the request for validating
            $this->ebizchargeLogger->addInfo(__('Fetching the merchant with provided credentials'));
            $soapClient = $this->getClient("enabled", $merchantCredentials);
            if ($soapClient) {
                $soapClient = $this->getClient("enabled", $merchantCredentials);
                $soapParams = ['securityToken' => $merchantCredentials];
                $merchantDataResults = $soapClient->GetMerchantTransactionData($soapParams);
                // check if merchant key is valid
                if (isset($merchantDataResults->GetMerchantTransactionDataResult)) {
                    $isValid = true;
                }
            }
        } catch (SoapFault $soapFault) {
            $this->ebizchargeLogger->addCritical(__('Exception occurred during validating API Keys: '
                . $soapFault->getMessage()));
        }
        return $isValid;
    }

    /**
     * Update customer payment method profile.
     *
     * @param mixed $customerToken
     * @param mixed $paymentMethodObject
     *
     * @return bool
     */
    public function updatePaymentMethod(
        mixed $customerToken,
        mixed $paymentMethodObject
    )
    {
        try {
            $storeId = $this->getStoreId();
            $params = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'customerToken' => $customerToken,
                'paymentMethodProfile' => $paymentMethodObject,
            ];

            $updatedMethodProfile = $this->getClient($storeId)->updateCustomerPaymentMethodProfile($params);

            if (isset($updatedMethodProfile->UpdateCustomerPaymentMethodProfileResult)) {
                return true;
            }
        } catch (Exception $ex) {
            $this->log($ex->getMessage());
        }

        return false;
    }

    /**
     * Logger for ebiz gateway.
     *
     * @param mixed $message
     * @param null|mixed $level
     *
     * @return mixed
     */
    public function log(mixed $message, mixed $level = null)
    {
        return $this->ebizchargeLogger->addInfo(__($message));
    }

    /**
     * Saved Process run.
     *
     * @param mixed $ebzcCustomerId
     * @param mixed $ebzcMethodId
     * @param InfoInterface $payment
     * @return bool
     * @throws LocalizedException
     */
    public function captureOnlineProcess(mixed $ebzcCustomerId, mixed $ebzcMethodId, InfoInterface $payment)
    {
        $this->ebizchargeLogger->addInfo(__('Adding Transaction using ... ' . __METHOD__));

        try {
            $storeId = $this->getStoreId();
            $transactionParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'custNum' => $ebzcCustomerId,
                'paymentMethodID' => $ebzcMethodId,
                'tran' => $this->getCustomerTransactionRequest(),
            ];

            $transactionResult = $this->getClient($storeId)->runCustomerTransaction($transactionParams);
            $transaction = $transactionResult->runCustomerTransactionResult;

            if (isset($transaction)) {
                $this->setTransactionResult($transaction);
                // if ($transactionApproved && $this->configFactory->create()->isRecurringEnabled() == 1) {
                // $this->runRecurring($payment);
                // }
            }
        } catch (Exception $ex) {
            $this->ebizchargeLogger->addCritical(__('savedProcess call and error occurred: '
                . $ex->getMessage()));

            // phpcs:ignore
            throw new LocalizedException(__(self::EBIZCHARGE_TRANSACTION_ERROR . '-' . $ex->getMessage()));
        }

        return false;
    }

    /**
     *  Calculate Surcharge Amount
     *
     * @param array $requestParams
     * @return array
     * @throws NoSuchEntityException
     */
    public function calculateSurchargeAmount(array $requestParams = []): array
    {
        $storeId = $this->storeId ?: $this->getStoreId();
        $surchargeSettings = $this->getSurchargeSettings($storeId);
        $isSurchargeEnabled = $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_ENABLED] ?? false;
        $surchargeTypeId = $surchargeSettings[SurchargeInterface::EBIZ_SURCHARGE_TYPE_ID] ?? false;

        /** Calculate Surcharge Amount params */
        $cartAmount = $requestParams['amount'] ?? 0;
        $cardNumber = $requestParams['cardNumber'] ?? null;
        $zipCode = $requestParams['cardZipCode'] ?? '';
        $customerInternalId = $requestParams['customerInternalId'] ?? null;
        $paymentMethodId = $requestParams['paymentMethodId'] ?? null;

        /** Response params */
        $response = [
            SurchargeInterface::EBIZ_SURCHARGE_ENABLED => false,
            SurchargeInterface::EBIZ_SURCHARGE_FOR_ZIP => false,
            SurchargeInterface::EBIZ_SURCHARGE_FOR_PAYMENT_METHOD => false,
            SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE => 0,
            SurchargeInterface::EBIZ_SURCHARGE_AMOUNT => 0,
            SurchargeInterface::EBIZ_SURCHARGE_CAPTION => '',
            SurchargeInterface::EBIZ_SURCHARGE_TERMS_NOTE => '',
            SurchargeInterface::EBIZ_SURCHARGE_INELIGIBLE => false,
        ];

        if (
            $isSurchargeEnabled && $cartAmount
            && ($cardNumber || $paymentMethodId)
            && SurchargeInterface::EBIZ_SURCHARGE_TYPE_ID_DAILY_DISCOUNT === $surchargeTypeId
        ) {

            /** API Request Params */
            $requestParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'amount' => $cartAmount,
                'cardNumber' => $cardNumber,
                'cardZipCode' => $zipCode,
                'customerInternalId' => $customerInternalId,
                'paymentMethodId' => $paymentMethodId,
            ];

            $soapClient = $this->getClient($storeId);

            /** Soap API result */
            $result = $soapClient ? $soapClient->CalculateSurchargeAmount($requestParams) : [];

            if (
                isset($result->CalculateSurchargeAmountResult)
                && is_object($result->CalculateSurchargeAmountResult)
            ) {
                $surchargeResult = (array)$result->CalculateSurchargeAmountResult;

                $response[SurchargeInterface::EBIZ_SURCHARGE_ENABLED] = $surchargeResult['IsSurchargeEnabled'] ?? false;
                $response[SurchargeInterface::EBIZ_SURCHARGE_FOR_ZIP]
                    = $surchargeResult['IsSurchargeAllowedForZipCode'] ?? false;
                $response[SurchargeInterface::EBIZ_SURCHARGE_FOR_PAYMENT_METHOD]
                    = $surchargeResult['IsSurchargeAllowedForPaymentMethod'] ?? false;
                $response[SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE] = $surchargeResult['SurchargePercentage'] ?? 0;
                $response[SurchargeInterface::EBIZ_SURCHARGE_AMOUNT] = $surchargeResult['SurchargeAmount'] ?? 0;
                $response[SurchargeInterface::EBIZ_SURCHARGE_CAPTION] = $surchargeResult['SurchargeCaption'] ?? '';
                $response[SurchargeInterface::EBIZ_SURCHARGE_TERMS_NOTE] = $surchargeResult['SurchargeTermsNote'] ?? '';
                $response[SurchargeInterface::EBIZ_SURCHARGE_INELIGIBLE]
                    = !($response[SurchargeInterface::EBIZ_SURCHARGE_FOR_ZIP]
                    && $response[SurchargeInterface::EBIZ_SURCHARGE_FOR_PAYMENT_METHOD]);
            }
        }

        // for testing
        // $response[SurchargeInterface::EBIZ_SURCHARGE_INELIGIBLE] = true;
        return $response;
    }

    /**
     * Get Merchant's Surcharge Settings
     *
     * @param mixed $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSurchargeSettings(mixed $storeId = "0")
    {
        $storeId = $storeId ?? $this->getStoreId();
        /** Response params */
        $response = [
            SurchargeInterface::EBIZ_SURCHARGE_ENABLED => false,
            SurchargeInterface::EBIZ_SURCHARGE_COUNTRY_ID => '',
            SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE => 0,
            SurchargeInterface::EBIZ_SURCHARGE_TERMS_NOTE => '',
            SurchargeInterface::EBIZ_SURCHARGE_CAPTION => '',
            SurchargeInterface::EBIZ_SURCHARGE_TYPE_ID => '',
        ];

        try {
            /** Security Token params */
            $merchantParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
            ];
            if ($this->getClient($storeId)) {
                /** Soap API result */
                $result = $this->getClient($storeId)->GetSurchargeSettings($merchantParams);

                if (is_object($result->GetSurchargeSettingsResult)) {
                    $surchargeSettings = (array)$result->GetSurchargeSettingsResult;

                    $response = [
                        SurchargeInterface::EBIZ_SURCHARGE_ENABLED => $surchargeSettings['IsSurchargeEnabled'] ?? false,
                        SurchargeInterface::EBIZ_SURCHARGE_COUNTRY_ID => $surchargeSettings['SurchargeCountryId'] ?? '',
                        SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE => $surchargeSettings['SurchargePercentage'] ?? 0,
                        SurchargeInterface::EBIZ_SURCHARGE_TERMS_NOTE => $surchargeSettings['SurchargeTermsNote'] ?? '',
                        SurchargeInterface::EBIZ_SURCHARGE_CAPTION => $surchargeSettings['SurchargeCaption'] ?? '',
                        SurchargeInterface::EBIZ_SURCHARGE_TYPE_ID => $surchargeSettings['SurchargeTypeId'] ?? '',
                    ];
                }
            }
        } catch (SoapFault $soapFault) {
            $this->ebizchargeLogger->addCritical(
                __('Error occurred during fetching surcharge settings. Error: '
                    . $soapFault->getMessage())
            );
        }

        return $response;
    }

    /**
     * @param mixed $storeId
     *
     * @param mixed $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getMerchantSettings(mixed $storeId = "0")
    {
        $storeId = $storeId ?? $this->getStoreId();
        /** Response params */
        $merchantSettingsResponse = [
            SurchargeInterface::EBIZ_SURCHARGE_ENABLED => false,
            SurchargeInterface::EBIZ_SURCHARGE_COUNTRY_ID => '',
            SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE => 0,
            SurchargeInterface::EBIZ_SURCHARGE_TERMS_NOTE => '',
            SurchargeInterface::EBIZ_SURCHARGE_CAPTION => '',
            SurchargeInterface::EBIZ_SURCHARGE_TYPE_ID => '',
        ];

        try {
            /** Security Token params */
            $merchantParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
            ];

            if ($this->getClient($storeId)) {
                /** Soap API result */
                $merchantSettingsResult = $this->getClient($storeId)->GetMerchantIntegrationSettings($merchantParams);

                if (is_object($merchantSettingsResult->GetMerchantIntegrationSettingsResult)) {
                    $merchantSettings = (array)$merchantSettingsResult->GetMerchantIntegrationSettingsResult;

                    $merchantSettingsResponse = [
                        SurchargeInterface::EBIZ_SURCHARGE_ENABLED => $merchantSettings['IsSurchargeEnabled'] ?? false,
                        SurchargeInterface::EBIZ_SURCHARGE_COUNTRY_ID => $merchantSettings['SurchargeCountryId'] ?? '',
                        SurchargeInterface::EBIZ_SURCHARGE_PERCENTAGE => $merchantSettings['SurchargePercentage'] ?? 0,
                        SurchargeInterface::EBIZ_SURCHARGE_TERMS_NOTE => $merchantSettings['SurchargeTermsNote'] ?? '',
                        SurchargeInterface::EBIZ_SURCHARGE_CAPTION => $merchantSettings['SurchargeCaption'] ?? '',
                        SurchargeInterface::EBIZ_SURCHARGE_TYPE_ID => $merchantSettings['SurchargeTypeId'] ?? '',
                    ];
                }
            }
        } catch (SoapFault $soapFault) {
            $this->ebizchargeLogger->addCritical(
                __('Error occurred during fetching surcharge settings. Error: '
                    . $soapFault->getMessage())
            );
        }

        return $merchantSettingsResponse;
    }

    /**
     * Get 3D Secure Settings
     *
     * @param $storeId
     * @return array|false[]
     * @throws NoSuchEntityException
     */
    public function get3DSecureSettings($storeId = "0")
    {
        /** Response params */
        $response = [
            SoapApiModelInterface::EBIZ_IS_3DSECURE_ENABLED => false,
            SoapApiModelInterface::EBIZ_IS_3DSECURE_TEST_MODE => false,
            SoapApiModelInterface::EBIZ_3DSECURE_BYPASS_ON_ERROR => false,
        ];

        try {
            // $this->setWsdlUrl('3dSecure');

            /** Security Token params */
            $merchantParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
            ];

            if ($soapClient = $this->getClient('3dSecure')) {
                /** Soap API result */
                $result = $soapClient->Get3DS2Settings($merchantParams);

                if (isset($result->Get3DS2SettingsResult) && is_object($result->Get3DS2SettingsResult)) {
                    $settings = (array)$result->Get3DS2SettingsResult;

                    /** Set Response Params */
                    $response = [
                        SoapApiModelInterface::EBIZ_IS_3DSECURE_ENABLED => $settings['Is3DS2Enabled'] ?? false,
                        SoapApiModelInterface::EBIZ_IS_3DSECURE_TEST_MODE => $settings['Is3DS2TestMode'] ?? false,
                        SoapApiModelInterface::EBIZ_3DSECURE_BYPASS_ON_ERROR => $settings['Bypass3DS2OnError'] ?? false,
                    ];
                }
            }
        } catch (SoapFault $soapFault) {
            $this->ebizchargeLogger->addCritical(
                __('Error occurred during fetching 3D Secure settings. Error: ')
                . $soapFault->getMessage()
            );
        }

        return $response;
    }

    /**
     * Line items for order.
     *
     * @return array
     */
    public function getLineItems()
    {
        $lineItems = [];

        $cart = $this->cart;

        $this->billingAddress = [
            'FirstName' => $cart->getQuote()->getBillingAddress()->getFirstname(),
            'LastName' => $cart->getQuote()->getBillingAddress()->getLastname(),
            'Company' => $cart->getQuote()->getBillingAddress()->getCompany(),
            'Street' => $cart->getQuote()->getBillingAddress()->getStreetFull(),
            'Street2' => '',
            'City' => $cart->getQuote()->getBillingAddress()->getCity(),
            'State' => $cart->getQuote()->getBillingAddress()->getRegion(),
            'Zip' => $cart->getQuote()->getBillingAddress()->getPostcode(),
            'Country' => $cart->getQuote()->getBillingAddress()->getCountry(),
            'Fax' => $cart->getQuote()->getBillingAddress()->getFax(),
            'Email' => $cart->getQuote()->getBillingAddress()->getEmail(),
        ];

        $this->shippingAddress = [
            'FirstName' => $cart->getQuote()->getShippingAddress()->getFirstname(),
            'LastName' => $cart->getQuote()->getShippingAddress()->getLastname(),
            'Company' => $cart->getQuote()->getShippingAddress()->getCompany(),
            'Street' => $cart->getQuote()->getShippingAddress()->getStreetFull(),
            'Street2' => '',
            'City' => $cart->getQuote()->getShippingAddress()->getCity(),
            'State' => $cart->getQuote()->getShippingAddress()->getRegion(),
            'Zip' => $cart->getQuote()->getShippingAddress()->getPostcode(),
            'Country' => $cart->getQuote()->getShippingAddress()->getCountry(),
            'Fax' => $cart->getQuote()->getShippingAddress()->getFax(),
            'Email' => $cart->getQuote()->getShippingAddress()->getEmail(),
        ];

        $items = $cart->getQuote()->getAllItems();

        foreach ($items as $item) {
            $lineItems[] = [
                'SKU' => $item->getSku(),
                'ProductName' => $item->getName(),
                'Description' => $item->getName(),
                'UnitPrice' => $item->getPrice(),
                'Qty' => $item->getQty(),
            ];
        }

        return $lineItems;
    }
    // phpcs:enable

    /**
     * Check if user have paid web form for placing order.
     *
     * @param mixed $paymentInternalId
     * @return bool
     */
    public function getWebFormPayment(mixed $paymentInternalId = "")
    {
        $orderPaid = false;
        $storeId = $this->getStoreId();

        try {
            $fromPaymentRequestDateTime = date('Y-m-d') . 'T00:00:00';
            $toPaymentRequestDateTime = date('Y-m-d') . 'T23:59:59';

            $client = $this->getClient($storeId);

            $finalArray = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'fromPaymentRequestDateTime' => $fromPaymentRequestDateTime,
                'toPaymentRequestDateTime' => $toPaymentRequestDateTime,
                'start' => 0,
                'limit' => 1,
                'sort' => 'PaymentRequestDateTime',
                'filters' => [
                    'SearchFilter' => [
                        'FieldName' => 'paymentInternalId',
                        'ComparisonOperator' => 'eq',
                        'FieldValue' => $paymentInternalId,
                    ],
                ],
            ];

            $response = $client->SearchEbizWebFormReceivedPayments($finalArray);

            if (isset($response->SearchEbizWebFormReceivedPaymentsResult->Payment)) {
                $orderPaid = true;
                $webFormPayment = $response->SearchEbizWebFormReceivedPaymentsResult->Payment;
                $this->checkoutSession->setStepData('placeOrder', 'webFormPayment', $webFormPayment);
            }
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }

        return $orderPaid;
    }

    /**
     * Create EbizCharge Hosted Pro Url
     *
     * @param Quote|null $cart
     * @param mixed $userId
     * @param mixed $successUrl
     * @param mixed $declinedUrl
     * @param mixed $errorUrl
     * @param mixed $storeId
     * @param mixed $formType
     * @return array
     * @throws Exception
     */
    public function createEbizHostedProUrl(
        ?Quote $cart = null,
        mixed  $userId = "",
        mixed  $successUrl = "",
        mixed  $declinedUrl = "",
        mixed  $errorUrl = "",
        mixed  $storeId = "0",
        mixed  $formType = ""
    ): array
    {
        return $this->createEbizWebFormHostedUrl(
            $cart,
            $userId,
            $successUrl,
            $declinedUrl,
            $errorUrl,
            $storeId,
            $formType
        );
    }

    /**
     * Create Ebiz WebForm Hosted Url
     *
     * @param Quote $cart
     * @param mixed $userId
     * @param mixed $approvedUrl
     * @param mixed $declinedUrl
     * @param mixed $errorUrl
     * @param mixed $storeId
     * @param mixed $formType
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createEbizWebFormHostedUrl(
        Quote $cart,
        mixed $userId = "",
        mixed $approvedUrl = "",
        mixed $declinedUrl = "",
        mixed $errorUrl = "",
        mixed $storeId = "0",
        mixed $formType = 'WebForm'
    ): array
    {
        $hostedProUrlResp = [
            'error' => true,
            'message' => __('An unknown error occurred during fetch URL.'),
            'response' => [
                GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_EBIZ_HOSTED_PRO_URL => '*',
                GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_PAYMENT_INTERNAL_ID => '*',
                GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE => __(
                    'Error occurred during creating payment URL with EBizCharge Payment Hub.'
                ),
            ],
        ];

        try {
            $cartId = $cart->getEntityId();
            $quote = $this->cart->getQuote()->reserveOrderId()->save();
            $cartReservedIncrementId = $quote->getReservedOrderId();
            $cart->setReservedOrderId($cartReservedIncrementId)->save();
            $storeId = $quote->getStoreId() ?? $this->getStoreId();

            if (!$cart->getItems()) {
                $hostedProUrlResp['message'] = 'No items are assigned to the cart, please add.';
                $hostedProUrlResp['response'][GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE]
                    = 'No items are assigned to the cart, please add those.';
                //  return $hostedProUrlResp;
            }
            $linItems = $this->getGraphQLLineItems($cartId);

            if (!$cart->getShippingAddress()->getAddressId()) {
                $hostedProUrlResp['message'] = 'Shipping address could not found, please add.';
                $hostedProUrlResp['response'][GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE]
                    = 'Billing address could not found, please add those.';

                return $hostedProUrlResp;
            }
            $shippingAddress = $this->getGraphQLCustomerShippingAddress($cartId);

            if (!$cart->getBillingAddress()->getAddressId()) {
                $hostedProUrlResp['message'] = 'Billing address could not found, please add.';
                $hostedProUrlResp['response'][GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE]
                    = 'Shipping address could not found, please add those.';

                return $hostedProUrlResp;
            }
            $billingAddress = $this->getGraphQLCustomerBillingAddress($cartId);

            $isEbizActive = $this->configFactory->create()->getEbizActive($storeId);
            $isCreditCardActive = $this->configFactory->create()->isCreditCardEnabled($storeId);
            $isBankAccountsActive = $this->configFactory->create()->isAchActive($storeId);
            $cardTypes = implode(',', $this->configFactory->create()->getCcTypes($storeId));
            $payByPaymentTypes = [];
            if ($isEbizActive && $isCreditCardActive) {
                $payByPaymentTypes[] = strtolower(SoapApiModelInterface::EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD);
            }
            if ($isEbizActive && $isBankAccountsActive) {
                $payByPaymentTypes[] = strtolower(SoapApiModelInterface::ACH);
            }

            $payByType = implode(',', $payByPaymentTypes);

            $customerFullName = $cart->getCustomerFirstname() . ' ' . $cart->getCustomerLastname();
            $grandTotal = (float)$cart->getGrandTotal();
            $baseTotal = (float)$cart->getBaseSubtotal();

            $dueAmount = $grandTotal;
            $taxAmount = $cart->getShippingAddress()->getTaxAmount();
            $discountAmount = $cart->getShippingAddress()->getDiscountAmount();
            $shippingAmount = $cart->getShippingAddress()->getShippingAmount();
            $dutyAmount = $cart->getShippingAddress()->getBaseShippingTaxAmount();

            $countryCode = $cart->getBillingAddress()->getCountryId();

            // if ($cart->getCustomerIsGuest()) {
            // //only process payment request as guest, will be advanced later...

            if ($cart->getCustomerIsGuest()) {
                $ebizCustomerId = $userId ? $userId : CustomerInterface::CUSTOMER_TYPE_GUEST;
                $savePaymentMethod = true;
                $showSavedPaymentMethods = false;
            } else {
                $customerId = $cart->getCustomerId();
                $customer = $this->customerFactory->create()->load($customerId);
                $ebizCustomerId = $customer->getEcCustId();
                $savePaymentMethod = $this->configFactory->create()->saveCard($storeId);
                $showSavedPaymentMethods = $this->configFactory->create()->showSavedMethodsCheckout($storeId);
            }

            $billingAddress['Address1'] = isset($billingAddress['Street']) ? $billingAddress['Street'] : '';
            $billingAddress['ZipCode'] = isset($billingAddress['Zip']) ? $billingAddress['Zip'] : '';
            $shippingAddress['Address1'] = isset($shippingAddress['Street']) ? $shippingAddress['Street'] : '';
            $shippingAddress['ZipCode'] = isset($shippingAddress['Zip']) ? $shippingAddress['Zip'] : '';

            $paymentFormParams = [
                'FormType' => $formType,
                'FromEmail' => GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_PARAM_TYPE_FROM_EMAIL,
                'FromName' => GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_PARAM_TYPE_FROM_NAME,
                'EmailAddress' => $cart->getCustomerEmail(),
                'ReplyToEmailAddress' => $cart->getCustomerEmail(),
                'ReplyToDisplayName' => $customerFullName,
                'EmailNotes' => 'Secure Payment for #' . $cartReservedIncrementId . ' ',
                'EmailSubject' => 'Secure Payment for #' . $cartReservedIncrementId . ' ',
                'SendEmailToCustomer' => 'true',
                'CustomerId' => $ebizCustomerId,
                'CustFullName' => $customerFullName,
                'TransDetail' => 'Secure Payment for Order #' . $cartReservedIncrementId . ' ',
                'InvoiceNumber' => $cartReservedIncrementId,
                'PoNum' => $cartReservedIncrementId,
                'SoNum' => $cartReservedIncrementId,
                'OrderId' => $cartReservedIncrementId,
                'Date' => strtok($cart->getCreatedAt(), ' '),
                'DueDate' => strtok($cart->getCreatedAt(), ' '),
                'TotalAmount' => $grandTotal,
                'AmountDue' => $dueAmount,
                'TipAmount' => '0.00',
                'ShippingAmount' => $shippingAmount,
                'DutyAmount' => $dutyAmount,
                'DiscountAmount' => $discountAmount,
                'TaxAmount' => $taxAmount,
                'Description' => 'Secure Payment for the Order # ' . $cartReservedIncrementId
                    . ' via EBizCharge host pro secure URL.',
                'ApprovedURL' => $approvedUrl,
                'DeclinedURL' => $declinedUrl,
                'ErrorURL' => $errorUrl,
                'DisplayDefaultResultPage' => 0,
                'PayByType' => $payByType,
                'AllowedPaymentMethods' => $cardTypes,
                'SavePaymentMethod' => $savePaymentMethod,
                'ShowSavedPaymentMethods' => $showSavedPaymentMethods,
                'CountryCode' => $countryCode,
                'CurrencyCode' => $cart->getBaseCurrencyCode(),
                'ProcessingCommand' => PaymentInterface::EBIZCHARGE_COMMAND_SALE,
                'SoftwareId' => self::EBIZCHARGE_MAGENTO_SOFTWARE,
                'Clerk' => 'Magento2WebForm',
                'Terminal' => 'Terminal1',
                'LineItems' => $linItems,
                'BillingAddress' => $billingAddress,
                'ShippingAddress' => $shippingAddress,
            ];

            $payFormParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'ePaymentForm' => $paymentFormParams,
            ];
            $webFormResponse = $this->getClient($storeId)->GetEbizWebFormURL($payFormParams);

            if ($webFormResponse->GetEbizWebFormURLResult) {
                $hostedProUrl = $webFormResponse->GetEbizWebFormURLResult;
                $hostedResult = explode('?pid=', $hostedProUrl);
                $paymentInternalId = isset($hostedResult[1]) ? $hostedResult[1] : '';
                $hostedProUrlResp = [
                    'error' => false,
                    'message' => __('Success, the web form url prepared successfully'),
                    'response' => [
                        GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_EBIZ_HOSTED_PRO_URL => $hostedProUrl,
                        GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_PAYMENT_INTERNAL_ID =>
                            $paymentInternalId,
                        // phpcs:ignore
                        GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE =>
                            __('Success, the hosted pro secure payment url has been created successfully
                            with EBizCharge Payment Hub'),
                    ],
                ];
            }
        } catch (SoapFault $soapFault) {
            $errorMessage = __('Critical Exception Error while retrieving web form url: '
                . $soapFault->getMessage());
            $this->ebizchargeLogger->addCritical($errorMessage);
            $hostedProUrlResp['response'][GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE]
                = $errorMessage;
        }

        return $hostedProUrlResp;
    }

    /**
     * Get Line Items
     *
     * @param mixed $cartId
     * @return array
     */
    public function getGraphQLLineItems(mixed $cartId = "0")
    {
        $lineItems = [];

        try {
            $currentQuote = $this->cartRepositoryInterface->get($cartId);

            if ($currentQuote) {
                $items = $currentQuote->getItems();
                foreach ($items as $item) {
                    $lineItems[] = [
                        'SKU' => $item->getSku(),
                        'ProductName' => $item->getName(),
                        'Description' => $item->getName(),
                        'UnitPrice' => $item->getPrice(),
                        'Qty' => $item->getQty(),
                    ];
                }
            }

            return $lineItems;
        } catch (Exception $exception) {
            $this->ebizchargeLogger->addCritical(__('Exception occurred during getting line items. '
                . $exception->getMessage()));
        }

        return $lineItems;
    }

    /**
     * Get GraphQL Customer Shipping Address
     *
     * @param mixed $cartId
     * @return array
     */
    public function getGraphQLCustomerShippingAddress(mixed $cartId = "0")
    {
        $this->shippingAddress = [];

        try {
            $currentQuote = $this->cartRepositoryInterface->get($cartId);
            $billingAddress = $currentQuote->getBillingAddress();
            $street = $billingAddress->getStreet();

            if ($currentQuote) {
                $this->shippingAddress = [
                    'FirstName' => $billingAddress->getFirstname(),
                    'LastName' => $billingAddress->getLastname(),
                    'Company' => $billingAddress->getCompany(),
                    'Street' => isset($street[0]) ? $street[0] : '',
                    'Street2' => isset($street[1]) ? $street[1] : '',
                    'City' => $billingAddress->getCity(),
                    'State' => $billingAddress->getRegionCode(),
                    'Zip' => $billingAddress->getPostcode(),
                    'Country' => $billingAddress->getCountryId(),
                    'Fax' => $billingAddress->getFax(),
                    'Email' => $billingAddress->getEmail(),
                ];
            }
        } catch (Exception $exception) {
            $this->ebizchargeLogger->addCritical(__('Exception occurred during getting line items'));
        }

        return $this->shippingAddress;
    }

    /**
     * Billing Address
     *
     * @param mixed $cartId
     * @return array
     */
    public function getGraphQLCustomerBillingAddress(mixed $cartId = "0")
    {
        $this->billingAddress = [];

        try {
            $currentQuote = $this->cartRepositoryInterface->get($cartId);
            $billingAddress = $currentQuote->getBillingAddress();
            $street = $billingAddress->getStreet();

            if ($currentQuote) {
                $this->billingAddress = [
                    'FirstName' => $billingAddress->getFirstname(),
                    'LastName' => $billingAddress->getLastname(),
                    'Company' => $billingAddress->getCompany(),
                    'Street' => isset($street[0]) ? $street[0] : '',
                    'Street2' => isset($street[1]) ? $street[1] : '',
                    'City' => $billingAddress->getCity(),
                    'State' => $billingAddress->getRegionCode(),
                    'Zip' => $billingAddress->getPostcode(),
                    'Country' => $billingAddress->getCountryId(),
                    'Fax' => $billingAddress->getFax(),
                    'Email' => $billingAddress->getEmail(),
                ];
            }
        } catch (Exception $exception) {
            $this->ebizchargeLogger->addCritical(__('Exception occurred during getting line items'));
        }

        return $this->billingAddress;
    }

    /**
     * Prepare Web Hosted Checkout form Url
     *
     * @param mixed $customerId
     * @param mixed $checkoutFormType
     * @param mixed $paymentMethodType
     * @param bool $isRedirect
     * @param mixed $redirectUrl
     * @param mixed $storeId
     * @param mixed $cartId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareWebHostedCheckoutFormUrl(
        mixed $customerId = "0",
        mixed $checkoutFormType = "RPMcheckoutUser",
        mixed $paymentMethodType = "CC",
        bool  $isRedirect = false,
        mixed $redirectUrl = "",
        mixed $storeId = "0",
        mixed $cartId = "0"
    ): array
    {
        $hostedProUrlResp = [
            'error' => true,
            'message' => __('An unknown error occurred during fetch URL.'),
            'hosted_pro_url' => '',
            'payload' => [],
            'response' => [
                'ebiz_hosted_pro_url' => '',
                'error_message' => __('An unknown error occurred during fetch URL.'),
                'payment_internal_id' => '',
            ],
        ];

        $configFactory = $this->configFactory->create();
        $storeId = !empty($storeId) ? $storeId : $configFactory->getStoreId();
        $webHostedFormParams = [
            "store_id" => $storeId
        ];

        $customer = $this->customerFactory->create()->load($customerId);
        $isSavePaymentMethod = $configFactory->saveCard($storeId);
        $isTokenizeOnly = $configFactory->isCardsTokenizeOnly($storeId);
        $isShowSavedPaymentMethods = $configFactory->showSavedMethodsCheckout($storeId);
        $defaultRedirectUrl = $configFactory->getCheckoutWebHostedFormUrl($webHostedFormParams);

        if ($isRedirect) {
            $redirectUrl = !empty($redirectUrl) ? $redirectUrl : $defaultRedirectUrl;
        }

        try {
            $cart = $this->quoteFactory->create()->load($cartId);
            $cart = $this->checkoutSession->getQuote();
            if (!$cart) {
                $cart = $this->checkoutSession->getQuote();
                $cartId = $cart->getId();
                $cart = $this->quoteFactory->create()->load($cartId);
            }
            $cartReservedIncrementId = $cart->getReservedOrderId();

            if (!$cartReservedIncrementId) {
                $cart = $this->cart->getQuote()->reserveOrderId()->save();
                $cartReservedIncrementId = $cart->getReservedOrderId();
                // $cart->setReservedOrderId($cartReservedIncrementId)->save();
                // $cartReservedIncrementId = $cart->getReservedOrderId();
            }
            if (!$cart->getItems()) {
                $hostedProUrlResp['message'] = 'No items are assigned to the cart, please add.';
                $hostedProUrlResp['response'][GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE]
                    = 'No items are assigned to the cart, please add those.';
                //  return $hostedProUrlResp;
            }
            $linItems = $this->getGraphQLLineItems($cartId);

            if (!$cart->getShippingAddress()->getAddressId()) {
                $hostedProUrlResp['message'] = 'Shipping address could not found, please add.';
                $hostedProUrlResp['response'][GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE]
                    = 'Billing address could not found, please add those.';

                return $hostedProUrlResp;
            }

            $shippingAddress = $this->getGraphQLCustomerShippingAddress($cartId);

            if (!$cart->getBillingAddress()->getAddressId()) {
                $hostedProUrlResp['message'] = 'Billing address could not found, please add.';
                $hostedProUrlResp['response'][GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE]
                    = 'Shipping address could not found, please add those.';

                return $hostedProUrlResp;
            }
            $billingAddress = $this->getGraphQLCustomerBillingAddress($cartId);

            $resultPage = $isRedirect ? 0 : 1;

            $approvedUrl = !empty($redirectUrl) ? $redirectUrl . 'approved/1' : '';
            $declinedUrl = !empty($redirectUrl) ? $redirectUrl . 'declined/1' :
                $configFactory->getCheckoutWebHostedErrorUrl($webHostedFormParams);
            $errorUrl = !empty($redirectUrl) ? $redirectUrl . 'error/1' :
                $configFactory->getCheckoutWebHostedDeclinedUrl($webHostedFormParams);
            $paymentFormParams['hosted_pro_url'] = $errorUrl;

            $isACHActive = $configFactory->isAchActive($storeId);
            $isCreditCardActive = $configFactory->isCreditCardEnabled($storeId);
            $payTypes = $configFactory->getCardTypes($storeId);

            $isEbizActive = $configFactory->getEbizActive($storeId);
            $isCreditCardActive = $configFactory->isCreditCardEnabled($storeId);
            $isBankAccountsActive = $configFactory->isAchActive($storeId);
            $payByPaymentTypes = [];
            if ($isEbizActive && $isCreditCardActive) {
                $payByPaymentTypes[] = strtolower(SoapApiModelInterface::EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD);
            }
            if ($isEbizActive && $isBankAccountsActive) {
                $payByPaymentTypes[] = strtolower(SoapApiModelInterface::ACH);
            }
            $payByType = implode(',', $payByPaymentTypes);
            $payTypes = implode(',', $payTypes);
            $customerFullName = $cart->getCustomerFirstname() . ' ' . $cart->getCustomerLastname();
            $grandTotal = $cart->getGrandTotal() ?? 0;
            $baseGrandTotal = $cart->getBaseGrandTotal() ?? 0;
            $baseSubTotal = $cart->getBaseSubtotal() ?? 0;
            $totalAmount = $grandTotal;
            $taxAmount = $cart->getShippingAddress()->getTaxAmount();
            $discountAmount = $cart->getShippingAddress()->getDiscountAmount();
            $shippingAmount = $cart->getShippingAddress()->getShippingAmount();
            $dutyAmount = $cart->getShippingAddress()->getBaseShippingTaxAmount();

            $countryCode = $cart->getBillingAddress()->getCountryId();
            $customerEmail = $cart->getCustomerEmail() ?? $cart->getBillingAddress()->getEmail();

            if ($cart->getCustomerIsGuest()) {
                $ebizCustomerId = $customerId ?? CustomerInterface::CUSTOMER_TYPE_GUEST;
                $guestCustomerId = $ebizCustomerId;
                $isSavePaymentMethod = false;
                $isShowSavedPaymentMethods = false;
                $billingFirstName = isset($billingAddress['FirstName']) ? $billingAddress['FirstName'] :
                    $ebizCustomerId;
                $billingLastName = isset($billingAddress['LastName']) ? $billingAddress['LastName'] : $ebizCustomerId;
                $customerFullName = $billingFirstName . ' ' . $billingLastName;
                $ebizCustomerId = $this->customerFactory->create()->saveGuestCustomerToEbizcharge($guestCustomerId);
            } else {
                $customerId = $cart->getCustomerId();
                $customer = $this->customerFactory->create()->load($customerId);
                $ebizCustomerId = $customer->getEcCustId();
                $customerEmail = $customer->getEmail();
                $customerFullName = $customer->getFirstname() . ' ' . $customer->getLastname();
            }

            $configPaymentAction = $configFactory->getPaymentAction($storeId);
            $paymentCommand = PaymentInterface::EBIZCHARGE_COMMAND_AUTHONLY;

            if (PaymentInterface::PAYMENT_TRANSACTION_TYPE_AUTHORIZE === $configPaymentAction) {
                $paymentCommand = PaymentInterface::EBIZCHARGE_COMMAND_AUTHONLY;
            }
            if (PaymentInterface::PAYMENT_TRANSACTION_TYPE_AUTHORIZE_CAPTURE === $configPaymentAction) {
                // $paymentCommand = PaymentInterface::EBIZCHARGE_COMMAND_CAPTURE;
                $paymentCommand = PaymentInterface::EBIZCHARGE_COMMAND_AUTHONLY;
            }

            if ((bool)$isTokenizeOnly === true) {
                $grandTotal = 0;
                $shippingAmount = 0;
                $discountAmount = 0;
                $taxAmount = 0;
                $dutyAmount = 0;
                $dueAmount = 0;
                $totalAmount = 0;
            }
            $billingAddress['Address1'] = isset($billingAddress['Street']) ? $billingAddress['Street'] : '';
            $billingAddress['ZipCode'] = isset($billingAddress['Zip']) ? $billingAddress['Zip'] : '';
            $shippingAddress['Address1'] = isset($shippingAddress['Street']) ? $shippingAddress['Street'] : '';
            $shippingAddress['ZipCode'] = isset($shippingAddress['Zip']) ? $shippingAddress['Zip'] : '';

            $paymentFormParams = [
                'FormType' => $checkoutFormType,
                'FromEmail' => GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_PARAM_TYPE_FROM_EMAIL,
                'FromName' => GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_PARAM_TYPE_FROM_NAME,
                'EmailAddress' => $customerEmail,
                'ReplyToEmailAddress' => $customerEmail,
                'ReplyToDisplayName' => $customerFullName,
                'EmailNotes' => 'Secure Payment for #' . $cartReservedIncrementId . ' ',
                'EmailSubject' => 'Secure Payment for #' . $cartReservedIncrementId . ' ',
                'SendEmailToCustomer' => false,
                'CustomerId' => $ebizCustomerId,
                'CustFullName' => $customerFullName,
                'TransDetail' => 'Secure Payment for Order #' . $cartReservedIncrementId . ' ',
                'InvoiceNumber' => $cartReservedIncrementId,
                'PoNum' => $cartReservedIncrementId,
                'SoNum' => $cartReservedIncrementId,
                'OrderId' => $cartReservedIncrementId,
                'Date' => strtok($cart->getCreatedAt(), ' '),
                'DueDate' => strtok($cart->getCreatedAt(), ' '),
                'TotalAmount' => (string)$grandTotal,
                'AmountDue' => (string)$totalAmount,
                'TipAmount' => '0.00',
                'ShippingAmount' => (string)$shippingAmount,
                'DutyAmount' => (string)$dutyAmount,
                'DiscountAmount' => (string)$discountAmount,
                'TaxAmount' => (string)$taxAmount,
                'Description' => 'Secure Payment for the Order # ' . $cartReservedIncrementId
                    . ' via EBizCharge host pro secure URL.',
                'ApprovedURL' => $approvedUrl,
                'DeclinedURL' => $declinedUrl,
                'ErrorURL' => $errorUrl,
                'DisplayDefaultResultPage' => $resultPage,
                'PayByType' => $payByType,
                'AllowedPaymentMethods' => $payTypes,
                'SavePaymentMethod' => $isSavePaymentMethod,
                'ShowSavedPaymentMethods' => $isShowSavedPaymentMethods,
                'CountryCode' => $countryCode,
                'CurrencyCode' => $cart->getBaseCurrencyCode(),
                'ProcessingCommand' => strtolower($paymentCommand),
                //    'ProcessingCommand' => "sale",
                'SoftwareId' => $this->getSoftwareId(),
                'Clerk' => 'Magento2WebForm',
                'Terminal' => 'Terminal1',
                'LineItems' => $linItems,
                'BillingAddress' => $billingAddress,
                'ShippingAddress' => $shippingAddress,
            ];

            $payFormParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'ePaymentForm' => $paymentFormParams,
            ];
            $webFormResponse = $this->getClient($storeId)->GetEbizWebFormURL($payFormParams);

            if ($webFormResponse->GetEbizWebFormURLResult) {
                $hostedProUrl = $webFormResponse->GetEbizWebFormURLResult;
                $hostedResult = explode('?pid=', $hostedProUrl);
                $paymentInternalId = isset($hostedResult[1]) ? $hostedResult[1] : '';
                $hostedProUrlResp = [
                    'error' => false,
                    'message' => __('Success, the web form url prepared successfully'),
                    'hosted_pro_url' => $hostedProUrl,
                    'payload' => $paymentFormParams,
                    'response' => [
                        GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_EBIZ_HOSTED_PRO_URL => $hostedProUrl,
                        GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_PAYMENT_INTERNAL_ID =>
                            $paymentInternalId,
                        // phpcs:ignore
                        GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE =>
                            __('Success, the hosted pro secure payment url has been created successfully
                            with EBizCharge Payment Hub'),
                    ],
                ];
            }
        } catch (SoapFault $soapFault) {
            $errorMessage = __('Critical Exception Error while retrieving web form url: '
                . $soapFault->getMessage());
            $this->ebizchargeLogger->addCritical($errorMessage);
            $hostedProUrlResp['response'][GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE]
                = $errorMessage;
        }

        return $hostedProUrlResp;
    }

    /**
     * Create EbizCharge Web Hosted Payment Method URL
     *
     * @param mixed $customerId
     * @param mixed $paymentMethodType
     * @param bool $isRedirect
     * @param mixed $redirectUrl
     * @param mixed $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createEbizWebHostedPaymentMethodFormUrl(
        mixed $customerId = "0",
        mixed $paymentMethodType = "CC",
        bool  $isRedirect = false,
        mixed $redirectUrl = "",
        mixed $storeId = "0"
    ): array
    {
        $hostedProUrlResp = [
            'error' => true,
            'message' => __('An unknown error occurred during fetch URL.'),
            'response' => [
                GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_EBIZ_HOSTED_PRO_URL => '*',
                GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_PAYMENT_INTERNAL_ID => '*',
                GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE => __(
                    'Error occurred during creating payment method URL with EBizCharge Payment Hub.'
                ),
            ],
        ];
        $configFactory = $this->configFactory->create();
        $customer = $this->customerFactory->create()->load($customerId);
        $ebizCustomerId = $customer->getEcCustId();
        $customerEmail = $customer->getEmail();
        $customerFullName = $customer->getFirstname() . ' ' . $customer->getLastname();
        $isSavePaymentMethod = $configFactory->saveCard($storeId);
        $isShowSavedPaymentMethods = $configFactory->showSavedMethodsCheckout($storeId);
        $defaultRedirectUrl = $configFactory->getCardsWebHostDefaultResponseUrl($storeId);

        if ($isRedirect) {
            $redirectUrl = !empty($redirectUrl) ? $redirectUrl : $defaultRedirectUrl;
        }

        try {
            $resultPage = $isRedirect ? 1 : 0;

            $approvedUrl = !empty($redirectUrl) ? $redirectUrl : '';
            $declinedUrl = !empty($redirectUrl) ? $redirectUrl : '';
            $errorUrl = !empty($redirectUrl) ? $redirectUrl : '';

            $isACHActive = $configFactory->isAchActive($storeId);
            $isCreditCardActive = $configFactory->isCreditCardEnabled($storeId);
            $types = implode(',', $configFactory->getCcTypes($storeId));

            $payByType = self::EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD;

            $payByType = $isACHActive ? strtolower(self::EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD
                . ',' . self::ACH) : strtolower(self::EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD);

            if ($isCreditCardActive || $isACHActive) {
                if ('CC' === $paymentMethodType) {
                    $payByType = self::EBIZCHARGE_PAYMENT_ACCOUNT_TYPE_CREDIT_CARD;
                }
                if ('ACH' === $paymentMethodType) {
                    $payByType = strtolower(self::ACH);
                    $types = strtolower('Checking,Savings');
                }
            }

            $customerBillingAddress = $customer->getDefaultBillingAddress();
            $customerShippingAddress = $customer->getDefaultShipingAddress();
            $countryCode = $customerBillingAddress ? $customerBillingAddress->getCountryId() : 'US';

            $billingAddressParams = $this->customerFactory->create()->getCustomerAddressAsArray($customerId);
            $shippingAddressParams = $this->customerFactory
                ->create()
                ->getCustomerAddressAsArray($customerId, 'shipping');

            // if ($cart->getCustomerIsGuest()) {
            // //only process payment request as guest, will be advanced later...

            /*
            if ($cart->getCustomerIsGuest() || 1) {
                $ebizCustomerId = $userId ? $userId : CustomerInterface::CUSTOMER_TYPE_GUEST;
                $savePaymentMethod = true;
                $showSavedPaymentMethods = false;
            }
            */

            $paymentFormParams = [
                'FormType' => 'RPMcheckoutUser',
                'FromEmail' => GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_PARAM_TYPE_FROM_EMAIL,
                'FromName' => GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_PARAM_TYPE_FROM_NAME,
                'EmailAddress' => $customerEmail,
                'ReplyToEmailAddress' => $customerEmail,
                'ReplyToDisplayName' => $customerFullName,
                'EmailNotes' => 'Adding Secure Payment Method for User #' . $customerFullName . ' ',
                'EmailSubject' => 'Addition of Secure Payment Method ',
                'SendEmailToCustomer' => false,
                'CustomerId' => $ebizCustomerId,
                'CustFullName' => $customerFullName,
                'TransDetail' => 'Hosted Pro addition of payment method ',
                'Date' => strtok($this->getCurrentDateTime('Y-m-d H:i:s'), ' '),
                'DueDate' => strtok($this->getCurrentDateTime('Y-m-d H:i:s'), ' '),
                'Description' => 'Adding Secure Payment method via EBizCharge host pro secure URL.',
                'ApprovedURL' => $approvedUrl,
                'DeclinedURL' => $declinedUrl,
                'ErrorURL' => $errorUrl,
                'DisplayDefaultResultPage' => $resultPage,
                'PayByType' => $payByType,
                'AllowedPaymentMethods' => $types,
                'SavePaymentMethod' => $isSavePaymentMethod,
                'ShowSavedPaymentMethods' => $isShowSavedPaymentMethods,
                'CountryCode' => $countryCode,
                'SoftwareId' => self::EBIZCHARGE_MAGENTO_SOFTWARE,
                'Clerk' => 'Magento2WebForm',
                'Terminal' => 'Terminal1',
                'LineItems' => [],
                'BillingAddress' => $billingAddressParams,
                'ShippingAddress' => $shippingAddressParams,
            ];

            $paymentMethodFormParams = [
                'securityToken' => $this->getUeSecurityToken($storeId),
                'ePaymentForm' => $paymentFormParams,
            ];
            $webFormResponse = $this->getClient($storeId)->GetEbizWebFormURL($paymentMethodFormParams);
            //   var_dump("<pre>", $paymentMethodFormParams, $webFormResponse);

            if ($webFormResponse->GetEbizWebFormURLResult) {
                $hostedProUrl = $webFormResponse->GetEbizWebFormURLResult;
                $hostedResult = explode('?pid=', $hostedProUrl);
                $paymentInternalId = isset($hostedResult[1]) ? $hostedResult[1] : '';
                $hostedProUrlResp = [
                    'error' => false,
                    'message' => __('Success, the web form url prepared successfully'),
                    'response' => [
                        GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_EBIZ_HOSTED_PRO_URL => $hostedProUrl,
                        GraphQLInterface::GRAPHQL_WEBFORM_TRANSACTION_RESPONSE_PAYMENT_INTERNAL_ID =>
                            $paymentInternalId,
                        // phpcs:ignore
                        GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE =>
                            __('Success, the hosted pro secure payment url has been created successfully
                            with EBizCharge Payment Hub'),
                    ],
                ];
            }
        } catch (SoapFault $soapFault) {
            $errorMessage = __('Critical Exception Error while retrieving web form url: '
                . $soapFault->getMessage());
            $this->ebizchargeLogger->addCritical($errorMessage);
            $hostedProUrlResp['response'][GraphQLInterface::GRAPHQL_WEBFORM_OUTPUT_PARAMS_ERROR_MESSAGE]
                = $errorMessage;
            // var_dump( $soapFault->getMessage());exit;
        }

        return $hostedProUrlResp;
    }

    /**
     * Is Valid Url.
     *
     * @phpcs:disable
     */
    public function isValidUrl(?string $url = null): bool
    {
        if (null === $url) {
            return false;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpcode >= 200 && $httpcode < 300) ? true : false;
    }

    /**
     * Set store id for frontend requests.
     */
    private function setRequestStoreId(): void
    {
        try {
            if (Area::AREA_ADMINHTML != EbizDataHelper::getAreaCode()) {
                $this->storeId = $this->storeManager->getStore()->getId();
            }
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(__('Exception occurred selecting the area code. Error: '
                . $e->getMessage()));

            return;
        }
    }
}
