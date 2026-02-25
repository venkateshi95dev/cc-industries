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

namespace Ebizcharge\Ebizcharge\Block\Customer\Account;

use Ebizcharge\Ebizcharge\Model\Config as ConfigModel;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Block\Address\Edit;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Cache\Type\Config as CacheConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface as ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config as PaymentConfigModel;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Response\RedirectInterface;

/**
 * Add Credit Card Account
 *
 * Class AddCard
 */
class AddCard extends Edit
{
    /**
     * Const Display All Region Config Path
     *
     * @const DISPLAY_ALL_REGION_CONFIG_PATH
     */
    public const DISPLAY_ALL_REGION_CONFIG_PATH = 'general/region/display_all';

    /**
     * Ebizcharge CC Types Config Path
     *
     * @const EBIZCHARGE_CC_TYPES_CONFIG_PATH
     */
    public const EBIZCHARGE_CC_TYPES_CONFIG_PATH = 'payment/ebizcharge_ebizcharge/cctypes';

    /**
     * @var TranApi
     */
    protected TranApi $_tran;

    /**
     * @var PaymentConfigModel
     */
    protected PaymentConfigModel $_paymentconfig;

    /**
     * @var Customer
     */
    protected Customer $_customer;

    /**
     * @var ConfigModel
     */
    protected ConfigModel $_configModel;

    /**
     * @var ResolverInterface
     */
    protected ResolverInterface $_localeResolver;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $_requestInterface;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;
    /**
     * @var RedirectInterface
     */
    protected RedirectInterface $_redirectInterface;

    /**
     * @param Context $context
     * @param Data $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param CacheConfig $configCacheType
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param CollectionFactory $countryCollectionFactory
     * @param Session $customerSession
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CurrentCustomer $currentCustomer
     * @param DataObjectHelper $dataObjectHelper
     * @param PaymentConfigModel $paymentconfig
     * @param ConfigModel $configModel
     * @param RequestInterface $requestInterface
     * @param ResolverInterface $localeResolver
     * @param UrlInterface $urlBuilder
     * @param CustomerFactory $customerFactory
     * @param RedirectInterface $redirectInterface
     * @param TranApi $TranApi
     * @param array $data
     */
    public function __construct(
        Context                    $context,
        Data                       $directoryHelper,
        EncoderInterface           $jsonEncoder,
        CacheConfig                $configCacheType,
        RegionCollectionFactory    $regionCollectionFactory,
        CollectionFactory          $countryCollectionFactory,
        Session                    $customerSession,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory    $addressDataFactory,
        CurrentCustomer            $currentCustomer,
        DataObjectHelper           $dataObjectHelper,
        PaymentConfigModel         $paymentconfig,
        ConfigModel                $configModel,
        RequestInterface           $requestInterface,
        ResolverInterface          $localeResolver,
        UrlInterface               $urlBuilder,
        CustomerFactory            $customerFactory,
        RedirectInterface          $redirectInterface,
        TranApi                    $TranApi,
        array                      $data = []
    ) {
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $customerSession,
            $addressRepository,
            $addressDataFactory,
            $currentCustomer,
            $dataObjectHelper,
            $data
        );

        /** @var _tran */
        $this->_tran = $TranApi;
        /** @var _paymentconfig */
        $this->_paymentconfig = $paymentconfig;
        /** @var _customerSession */
        $this->_customerSession = $customerSession;
        /** @var _customer */
        $this->_customer = $customerSession->getCustomer();
        /** @var _configModel */
        $this->_configModel = $configModel;
        /** @var  _localeResolver */
        $this->_localeResolver = $localeResolver;
        /** @var  urlBuilder */
        $this->_urlBuilder = $urlBuilder;
        /** @var  _requestInterface */
        $this->_requestInterface = $requestInterface;
        /** @var  _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var  _redirectInterface */
        $this->_redirectInterface = $redirectInterface;
    }

    /**
     * Get Config
     *
     * @param string $path
     * @return mixed|string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get CC Types
     *
     * @return array
     */
    public function getCcTypes(): array
    {
        $storeId = $this->_configModel->getStoreId();
        return $this->_configModel->getSelectedPaymentCardTypes($storeId);
    }

    /**
     * Get Params
     *
     * @return array
     */
    public function getParams(): array
    {
        return $this->_requestInterface->getParams();
    }

    /**
     * Get ebiz customer Id
     *
     * @return array|mixed|null
     */
    public function getEbizCustomerId()
    {
        $customerId = $this->_customerSession->getCustomerId();
        return $this->_customerFactory->create()->load($customerId)->getEcCustId();
    }

    /**
     * Get Customer Email
     *
     * @return string
     */
    public function getCustomerEmail(): string
    {
        return $this->getCustomer()->getEmail();
    }

    /**
     * Get Customer
     *
     * @return \Ebizcharge\Ebizcharge\Model\Customer|CustomerInterface
     */
    public function getCustomer()
    {
        $customerId = $this->_customer->getId();
        return $this->_customerFactory->create()->load($customerId);
    }

    /**
     * Get Param
     *
     * @param null|string $key
     * @param null|mixed $default
     * @return mixed
     */
    public function getParam(string $key = null, $default = null)
    {
        return $this->_requestInterface->getParam($key, $default);
    }

    /**
     * Get Years
     *
     * @return array
     */
    public function getYears(): array
    {
        return range(date('Y'), date('Y') + 10);
    }

    /**
     * Retrieve list of months
     *
     * @return array
     */
    public function getMonths(): array
    {
        $monthNames = [];

        $months = (new DataBundle())->get(
            $this->_localeResolver->getLocale()
        )['calendar']['gregorian']['monthNames']['format']['wide'];

        foreach ($months as $key => $month) {
            $monthNum = ++$key < 10 ? '0' . $key : $key;
            $monthNames[$monthNum] = $month;
        }
        return $monthNames;
    }

    /**
     * Get save url for save action
     *
     * @return string
     */
    public function getSaveUrl(): string
    {
        if ($this->_configModel->getIsSaveCreditCards()) {
            return $this->getValidatePaymentMethodUrl();
        } else {
            return $this->_urlBuilder->getUrl(
                'ebizcharge/*/saveaction',
                ['_secure' => true]
            );
        }
    }

    /**
     * Get save url for save action
     *
     * @return string
     */
    public function getValidatePaymentMethodUrl(): string
    {
        return $this->_urlBuilder->getUrl(
            'ebizcharge/*/validatecvvavscards',
            ['_secure' => true]
        );
    }

    /**
     * Whether to display all regions or not
     *
     * @return mixed
     */
    public function displayAllRegion()
    {
        return $this->_scopeConfig->getValue(
            static::DISPLAY_ALL_REGION_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Ebizcharge payment CC types
     *
     * @return mixed
     */
    public function getEbizCcTypes()
    {
        return $this->_scopeConfig->getValue(
            static::EBIZCHARGE_CC_TYPES_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get ccHoderName
     *
     * @return string
     */
    public function getCcHolderName(): string
    {
        $ccHolderName = '';
        $requestParams = $this->getRequestParams();

        if (isset($requestParams['payment'])) {
            $paymentRequestParams = $requestParams['payment'];
            $ccHolderName = $paymentRequestParams['cc_holder'] ?? '';
        }
        return $ccHolderName;
    }

    /**
     * Get Customer Session Request Params
     *
     * @return mixed
     */
    public function getRequestParams()
    {
        $refferalUrl = $this->_redirectInterface->getRefererUrl();
        if (str_contains($refferalUrl, "listaction")) {
            $this->_customerSession->unsRequestParams();
        }
        return $this->_customerSession->getRequestParams();
    }

    /**
     * Get CC Number
     *
     * @return string
     */
    public function getCcNumber(): string
    {
        $ccNumber = '';
        $requestParams = $this->getRequestParams();

        if (isset($requestParams['payment'])) {
            $paymentRequestParams = $requestParams['payment'];
            $ccNumber = $paymentRequestParams['cc_number'] ?? '';
        }
        return $ccNumber;
    }

    /**
     * Get CC Number
     *
     * @return string
     */
    public function getCcType(): string
    {
        $ccType = '';
        $requestParams = $this->getRequestParams();

        if (isset($requestParams['payment'])) {
            $paymentRequestParams = $requestParams['payment'];
            $ccType = isset($paymentRequestParams['cc_type']) ? $paymentRequestParams['cc_type'] : '';
        }

        return $ccType;
    }

    /**
     * Get Is Default
     *
     * @return string
     */
    public function getIsDefault(): string
    {
        $isDefault = '';
        $requestParams = $this->getRequestParams();

        if (isset($requestParams['payment'])) {
            $paymentRequestParams = $requestParams['payment'];
            $isDefault = isset($paymentRequestParams['default']) ? $paymentRequestParams['default'] : 0;
        }
        return (string)$isDefault;
    }

    /**
     * Get Firstname
     *
     * @return string
     */
    public function getFirstname(): string
    {
        $firstName = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['firstname'])) {
            $firstName = isset($requestParams['firstname']) ? $requestParams['firstname'] : '';
        }
        return $firstName;
    }

    /**
     * Get Last Name
     *
     * @return string
     */
    public function getLasttname(): string
    {
        $lastName = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['lastname'])) {
            $lastName = isset($requestParams['lastname']) ? $requestParams['lastname'] : '';
        }
        return $lastName;
    }

    /**
     * Get Company
     *
     * @return string
     */
    public function getCompany(): string
    {
        $companyName = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['company'])) {
            $companyName = isset($requestParams['company']) ? $requestParams['company'] : '';
        }
        return $companyName;
    }

    /**
     * Get Telephone
     *
     * @return string
     */
    public function getTelephone(): string
    {
        $telephone = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['telephone'])) {
            $telephone = isset($requestParams['telephone']) ? $requestParams['telephone'] : '';
        }
        return $telephone;
    }

    /**
     * Get Street Address
     *
     * @return string
     */
    public function getStreet(): string
    {
        $streetAddress = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['street'])) {
            $streetAddress = isset($requestParams['street']) ? $requestParams['street'] : '';
        }
        return $streetAddress;
    }

    /**
     * Get Country Id
     *
     * @return string
     */
    public function getCountryId(): string
    {
        $countryId = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['country_id'])) {
            $countryId = isset($requestParams['country_id']) ? $requestParams['country_id'] : '';
        }
        return $countryId;
    }

    /**
     * Get City
     *
     * @return string
     */
    public function getCity(): string
    {
        $city = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['city'])) {
            $city = isset($requestParams['city']) ? $requestParams['city'] : '';
        }
        return $city;
    }

    /**
     * Get Region Id
     *
     * @return string
     */
    public function getRegionId(): string
    {
        /** @var  $customerAddress */
        $customerAddress = $this->getCustomerBillingAddress();

        $regionId = $customerAddress->getRegionId();
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['region_id'])) {
            $regionId = isset($requestParams['region_id']) ? $requestParams['region_id'] : '';
        }
        return (string)$regionId;
    }

    /**
     * Get Customer billing Address
     *
     * @return bool|false|Address
     */
    public function getCustomerBillingAddress()
    {
        return $this->_customer->getDefaultBillingAddress();
    }

    /**
     * Get Region
     *
     * @return string
     */
    public function getRegion(): string
    {
        $region = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['region'])) {
            $region = isset($requestParams['region']) ? $requestParams['region'] : '';
        }
        return $region;
    }

    /**
     * Get postCode
     *
     * @return string
     */
    public function getPostcode(): string
    {
        $postCode = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['postcode'])) {
            $postCode = isset($requestParams['postcode']) ? $requestParams['postcode'] : '';
        }
        return $postCode;
    }

    /**
     * Get Expiry Month
     *
     * @return string
     */
    public function getExpiryMonth(): string
    {
        $expMonth = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['payment']['cc_exp_month'])) {
            $expMonth = $requestParams['payment']['cc_exp_month'] ?? '';
        }
        return $expMonth;
    }

    /**
     * Get Expiry Month
     *
     * @return string
     */
    public function getExpiryYear(): string
    {
        $expYear = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['payment']['cc_exp_year'])) {
            $expYear = isset($requestParams['payment']['cc_exp_year']) ? $requestParams['payment']['cc_exp_year'] : '';
        }
        return $expYear;
    }

    /**
     * Get is Credit Card Active
     *
     * @return bool
     */
    public function isCreditCardActive(): bool
    {
        $storeId = $this->getStore()->getId();
        return $this->_configModel->isCreditCardEnabled($storeId);
    }

    /**
     * Get Store
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore(): StoreInterface
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Is Save Cards Allowed
     *
     * @return bool
     */
    public function isSaveCardsAllowed(): bool
    {
        $storeId = $this->getStore()->getId();
        return $this->_configModel->getIsSaveCreditCards($storeId);
    }



    /**
     * @param $storeId
     * @return mixed
     */
    public function getPaymentWebFormUrl($storeId = 0)
    {
        return $this->_configModel->getPaymentWebFormUrl($storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getPaymentFormType($storeId = 0)
    {
        return $this->_configModel->getPaymentFormType($storeId);
    }

    /**
     * @return mixed
     */
    public function prepareWebHostedPaymentMethodFormUrl()
    {
        $customerId = $this->getCustomer()->getId();
        $methodType = "CC";
        $redirectUrl = $this->_configModel->getCardsWebHostDefaultResponseUrl() . "payment_type/cc/";
        return $this->_customerFactory->create()->prepareWebHostedPaymentMethodFormUrl($customerId, $methodType, true, $redirectUrl);

    }

}
