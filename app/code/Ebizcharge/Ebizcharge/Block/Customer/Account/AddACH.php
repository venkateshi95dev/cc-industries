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

use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\Config as ConfigModel;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Block\Address\Edit;
use Magento\Customer\Helper\Address;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface as ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use \Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Payment\Model\Config as PaymentConfigModel;
use Magento\Store\Model\ScopeInterface;

/**
 * Add ACH account
 *
 * Class AddACH
 */
class AddACH extends Edit
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
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

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
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * AddACH constructor.
     *
     * @param Context $context
     * @param Data $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param Config $configCacheType
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param CollectionFactory $countryCollectionFactory
     * @param Session $customerSession
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CurrentCustomer $currentCustomer
     * @param DataObjectHelper $dataObjectHelper
     * @param EbizchargeLogger $ebizchargeLogger
     * @param CustomerFactory $customerFactory
     * @param PaymentConfigModel $paymentconfig
     * @param ConfigModel $configModel
     * @param ResolverInterface $localeResolver
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param AddressMetadataInterface|null $addressMetadata
     * @param Address|null $addressHelper
     */
    public function __construct(
        Context                    $context,
        Data                       $directoryHelper,
        EncoderInterface           $jsonEncoder,
        Config                     $configCacheType,
        RegionCollectionFactory    $regionCollectionFactory,
        CollectionFactory          $countryCollectionFactory,
        Session                    $customerSession,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory    $addressDataFactory,
        CurrentCustomer            $currentCustomer,
        DataObjectHelper           $dataObjectHelper,
        EbizchargeLogger           $ebizchargeLogger,
        CustomerFactory            $customerFactory,
        PaymentConfigModel         $paymentconfig,
        ConfigModel                $configModel,
        ResolverInterface          $localeResolver,
        UrlInterface               $urlBuilder,
        array                      $data = [],
        AddressMetadataInterface   $addressMetadata = null,
        Address                    $addressHelper = null
    )
    {
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
            $data,
            $addressMetadata,
            $addressHelper
        );

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
        /** @var  _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $ebizchargeLogger;
    }

    /**
     * Get Customer
     *
     * @return CustomerInterface|Customer
     */
    public function getCustomer()
    {
        return $this->_customer;
    }

    /**
     * Get Customer billing Address
     *
     * @return bool|false|\Magento\Customer\Model\Address
     */
    public function getCustomerBillingAddress()
    {
        return $this->_customer->getDefaultBillingAddress();
    }

    /**
     * Get Years
     *
     * @return array
     */
    public function getYears()
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
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'ebizcharge/*/saveaction',
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
     * Get Customer Session Request Params
     *
     * @return mixed
     */
    public function getRequestParams()
    {
        return $this->_customerSession->getRequestParams();
    }

    /**
     * Get Ach Holder Name
     *
     * @return mixed|string
     */
    public function getAchHolderName()
    {

        $achHolderName = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['ach_holder'])) {
            $achHolderName = isset($requestParams['ach_holder']) ? $requestParams['ach_holder'] : '';
        }
        return $achHolderName;
    }

    /**
     * Get Ach Type
     *
     * @return mixed|string
     */
    public function getAchType()
    {
        $achType = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['ach_type'])) {
            $achType = isset($requestParams['ach_type']) ? $requestParams['ach_type'] : '';
        }
        return $achType;
    }

    /**
     * Get Ach Number
     *
     * @return mixed|string
     */
    public function getAchNumber()
    {
        $achNumber = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['ach_number'])) {
            $achNumber = isset($requestParams['ach_number']) ? $requestParams['ach_number'] : '';
        }
        return $achNumber;
    }

    /**
     * Get Ach Route Number
     *
     * @return mixed|string
     */
    public function getAchRouteNumber()
    {
        $achRouteNumber = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['ach_route'])) {
            $achRouteNumber = isset($requestParams['ach_route']) ? $requestParams['ach_route'] : '';
        }
        return $achRouteNumber;
    }

    /**
     * Get Ach Is Default
     *
     * @return mixed|string
     */
    public function getIsDefault()
    {
        $isDefault = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['is_default'])) {
            $isDefault = isset($requestParams['is_default']) ? $requestParams['is_default'] : '';
        }
        return $isDefault;
    }

    /**
     * Get First name
     *
     * @return string
     */
    public function getFirstname()
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
    public function getLasttname()
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
    public function getCompany()
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
    public function getTelephone()
    {
        $telephone = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['telephone'])) {
            $telephone = isset($requestParams['telephone']) ? $requestParams['company'] : '';
        }
        return $telephone;
    }

    /**
     * Get Street Address
     *
     * @return string
     */
    public function getStreet()
    {
        $streetAddress = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['street'])) {
            $streetAddress = isset($requestParams['street']) ? $requestParams['company'] : '';
        }
        return $streetAddress;
    }

    /**
     * Get Country Id
     *
     * @return string
     */
    public function getCountryId()
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
    public function getCity()
    {
        $city = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['city'])) {
            $city = isset($requestParams['city']) ? $requestParams['country_id'] : '';
        }
        return $city;
    }

    /**
     * Get Region Id
     *
     * @return string
     */
    public function getRegionId()
    {
        /** @var  $customerAddress */
        $customerAddress = $this->getCustomerBillingAddress();
        $regionId = $customerAddress->getRegionId();

        $requestParams = $this->getRequestParams();
        if (isset($requestParams['region_id'])) {
            $regionId = isset($requestParams['region_id']) ? $requestParams['region_id'] : '';
        }
        return $regionId;
    }

    /**
     * Get Region
     *
     * @return string
     */
    public function getRegion()
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
    public function getPostcode()
    {
        $postCode = '';
        $requestParams = $this->getRequestParams();
        if (isset($requestParams['postcode'])) {
            $postCode = isset($requestParams['postcode']) ? $requestParams['postcode'] : '';
        }
        return $postCode;
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
    public function getStoreId()
    {
        return $this->_configModel->getStore()->getId();
    }

    /**
     * @return mixed
     */
    public function prepareWebHostedPaymentMethodFormUrl()
    {
        $customerId = $this->getCustomer()->getId();
        $methodType = "ACH";
        $redirectUrl = $this->_configModel->getAchWebHostDefaultResponseUrl() . "payment_type/ach/";

        return $this->_customerFactory->create()->prepareWebHostedPaymentMethodFormUrl($customerId, $methodType, true, $redirectUrl);

    }


}
