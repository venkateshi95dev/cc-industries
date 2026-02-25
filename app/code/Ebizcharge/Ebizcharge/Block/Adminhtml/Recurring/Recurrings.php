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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\Recurring;

use Exception;
use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Helper\Data;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config as EbizConfig;
use Ebizcharge\Ebizcharge\Model\Customer;
use Ebizcharge\Ebizcharge\Model\Recurring;
use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\ProductFactory;

use Magento\Backend\Helper\Data as BackendDataHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductCollection;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Block\Address\Renderer\RendererInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\Address\Mapper as AddressMapper;
use Magento\Customer\Model\CustomerIdProvider as AdminCustomerIdProvider;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Shipping\Model\Config as ShippingConfig;

/**
 * Recurring block class
 *
 * Class Recurrings
 */
class Recurrings extends Template
{
    /**
     * Suggession Processor Action URL
     *
     * @const: SUGGESSION_PROCESSOR_ACTION_URL
     */
    public const SUGGESSION_PROCESSOR_ACTION_URL = 'ebizcharge/searchbox/suggessionprocessor';

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $productCollection;

    /**
     * @var PaymentConfig
     */
    protected PaymentConfig $paymentConfig;

    /**
     * @var ShippingConfig
     */
    protected ShippingConfig $shippingConfig;

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @var CustomerCollection
     */
    protected CustomerCollection $customerCollection;

    /**
     * @var RegionCollectionFactory
     */
    protected RegionCollectionFactory $regionCollectionFactory;

    /**
     * @var CountryCollection
     */
    protected CountryCollection $countryCollectionFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var AreaList
     */
    protected AreaList $areaList;

    /**
     * @var BackendDataHelper
     */
    protected BackendDataHelper $backendDataHelper;

    /**
     * @var TranApi
     */
    protected TranApi $soapApiModel;

    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $recurringFactory;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $productFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var AddressRepositoryInterface
     */
    protected AddressRepositoryInterface $addressRepository;

    /**
     * @var AddressConfig
     */
    protected AddressConfig $addressConfig;

    /**
     * @var AddressMapper
     */
    protected AddressMapper $addressMapper;

    /**
     * @var PriceHelper
     */
    protected PriceHelper $priceHelper;

    /**
     * @var ImageHelper
     */
    protected ImageHelper $imageHelper;

    /**
     * @var AdminCustomerIdProvider
     */
    protected AdminCustomerIdProvider $adminCustomerIdProvider;

    /**
     * @var CustomerRegistry
     */
    protected CustomerRegistry $customerRegistry;

    /**
     * @var RedirectInterface
     */
    protected RedirectInterface $redirectInterface;

    /**
     * @var Http
     */
    protected Http $responseInterface;

    /**
     * @var EbizConfig
     */
    protected EbizConfig $ebizConfig;

    /**
     * @var Customer
     */
    public $paymentMethodProfile;

    /**
     * @var RecurringRepositoryInterface
     */
    protected RecurringRepositoryInterface $recurringRepository;

    /**
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param Template\Context $context
     * @param TranApi $tranApi
     * @param PaymentConfig $paymentConfig
     * @param EbizConfig $ebizConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param ShippingConfig $shippingConfig
     * @param Data $dataClass
     * @param Data $helper
     * @param PriceHelper $priceHelper
     * @param CollectionFactory $productCollection
     * @param RecurringRepositoryInterface $recurringRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param CustomerCollection $customerCollection
     * @param RecurringFactory $recurringFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerFactory $customerFactory
     * @param AreaList $areaList
     * @param ImageHelper $imageHelper
     * @param BackendDataHelper $backendDataHelper
     * @param CustomerRegistry $customerRegistry
     * @param ProductFactory $productFactory
     * @param OrderFactory $orderFactory
     * @param AddressConfig $addressConfig
     * @param AddressMapper $addressMapper
     * @param AdminCustomerIdProvider $adminCustomerIdProvider
     * @param Http $responseInterface
     * @param RedirectInterface $redirectInterface
     * @param ProductRepositoryInterface $productRepository
     * @param EbizchargeLogger $ebizchargeLogger
     * @param CountryCollection $countryCollectionFactory
     * @param array $data
     */
    public function __construct(
        RegionCollectionFactory      $regionCollectionFactory,
        Template\Context             $context,
        TranApi                      $tranApi,
        PaymentConfig                $paymentConfig,
        EbizConfig                   $ebizConfig,
        ScopeConfigInterface         $scopeConfig,
        ShippingConfig               $shippingConfig,
        Data                         $dataClass,
        Data                         $helper,
        PriceHelper                  $priceHelper,
        CollectionFactory            $productCollection,
        RecurringRepositoryInterface $recurringRepository,
        OrderRepositoryInterface     $orderRepository,
        CustomerCollection           $customerCollection,
        RecurringFactory             $recurringFactory,
        AddressRepositoryInterface   $addressRepository,
        CustomerFactory              $customerFactory,
        AreaList                     $areaList,
        ImageHelper                  $imageHelper,
        BackendDataHelper            $backendDataHelper,
        CustomerRegistry             $customerRegistry,
        ProductFactory               $productFactory,
        OrderFactory                 $orderFactory,
        AddressConfig                $addressConfig,
        AddressMapper                $addressMapper,
        AdminCustomerIdProvider      $adminCustomerIdProvider,
        Http                         $responseInterface,
        RedirectInterface            $redirectInterface,
        ProductRepositoryInterface   $productRepository,
        EbizchargeLogger             $ebizchargeLogger,
        CountryCollection            $countryCollectionFactory,
        array                        $data = []
    ) {
        /** Parent reconstruct */
        parent::__construct($context, $data);

        /** @var paymentConfig */
        $this->paymentConfig = $paymentConfig;
        /** @var addressRepository */
        $this->addressRepository = $addressRepository;
        /** @var shippingConfig */
        $this->shippingConfig = $shippingConfig;
        /** @var orderRepository */
        $this->orderRepository = $orderRepository;
        /** @var productCollection */
        $this->productCollection = $productCollection;
        /** @var helper */
        $this->helper = $helper;
        /** @var productFactory */
        $this->productFactory = $productFactory;
        /** @var customerCollection */
        $this->customerCollection = $customerCollection;
        /** @var regionCollectionFactory */
        $this->regionCollectionFactory = $regionCollectionFactory;
        /** @var countryCollectionFactory */
        $this->countryCollectionFactory = $countryCollectionFactory;
        /** @var ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var areaList */
        $this->areaList = $areaList;
        /** @var backendDataHelper */
        $this->backendDataHelper = $backendDataHelper;
        /** @var soapApiModel */
        $this->soapApiModel = $tranApi;
        /** @var customerFactory */
        $this->customerFactory = $customerFactory;
        /** @var recurringFactory */
        $this->recurringFactory = $recurringFactory;
        /** @var addressConfig */
        $this->addressConfig = $addressConfig;
        /** @var addressMapper */
        $this->addressMapper = $addressMapper;
        /** @var priceHelper */
        $this->priceHelper = $priceHelper;
        /** @var adminCustomerIdProvider */
        $this->adminCustomerIdProvider = $adminCustomerIdProvider;
        /** @var imageHelper */
        $this->imageHelper = $imageHelper;
        /** @var customerRegistry */
        $this->customerRegistry = $customerRegistry;
        /** @var redirectInterface */
        $this->redirectInterface = $redirectInterface;
        /** @var responseInterface */
        $this->responseInterface = $responseInterface;
        /** @var  ebizConfig */
        $this->ebizConfig = $ebizConfig;
    }

    /**
     * Add new payment functions
     *
     * @return string
     */
    public function getRequestCardCodeAdmin()
    {
        return $this->ebizConfig->getRequestCardCodeAdmin();
    }

    /**
     * Get Default DropDownProducts
     *
     * @return mixed
     */
    public function getDefaultDropDownProducts()
    {
        $searchParams = [
            "keywords" => "",
            "limit" => 50
        ];
        return $this->productFactory->create()->renderProductSearchListing($searchParams);
    }

    /**
     * Get Default Dropdown Customers
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getDefaultDropDownCustomers()
    {
        $searchParams = [
            "keywords" => "",
            "limit" => 50
        ];
        return $this->customerFactory->create()->renderCustomerSearchListing($searchParams);
    }

    /**
     * Get CC Available Types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $storeId = $this->ebizConfig->getStoreId();
        return $this->ebizConfig->getSelectedPaymentCardTypes($storeId);
    }

    /**
     * Get Payment CC Types
     *
     * @return string[]
     */
    public function getPaymentCctypes()
    {
        return explode(',', $this->helper->getPaymentCctypes());
    }

    /**
     * Get CC Months
     *
     * @return array|mixed|null
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');

        if ($months === null) {
            $months[0] = __('Month');
            $months = array_merge($months, $this->paymentConfig->getMonths());
            $this->setData('cc_months', $months);
        }

        return $months;
    }

    /**
     * Get CC Years
     *
     * @return array|mixed|null
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');

        if ($years === null) {
            $years = $this->paymentConfig->getYears();
            $years = [0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }

    /**
     * Format Price
     *
     * @param int $amount
     * @return float|string
     */
    public function formatPrice($amount = 0)
    {
        return $this->priceHelper->currency($amount);
    }

    /**
     * Get All Items list for New Product Subscriptions
     *
     * @return void
     * @phpcs:disable
     */
    public function getAllItemsList()
    {
        $collection = $this->productCollection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToSort('name')
            ->load();

        foreach ($collection as $product) {
            if (($product->getTypeId() != 'configurable') && ($product->getTypeId() != 'grouped') &&
                ($product->getPrice() > 0)) {
                ?>
                <option value="<?php echo $product->getId(); ?>"><?php echo $product->getName(); ?>
                    (<?php echo "Price: " . number_format((float)$product->getPrice(), 2, '.', ''); ?>)
                </option>
                <?php
            }
        }
    }

    /**
     * Get All Active Customers List
     *
     * @return void
     */
    public function getAllActiveCustomersList()
    {
        $customersList = $this->customerCollection->create()->load();

        if ($customersList && count($customersList) > 0) {
            foreach ($customersList as $customer) {
                ?>
                <option
                    value="<?php echo $customer->getId(); ?>"><?php echo $customer->getFirstname() . ' ' .
                        $customer->getLastname(); ?>
                    (<?php echo $customer->getEmail(); ?>)
                </option>
                <?php
            }
        }
    }

    /**
     * Get Shipping Methods
     *
     * @param mixed $selectedMethod
     * @return void
     */
    public function getShippingMethods($selectedMethod = null)
    {

        $carriers = $this->shippingConfig->getAllCarriers();

        foreach ($carriers as $carrierCode => $carrierModel) {
            if ($carrierModel->isActive()) {
                $carrierMethods = $carrierModel->getAllowedMethods();
                if ($carrierMethods) {
                    //$carrierTitle = $this->_scopeConfig->getValue('carriers/' . $carrierCode .
                    // '/title',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                    foreach ($carrierMethods as $methodCode => $methodTitle) {
                        $value = $carrierCode . '_' . $methodCode;
                        $title = $methodTitle . ' [' . $carrierCode . ']'; ?>
                        <option value="<?php echo $value ?>"<?php if ($value === $selectedMethod) {
                            echo 'selected';
                                       } ?>>
                            <?php echo $title ?>
                        </option>

                        <?php
                    }
                }
            }
        }

    }
    // phpcs:enable

    /**
     * Prepare Recurring Message
     *
     * @param null|mixed $recurringId
     * @return string
     */
    public function prepareRecurringMessage($recurringId = null)
    {
        if (!$recurringId) {
            return "";
        }

        $messageOut = "";
        $recurring = $this->recurringFactory->create()->load($recurringId);
        $recurringStatus = $recurring->getRecStatus();
        /** @var  $recEndDate */
        $recEndDate = $recurring->getEbRecEndDate();
        /** @var  $expire */
        $expire = date("Y-m-d H:i:s", strtotime($recEndDate));

        switch ($recurringStatus) {
            case Recurring::EBIZCHARGE_RECURRING_STATUS_SUSPENDED:
                /** @var  $messageOut */
                $messageOut = __("(This subscription has been suspended)");
                break;
            case Recurring::EBIZCHARGE_RECURRING_STATUS_CANCELED:
                /** @var  $messageOut */
                $messageOut = __("(This subscription has been canceled)");
                break;
            case Recurring::EBIZCHARGE_RECURRING_STATUS_EXPIRED:
                /** @var  $messageOut */
                $messageOut = __("(This subscription has been expired.)");
                break;
            default:
                $messageOut = "";
                if ($expire < date('Y-m-d H:i:s')) {
                    $messageOut = __("(This subscription has been expired.)");
                }
                break;
        }

        return $messageOut;
    }

    /**
     * Prepare Subscription Buttons Status
     *
     * @param int $recurringId
     * @return bool[]
     */
    public function prepareSubscriptionButtonsStatus($recurringId = 0)
    {
        $buttonsStatusResponse = [
            'save' => true,
            'suspend' => true,
            'unsubscribe' => true
        ];

        $recurring = $this->recurringFactory->create()->load($recurringId);
        $recurringStatus = $recurring->getRecStatus();
        /** @var  $recEndDate */
        $recEndDate = $recurring->getEbRecEndDate();

        /** @var  $expire */
        $expireDate = date("Y-m-d H:i:s", strtotime($recEndDate));
        $isExpired = false;

        if ($expireDate < date("Y-m-d H:i:s")) {
            $isExpired = true;
        }

        if ($isExpired === false) {
            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_ACTIVE) {
                $buttonsStatusResponse = [
                    'save' => true,
                    'suspend' => true,
                    'unsubscribe' => true
                ];
            }

            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_EXPIRED) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => true,
                    'unsubscribe' => true
                ];
            }
            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_SUSPENDED) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => true,
                    'unsubscribe' => true
                ];
            }

            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_CANCELED) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => false,
                    'unsubscribe' => false
                ];
            }

        } else {
            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_ACTIVE) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => true,
                    'unsubscribe' => true
                ];
            }
            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_EXPIRED) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => true,
                    'unsubscribe' => true
                ];
            }

            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_SUSPENDED) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => true,
                    'unsubscribe' => true
                ];
            }
            if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_CANCELED) {
                $buttonsStatusResponse = [
                    'save' => false,
                    'suspend' => false,
                    'unsubscribe' => false
                ];
            }
        }

        return $buttonsStatusResponse;
    }

    /**
     * Get Configured Frequencies
     *
     * @param mixed $selectedFrequency
     * @return void
     */
    public function getConfiguredFrequencies($selectedFrequency = null)
    {
        $this->ebizConfig->getRecurringFrequencyOptions($selectedFrequency);
    }

    /**
     * Get Country Region Select
     *
     * @return string
     */
    public function getCountryRegionSelect()
    {
        try {
            $options = $this->regionCollectionFactory->create()
                ->addCountryFilter('US')
                ->load()
                ->toOptionArray();

            /** defining the Block
             *  Definition and create block
             */
            return $this->getLayout()->createBlock(
                Select::class
            )->setName(
                'ship_region'
            )->setTitle(
                __('State/Province')
            )->setId(
                'region_id'
            )->setClass(
                'input-text admin__control-text admin__control-select required-entry validate-state'
            )->setValue(
                null
            )->setOptions(
                $options
            )->getHtml();
        } catch (Exception $e) {
            $this->ebizchargeLogger->addCritical(
                __("Exception occured during creating state province regison dropdown Error: " .
                    $e->getMessage())
            );
            return '';
        }
    }

    /**
     * Is Recurring Fields Disabled
     *
     * @param null|mixed $recurringId
     * @return string
     */
    public function isRecurringFieldsDisabled($recurringId = null)
    {
        $disabled = "";

        if (!$recurringId) {
            return $disabled;
        }

        $recurring = $this->recurringFactory->create()->load($recurringId);
        $recurringStatus = $recurring->getRecStatus();

        /** @var  $recEndDate */
        $recEndDate = $recurring->getEbRecEndDate();

        /** @var  $expire */
        $expireDate = date("Y-m-d H:i:s", strtotime($recEndDate));
        $isExpired = false;

        if ($expireDate < date("Y-m-d H:i:s")) {
            $isExpired = true;
        }

        if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_CANCELED) {
            $disabled = "disabled";
        }
        if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_EXPIRED || $isExpired === true) {
            $disabled = "disabled";
        }
        if ($recurringStatus === Recurring::EBIZCHARGE_RECURRING_STATUS_SUSPENDED) {
            $disabled = "disabled";
        }
        if (!$this->isCreditCardAllowed() && !$this->isBankAccountsAllowed()) {
            $disabled = "disabled";
        }

        return $disabled;
    }

    /**
     * Is Credit Card Allowed
     *
     * @return bool
     */
    public function isCreditCardAllowed()
    {
        return $this->ebizConfig->isCreditCardEnabled();
    }

    /**
     * Is Bank Accounts Allowed
     *
     * @return bool
     */
    public function isBankAccountsAllowed()
    {
        return $this->ebizConfig->isAchActive();
    }

    /**
     * Is Recurring Expired
     *
     * @param int $recurringId
     * @return bool
     */
    public function isRecurringExpired($recurringId = 0): bool
    {
        /** @var  $isRecurringExpired */
        $isRecurringExpired = false;

        if (!$recurringId) {
            return $isRecurringExpired;
        }

        $recurring = $this->recurringFactory->create()->load($recurringId);
        $recurringStatus = $recurring->getRecStatus();

        /** @var  $recEndDate */
        $recEndDate = $recurring->getEbRecEndDate();

        /** @var  $expire */
        $expireDate = date("Y-m-d H:i:s", strtotime($recEndDate));

        if ($expireDate < date("Y-m-d H:i:s") || $recurringStatus ===
            Recurring::EBIZCHARGE_RECURRING_STATUS_EXPIRED || $recurringStatus ===
            Recurring::EBIZCHARGE_RECURRING_STATUS_SUSPENDED) {
            $isRecurringExpired = true;
        }

        return $isRecurringExpired;
    }

    /**
     * Get card payments status
     *
     * @return mixed
     */
    public function isCreditCardEnabled()
    {
        return $this->ebizConfig->isCreditCardEnabled();
    }

    /**
     * Get ACH Status
     *
     * @return mixed
     */
    public function getAchStatus()
    {
        return $this->ebizConfig->isAchActive();
    }

    /**
     * Returns country html select
     *
     * @return string
     */
    public function getCountrySelect()
    {
        try {
            $options = $this->countryCollectionFactory->create()->load()->toOptionArray();

            return $this->getLayout()->createBlock(
                Select::class
            )->setName(
                'ship_country'
            )->setTitle(
                __('Country')
            )->setId(
                'country'
            )->setClass(
                'input-text admin__control-text admin__control-select required-entry validate-state'
            )->setValue(
                'US'
            )->setOptions(
                $options
            )->getHtml();
        } catch (Exception $e) {
            $this->ebizchargeLogger->addCritical(
                __("Error occured during preparing dropdown for country Error: " . $e->getMessage())
            );
            return '';
        }
    }

    /**
     * Get Suggession Action URl
     *
     * @return string
     */
    public function getSuggessionActionUrl()
    {
        $adminUri = $this->backendDataHelper->getAreaFrontName('adminhtml');
        return str_replace(
            $adminUri . '/',
            '',
            $this->getUrl(self::SUGGESSION_PROCESSOR_ACTION_URL)
        );
    }

    /**
     * Get Loader
     *
     * @return string
     */
    public function getLoader()
    {
        return $this->getViewFileUrl('Ebizcharge_Ebizcharge::images/suggession-loader.gif');
    }

    /**
     * Get Customer Saved Payment Methods
     *
     * @param int $recurringId
     * @return array
     */
    public function getCutomerSavedPaymentMethods($recurringId = 0)
    {
        $customerPaymentMethods = [];
        if (!$recurringId) {
            return $customerPaymentMethods;
        }

        /** @var  $orderRecurring */
        $orderRecurring = $this->recurringFactory->create()->load($recurringId);
        /** @var $customerId */
        $customerId = $orderRecurring->getMageCustId();
        /** @var $paymentMethodId */
        $paymentMethodId = $orderRecurring->getEbRecScheduledPaymentInternalId();
        /** @var $customer */
        $customerFactory = $this->customerFactory->create();

        /** @var $paymethodResponse */
        $paymethodResponse = $customerFactory->getEbizCustomerPaymentMethods($customerId);

        if (count($paymethodResponse) > 0) {
            $customerPaymentMethods = $paymethodResponse;
        }
        return $customerPaymentMethods;
    }

    /**
     * Get Customer Address List
     *
     * @param string $selectedId
     * @return string
     */
    public function getCustomerAddressList($selectedId = '')
    {
        return $this->customerFactory->create()->getCustomerAddressList($this->getMageCustId(), $selectedId);
    }

    /**
     * Get customer magento id
     *
     * @return mixed
     */
    public function getMageCustId()
    {
        return $this->getRequest()->getParam('magcid');
    }

    /**
     * Get Saved Payment Method Id
     *
     * @param null|mixed $recurringId
     * @return string|null
     */
    public function getSavedPaymentMethodId($recurringId = null)
    {
        if (!$recurringId) {
            return null;
        }
        $recurring = $this->recurringFactory->create()->load($recurringId);

        if ($recurring) {
            return $recurring->getEbRecMethodId();
        }
        return null;
    }

    /**
     * Get Recurring Data
     *
     * @param int $recurringId
     * @return bool|Recurring
     */
    public function getRecurringData($recurringId = 0)
    {
        if (!$recurringId) {
            return false;
        }
        $recurringData = $this->recurringFactory->create()->load($recurringId);

        if ($recurringData->getId()) {
            return $recurringData;
        }
        return false;
    }

    /**
     * Get Ebiz Custeomer Id
     *
     * @return mixed
     */
    public function getEbzcCustId()
    {
        return $this->getRequest()->getParam('cid');
    }

    /**
     * Get Ebiz Custeomer Id
     *
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * Get Payment Methods
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        $ebizCustomer = $this->soapApiModel->getCustomer($this->getMageCustId());

        if ($ebizCustomer !== null) {
            $profiles = isset($ebizCustomer->PaymentMethodProfiles)
                ? $ebizCustomer->PaymentMethodProfiles->PaymentMethodProfile ?? []
                : [];

            if (is_object($profiles)) {
                $paymentMethods[] = $profiles;
            } else {
                $paymentMethods = $profiles;
            }
            return $paymentMethods;
        }

        return [];
    }

    /**
     * Check Transaction Status
     *
     * @return void
     */
    public function checkTransactionStatus()
    {
        /** @var $ueSecurityToken */
        $ueSecurityToken = $this->soapApiModel->getUeSecurityToken();
        $client = $this->soapApiModel->getClient();

        /** @var $response */
        $response = $client->SearchRecurringPayments(
            [
                'securityToken' => $ueSecurityToken,
                'fromDateTime' => '2020-12-01',
                'toDateTime' => date('Y-m-d'),
                'start' => 0,
                'limit' => 1000,
            ]
        );

        $recurringPayments = $response->SearchRecurringPaymentsResult->Payment;

        $declineTransactions = [];

        if (!empty($recurringPayments)) {
            foreach ($recurringPayments as $payment) {
                $responseGetTransactionDetails = $client->GetTransactionDetails(
                    [
                        'securityToken' => $ueSecurityToken,
                        'transactionRefNum' => $payment->RefNum,
                    ]
                );

                $transactionResult = $responseGetTransactionDetails->GetTransactionDetailsResult;
                $paymentRes = $transactionResult->Response;

                if ($paymentRes->ResultCode == 'D') {
                    $declineTransactions[$payment->ScheduledPaymentInternalId][] = 1;
                }
            }
        }

        if (!empty($declineTransactions)) {
            foreach ($declineTransactions as $scheduledPaymentInternalId => $val) {
                if (count($val) > 2) {
                    // suspend recurring
                    $params = [
                        'securityToken' => $ueSecurityToken,
                        'scheduledPaymentInternalId' => $scheduledPaymentInternalId,
                        'statusId' => 1,
                    ];

                    $client->ModifyScheduledRecurringPaymentStatus($params);
                }
            }
        }
    }

    /**
     * >Is Card Code Required For Admin
     *
     * @return bool[]
     */
    public function isCardCodeRequiredForAdmin()
    {
        return $this->customerFactory->create()->isCvvRequiredForAdminSidePaymentMethod();
    }

    /**
     * Get Search Scheduled Recurring Payments
     *
     * @return mixed
     */
    public function getSearchScheduledRecurringPayments()
    {
        $payments = $this->soapApiModel->getSearchScheduledRecurringPayments(
            $this->getEbzcCustInternalId(),
            $this->getRequest()->getParam('mid')
        );
        if ($payments) {
            return $payments;
        } else {
            $this->redirectInterface->redirect($this->responseInterface, 'ebizcharge_ebizcharge/recurrings');
        }
    }

    /**
     * Get customer internal id
     *
     * @return string
     */
    public function getEbzcCustInternalId()
    {
        $customer = $this->getCustomerDetail($this->getMageCustId());
        return $customer ? $customer->getEcCustInternalid() : '';
    }

    /**
     * Get Customer Detail
     *
     * @param mixed $customerID
     * @return \Magento\Customer\Model\Customer|null
     */
    public function getCustomerDetail($customerID)
    {
        if (!empty($customerID)) {
            try {
                return $this->customerRegistry->retrieve($customerID);
            } catch (Exception $e) {
                $this->ebizchargeLogger->addError(__("Exception occured " . $e->getMessage()));
                return null;
            }
        }
        return null;
    }

    /**
     * Get Customer Detail
     *
     * @param null|mixed $customerId
     * @return bool|Customer
     */
    public function getCustomer($customerId = null)
    {
        if (!$customerId) {
            return false;
        }
        /** @var  $customer */
        $customer = $this->customerFactory->create()->load($customerId);
        if ($customer) {
            return $customer;
        }
        return false;
    }

    /**
     * Get Recurring Ordered Items
     *
     * @param int $recurringId
     * @return array
     */
    public function getRecurringOrderedItems($recurringId = 0)
    {
        $recurredItems = [];
        $recurringCollection = $this->recurringFactory->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $recurringId);
        if (count($recurringCollection) > 0) {
            return $recurringCollection;
        }
        return $recurredItems;
    }

    /**
     * Get formatted address html
     *
     * @param mixed $orderId
     * @param null|mixed $shippingAddressId
     * @return string|null
     */
    public function getCustomerShippingAddress($orderId, $shippingAddressId = null): ?string
    {
        $html = '';
        try {
            if (empty($orderId) || !empty($shippingAddressId)) {
                $address = $this->addressRepository->getById($shippingAddressId);
            } else {
                $order = $this->orderRepository->get($orderId);
                $address = $order->getShippingAddress();
            }

            /** @var RendererInterface $renderer */
            $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
            $html = $renderer->renderArray($this->addressMapper->toFlatArray($address));

            return $html;
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(__("Exception occured during getting Address " . $e->getMessage()));
            return $html;
        }
    }

    /**
     * Get formatted address html
     *
     * @param mixed $orderId
     * @param null|mixed $billingAddressId
     * @return string|null
     */
    public function getCustomerBillingAddress($orderId, $billingAddressId = null): ?string
    {
        $html = '';
        try {
            if (empty($orderId) || !empty($billingAddressId)) {
                $address = $this->addressRepository->getById($billingAddressId);
            } else {
                $order = $this->orderRepository->get($orderId);
                $address = $order->getShippingAddress();
            }

            /** @var RendererInterface $renderer */
            $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
            $html = $renderer->renderArray($this->addressMapper->toFlatArray($address));

            return $html;
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(__("Exception occured during getting Address " . $e->getMessage()));
            return $html;
        }
    }

    /**
     * Get Recurring
     *
     * @return array|RecurringInterface|false|null
     */
    public function getRecurring()
    {
        try {
            return $this->recurringRepository->getById(
                $this->getEbzcMethodId(),
                'eb_rec_scheduled_payment_internal_id'
            );
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(__("Exception occured " . __METHOD__ . $e->getMessage()));

            return [];
        }
    }

    /**
     * Get EBiz Method Id
     *
     * @return mixed
     */
    public function getEbzcMethodId()
    {
        return $this->getRequest()->getParam('mid');
    }

    /**
     * Get recurring product
     *
     * @param string $productId
     * @return ProductInterface|null
     */
    public function getProduct(string $productId): ?ProductInterface
    {
        if (!$productId) {
            return null;
        }
        try {
            return $this->productFactory->create()->load($productId);
        } catch (Exception $e) {
            $this->ebizchargeLogger->addError(__("Exception occurred during loading product " . $e->getMessage()));
            return null;
        }
    }

    /**
     * Is AVS Cvv Enabled
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isAvsCvvEnabled()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        return $this->ebizConfig->isAvsCvvZipEnabled($storeId);
    }

    /**
     * Get customer id in admin customer edit
     *
     * @return int
     */
    public function getCustomerIdAdmin()
    {
        return $this->adminCustomerIdProvider->getCustomerId();
    }

    /**
     * Get customer card for edit admin section
     *
     * @return mixed
     */
    public function getCustomerPaymentMethodProfile()
    {
        $customerId = $this->_request->getParam('id');
        $customer = $this->customerFactory->create()->load($customerId);
        $ebzcMethodToken = $customer->getEcCustToken();
        $ebzcMethodId = $this->_request->getParam('mid');
        $this->paymentMethodProfile = $this->customerFactory->create();

        return $this->paymentMethodProfile->getPaymentMethodProfileById($ebzcMethodId, $ebzcMethodToken);
    }

    /**
     * Get Payment Method Name
     *
     * @return mixed|string
     */
    public function getPaymentMethodName()
    {
        return $this->paymentMethodProfile->getPaymentProfileMethodName();
    }

    /**
     * Get Payment Method Name Json String
     *
     * @return array|string|string[]
     */
    public function getPaymentMethodNameJsonString()
    {
        return $this->paymentMethodProfile->getPaymentMethodNameJsonString();
    }

    /**
     * Get Payment Method Type
     *
     * @return mixed|string
     */
    public function getPaymentMethodType()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileMethodType();
    }

    /**
     * Get Payment Method Json Name
     *
     * @return mixed|string
     */
    public function getPaymentMethodJsonName()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileJsonName();
    }

    /**
     * Get Payment Method Json Type
     *
     * @return mixed|string
     */
    public function getPaymentMethodJsonType()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileJsonType();
    }

    /**
     * Get Payment Method Id
     *
     * @return mixed|string
     */
    public function getPaymentMethodId()
    {
        return $this->paymentMethodProfile->getPaymentProfileMethodId();
    }

    /**
     * Get Payment Method Exp Month
     *
     * @return string
     */
    public function getPaymentMethodExpMonth()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileExpMonth();
    }

    /**
     * Get Payment Method Exp Year
     *
     * @return string
     */
    public function getPaymentMethodExpYear()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileExpYear();
    }

    /**
     * Get Payment Method Created At
     *
     * @return mixed|string
     */
    public function getPaymentMethodCreatedAt()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileCreatedAt();
    }

    /**
     * Get Payment Method Modified At
     *
     * @return mixed|string
     */
    public function getPaymentMethodModifiedAt()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileModifiedAt();
    }

    /**
     * Get Payment Method Card No
     *
     * @return mixed|string
     */
    public function getPaymentMethodCardNumber()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileCardNumber();
    }

    /**
     * Get Payment Method Avs Street
     *
     * @return mixed|string
     */
    public function getPaymentMethodAvsStreet()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileAvsStreet();
    }

    /**
     * Get Payment Method Avs Zip
     *
     * @return mixed|string
     */
    public function getPaymentMethodAvsZip()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileAvsZip();
    }

    /**
     * Get Payment Method Account Holder Name
     *
     * @return mixed|string
     */
    public function getPaymentMethodAccountHolderName()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileAccountHolderName();
    }

    /**
     * Get Payment Method Card Type
     *
     * @return mixed|string
     */
    public function getPaymentMethodCardType()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileCardType();
    }

    /**
     * Get Payment Method Profile Balance
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileBalance()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileBalance();
    }

    /**
     * Get Payment Method Profile Max Balance
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileMaxBalance()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileMaxBalance();
    }

    /**
     * Get Payment Method Profile Reload Schedule
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileReloadSchedule()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileReloadSchedule();
    }

    /**
     * Get Payment Method Profile Secondary Sort
     *
     * @return mixed|string
     */
    public function getPaymentMethodProfileSecondarySort()
    {
        return $this->paymentMethodProfile->getPaymentMethodProfileSecondarySort();
    }

    /**
     * Get action name
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->_request->getParam('action');
    }

    /**
     * Get Image Place Holder
     *
     * @param string $placeHolderName
     * @return string
     */
    public function getImagePlaceHoder($placeHolderName = 'image')
    {
        return $this->imageHelper->getDefaultPlaceholderUrl($placeHolderName);
    }

    /**
     * Render Customer Payment Methods
     *
     * @param int $customerId
     * @param string $methodType
     * @return array
     */
    public function renderCustomerPaymentMethods($customerId = 0, $methodType = 'check')
    {
        /** @var  $customerPaymentMethods */
        $customerPaymentMethods = $this->getCustomerPaymentMethods($customerId);
        $customerMethods = [];

        if (count($customerPaymentMethods) > 0) {
            foreach ($customerPaymentMethods as $customerPaymentMethod) {
                if (!empty($methodType) && $customerPaymentMethod->MethodType == $methodType) {
                    $paymentMethodName = $customerPaymentMethod->MethodName;
                    $paymentMethodJson = json_decode($paymentMethodName);
                    if (is_object($paymentMethodJson)) {
                        $paymentMethodName = $paymentMethodJson->a."-".$paymentMethodJson->b;
                    }
                    $customerMethods [] = [
                        'method_id' => $customerPaymentMethod->MethodID,
                        'method_name' => $paymentMethodName
                    ];
                }
            }
        }
        return $customerMethods;
    }

    /**
     * Get Customer Payment Methods
     *
     * @param int $customerId
     * @return array|null
     */
    public function getCustomerPaymentMethods($customerId = 0)
    {
        if (!$customerId) {
            return [];
        }
        return $this->customerFactory->create()->getEbizCustomerPaymentMethods($customerId);
    }

    /**
     * Is Subscription Enabled
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isSurchargeEnabled(): bool
    {
        $storeId = $this->ebizConfig->getStore()->getId();
        return $this->customerFactory->create()->isSurchargeEnabled($storeId);
    }

}
