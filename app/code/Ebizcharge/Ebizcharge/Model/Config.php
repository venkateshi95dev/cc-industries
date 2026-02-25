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

use Ebizcharge\Ebizcharge\Api\Data\ConfigModelInterface;
use Ebizcharge\Ebizcharge\Api\Data\PaymentInterface;
use Ebizcharge\Ebizcharge\Api\Data\SoapApiModelInterface;
use Ebizcharge\Ebizcharge\Helper\Data as EbizDataHelper;
use Ebizcharge\Ebizcharge\Model\Source\RecurringFrequencyType;
use Exception;
use Magento\Directory\Model\RegionFactory as StateCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Admin configurations model class.
 *
 * Class Config
 */
class Config implements ConfigModelInterface
{
    /**
     * @var array|array[]
     */
    public array $paymentTypeOptions;
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;
    /**
     * @var UrlInterface
     */
    protected UrlInterface $urlBuilder;
    /**
     * @var RecurringFrequencyType
     */
    protected RecurringFrequencyType $frequencyType;
    /**
     * @var CarrierFactory
     */
    protected CarrierFactory $carrierFactory;
    /**
     * @var PaymentConfig
     */
    protected PaymentConfig $paymentConfig;
    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $timezoneInterface;
    /**
     * @var FormKey
     */
    protected FormKey $formKey;
    /**
     * @var FormKeyValidator
     */
    protected FormKeyValidator $formKeyValidator;
    /**
     * @var StateCollectionFactory
     */
    protected StateCollectionFactory $stateCollectionFactory;
    /**
     * @var WriterInterface
     */
    protected WriterInterface $writerInterface;
    /**
     * @var EncryptorInterface
     */
    protected EncryptorInterface $encryptor;

    /**
     * Main Constructor of the Class
     *
     * @param ScopeConfigInterface $configInterface
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param RecurringFrequencyType $frequencyType
     * @param CarrierFactory $carrierFactory
     * @param TimezoneInterface $timezoneInterface
     * @param PaymentConfig $paymentConfig
     * @param StateCollectionFactory $stateCollectionFactory
     * @param FormKey $formKey
     * @param FormKeyValidator $formKeyValidator
     * @param WriterInterface $writerInterface
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface   $configInterface,
        StoreManagerInterface  $storeManager,
        UrlInterface           $urlBuilder,
        RecurringFrequencyType $frequencyType,
        CarrierFactory         $carrierFactory,
        TimezoneInterface      $timezoneInterface,
        PaymentConfig          $paymentConfig,
        StateCollectionFactory $stateCollectionFactory,
        FormKey                $formKey,
        FormKeyValidator       $formKeyValidator,
        WriterInterface        $writerInterface,
        EncryptorInterface     $encryptor
    )
    {
        /** @var  scopeConfigInterface * */
        $this->scopeConfig = $configInterface;

        /** @var $storeManager */
        $this->storeManager = $storeManager;
        /** @var $urlBuilder * */
        $this->urlBuilder = $urlBuilder;
        /** @var $frequencyType * */
        $this->frequencyType = $frequencyType;
        /** @var $carrierFactory * */
        $this->carrierFactory = $carrierFactory;
        /** @var $paymentConfig * */
        $this->paymentConfig = $paymentConfig;
        /** @var $timezoneInterface * */
        $this->timezoneInterface = $timezoneInterface;
        /** @var $stateCollectionFactory */
        $this->stateCollectionFactory = $stateCollectionFactory;
        /** @var $formKey */
        $this->formKey = $formKey;
        /** @var $formKeyValidator */
        $this->formKeyValidator = $formKeyValidator;
        /** @var $writerInterface */
        $this->writerInterface = $writerInterface;
        /** @var $encryptor */
        $this->encryptor = $encryptor;

        /** @var $paymentTypeOptions */
        $this->paymentTypeOptions = [
            PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_ADD_NEW_CARD => [
                'label' => __('Add new card '),
                'id' => PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_ADD_NEW_CARD,
            ],
            PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_SAVED_CARD => [
                'label' => __('Select saved card'),
                'id' => PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_SAVED_CARD,
            ],
            PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_UPDATE_CARD => [
                'label' => __('Update existing card'),
                'id' => PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_UPDATE_CARD,
            ],
            PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_ADD_NEW_ACH => [
                'label' => __('Add new bank account'),
                'id' => PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_ADD_NEW_ACH,
            ],
            PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_SAVED_ACH => [
                'label' => __('Select saved bank account'),
                'id' => PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_SAVED_ACH,
            ],
            PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_UPDATE_ACH => [
                'label' => __('Update existing bank account'),
                'id' => PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_UPDATE_ACH,
            ],
            PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_PAY_LATER => [
                'label' => __('Select pay later'),
                'id' => PaymentInterface::PAYMENT_TYPE_OPTION_SELECT_PAY_LATER,
            ],
        ];
    }

    /**
     * Get place Subscriptions Url
     *
     * @param $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getPlaceSubscriptionsUrl(array $queryParams = [])
    {
        return $this->urlBuilder->getUrl(
            'ebizcharge/checkout/placesubscriptionsaction',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     * @return array
     *
     * @throws LocalizedException
     */
    protected function prepareUrlQueryParams($reqQueryParams = [])
    {
        $urlQueryParams = [
            '_secure' => true,
            'form_key' => $this->getFormKey(),
        ];

        if (is_array($reqQueryParams) && count($reqQueryParams) > 0) {
            $urlQueryParams = array_merge_recursive($urlQueryParams, $reqQueryParams);
        }

        return $urlQueryParams;
    }

    /**
     * @return string
     *
     * @throws LocalizedException
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Get Cart URL
     *
     * @param $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getCartUrl($queryParams = [])
    {
        return $this->urlBuilder->getUrl(
            'checkout/cart/',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     * Get Success Page Url
     *
     * @param $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getSuccessPageUrl(array $queryParams = [])
    {
        return $this->urlBuilder->getUrl(
            'checkout/onepage/success',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     * Get States By Counbtry Code
     * @param $countryCode
     * @return array
     */
    public function getStatesByCountryCode(mixed $countryCode = '')
    {
        $stateList = [];
        if (empty($countryCode)) {
            $states = $this->stateCollectionFactory->create()->getCollection();
        } else {
            $states = $this->stateCollectionFactory->create()->getCollection()->addFieldToFilter('country_id', $countryCode);
        }
        if ($states->getSize() > 0) {
            foreach ($states as $state) {
                $stateList[] = [
                    'id' => $state->getId() ?? '',
                    'code' => $state->getCode() ?? '',
                    'name' => $state->getName() ?? '',
                    'country_id' => $state->getName() ?? '',
                ];
            }
        }

        return $stateList;
    }

    /**
     * get Ebizcharge Code
     *
     * @return mixed
     */
    public function getEbizchargeCode()
    {
        return PaymentInterface::CODE;
    }

    /**
     * Get State By Region Id
     *
     * @return array|mixed
     */
    public function getStateByRegionId(mixed $countryCode = '', mixed $regionId = '')
    {
        $currentState = [
            'id' => '42',
            'code' => 'NY',
            'name' => 'New York',
            'country_id' => 'US',
        ];
        $stateCollection = $this->stateCollectionFactory->create()
            ->getCollection()
            ->addFieldToFilter('country_id', $countryCode);
        if ($stateCollection->getSize() > 0) {
            foreach ($stateCollection as $state) {
                $statId = $state->getId();
                if ((int)$statId === (int)$regionId) {
                    $currentState = [
                        'id' => $state->getId() ?? '',
                        'code' => $state->getCode() ?? '',
                        'name' => $state->getName() ?? '',
                        'country_id' => $state->getName() ?? '',
                    ];
                }
            }
        }

        return $currentState;
    }

    /**
     * Get All Active Payment Methods.
     *
     * @return array
     *
     * @throws NoSuchEntityException
     */
    public function getAllActivePaymentMethods()
    {
        $store = $this->storeManager->getStore();

        return $this->paymentConfig->getActiveMethods();
    }

    /**
     * Get Store.
     *
     * @return StoreInterface
     *
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Get Active Methods.
     *
     * @return array
     */
    public function getActiveMethods()
    {
        return $this->paymentConfig->getActiveMethods();
    }

    /**
     * Is Module Active.
     *
     * @param mixed $storeCode
     * @return bool
     */
    public function isActive(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Check if IS ACH Enable / Disable.
     *
     * @param mixed $storeCode
     * @return bool
     */
    public function isAchActive(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_ACH_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    public function isSpecificCountryAllowed(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_ALLOW_SPECIFIC,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    public function getSpecificAllowedCountries(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_SPECIFIC_COUNTRY,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Check if Download Customers Enabled|Disbaled.
     *
     * @param mixed $storeCode
     */
    public function isDownlaodCustomersEnabled(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_CUSTOMERS,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param $storeCode
     * @return string
     */
    public function getRecaptchaForCheckout(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_GOOGLE_RECAPTCHA_TYPE_FOR_PLACE_ORDER,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param $storeCode
     * @return string
     */
    public function getRecaptchaForCouponCode(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_GOOGLE_RECAPTCHA_TYPE_FOR_COUPON_CODE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param $storeCode
     * @return string
     */
    public function getRecaptchaV2SiteKey(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_GOOGLE_RECAPTCHA_V2_SITE_KEY,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param $storeCode
     * @return string
     */
    public function getRecaptchaV2SiteSecret(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_GOOGLE_RECAPTCHA_V2_SITE_SECRET,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param $storeCode
     * @return string
     */
    public function getRecaptchaV3SiteSecretKey(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_GOOGLE_RECAPTCHA_V2_SITE_SECRET,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param $storeCode
     * @return bool
     */
    public function getRecaptchaV3SiteSecret(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_GOOGLE_RECAPTCHA_V3_SITE_SECRET,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Store Admin Emails.
     *
     * @return array
     *
     * @throws NoSuchEntityException
     */
    public function getStoreAdminEmails(mixed $storeCode = "0")
    {
        $store = $this->storeManager->getStore();

        return [
            'generalName' => $this->getConfig(
                'trans_email/ident_general/name',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            ),
            'generalEmail' => $this->getConfig(
                'trans_email/ident_general/email',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            ),
            'salesName' => $this->getConfig(
                'trans_email/ident_sales/name',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            ),
            'salesEmail' => $this->getConfig(
                'trans_email/ident_sales/email',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            ),
            'supportName' => $this->getConfig(
                'trans_email/ident_support/name',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            ),
            'supportEmail' => $this->getConfig(
                'trans_email/ident_support/email',
                ScopeInterface::SCOPE_STORE,
                $storeCode
            ),
        ];
    }

    /**
     * Get config.
     *
     * @param mixed $path
     * @param mixed $scope
     * @param mixed $scopeCode
     *
     * @return mixed
     */
    public function getConfig(mixed $path = "", mixed $scope = ScopeInterface::SCOPE_STORE, mixed $scopeCode = "0")
    {
        return $this->scopeConfig->getValue($path, $scope, $scopeCode);
    }

    /**
     * Check if Download Items Enabled|Disbaled.
     *
     * @param mixed $storeCode
     * @return bool
     */
    public function isDownlaodItemsEnabled(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_ITEMS,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Check if Download Invoices Enabled|Disbaled.
     *
     * @param mixed $storeCode
     */
    public function isDownlaodInvoicesEnabled(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_INVOICES,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Check if Download Items Enabled|Disbaled.
     *
     * @param mixed $storeCode
     */
    public function isDownlaodOrdersEnabled(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_ORDERS,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Check if Upload Customers Enabled|Disbaled.
     *
     * @param mixed $storeCode
     */
    public function isUploadCustomersEnabled(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_CUSTOMERS,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Check if Upload Items Enabled|Disabled.
     *
     * @param mixed $storeCode
     */
    public function isUploadItemsEnabled(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_ITEMS,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Check if Upload Items Enabled|Disabled.
     *
     * @param mixed $storeCode
     */
    public function isUplaodOrdersEnabled(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_ORDERS,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Payment Min Order Total.
     *
     * For Payment Gateway
     *
     * @param mixed $storeCode
     */
    public function getPaymentMinOrderTotal(mixed $storeCode = "0"): float
    {
        $scope = $storeCode ?? 0;

        return (float)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_MIN_ORDER_TOTAL,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Payment Max Order Total.
     *
     * @param mixed $storeCode
     */
    public function getPaymentMaxOrderTotal(mixed $storeCode = "0"): float
    {
        $scope = $storeCode ?? 0;

        return (float)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_MAX_ORDER_TOTAL,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Payment Action Authorize Only.
     *
     * @param mixed $storeCode
     *
     * @eturn string
     */
    public function isAuthorizeOnly(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_PAYMENT_ACTION,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Payment Action transactionCommandType.
     *
     * @param mixed $storeCode
     *
     * @eturn string
     */
    public function transactionCommandType(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_PAYMENT_ACTION,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get credit card payments status enable|disable.
     *
     * @param mixed $storeCode
     */
    public function isCreditCardEnabled(mixed $storeCode = "0"): bool
    {
        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_ENABLE_CARD,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * Get is Save Credit Cards.
     *
     * @param mixed $storeCode
     */
    public function getIsSaveCreditCards(mixed $storeCode = "0"): bool
    {
        return $this->saveCard($storeCode);
    }

    /**
     * Save Card.
     *
     * @param mixed $storeCode
     */
    public function saveCard(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_SAVE_CARD,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get is Save Bank Accounts.
     *
     * @param mixed $storeCode
     */
    public function getIsSaveBankAccounts(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_SAVE_BANK_ACCOUNTS,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Ebizcharge Payment Gateway Active.
     *
     * @param mixed $storeCode
     */
    public function getEbizActive(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Ebizcharge Get Envoirnment Prefix.
     *
     * @param mixed $storeCode
     */
    public function getEnvoirnmentPrefix(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_ENVOIRNMENT_PREFIX,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Ebizcharge Get Division ID.
     *
     * @param mixed $storeCode
     */
    public function getDivisionID(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_DIVISION_ID,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get payment Description.
     *
     * @param mixed $storeCode
     */
    public function getPaymentDescription(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Debug Mode.
     *
     * @param mixed $code
     *
     * @return bool
     */
    public function debugMode(mixed $code = "")
    {
        return (bool)$this->getConfig('payment/' . $code . '/debug');
    }

    /**
     * Get Request Card Code.
     *
     * @return int
     */
    public function getRequestCardCode()
    {
        return 1;
        // Removed setting from admin
        // return $this->getConfig('payment/ebizcharge_ebizcharge/request_card_code',
        // \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Request Card Code Admin.
     *
     * @param mixed $storeCode
     */
    public function getRequestCardCodeAdmin(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?: 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_CARD_CODE_ADMIN,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Customer Receipt Template.
     *
     * @param mixed $storeCode
     */
    public function getCustreceiptTemplate(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_CUSTOMER_RECEIPT_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Delete URL.
     *
     * @param int $storeCode
     */
    public function getDeleteURL(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_CARD_INLINE_ACTION,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }
    // disabled as per request of Frank
    /*public function getCustreceipt()
    {
        return $this->getConfig('payment/ebizcharge_ebizcharge/custreceipt',
     \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getCustreceiptTemplate()
    {
        return $this->getConfig('payment/ebizcharge_ebizcharge/custreceipt_template',
     \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }*/

    /**
     * Get Software Id.
     *
     * @return string
     */
    public function getSoftwareId()
    {
        return SoapApiModelInterface::EBIZCHARGE_MAGENTO_SOFTWARE;
    }

    /**
     * Show saved/update cards/bankAccounts on customer checkout.
     *
     * @param int $storeCode
     *
     * @throws LocalizedException
     */
    public function showSavedMethodsCheckout(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return Area::AREA_FRONTEND == (bool)$this->getArea()
            && $this->scopeConfig->getValue(
                ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_SHOW_SAVED_METHODS,
                ScopeInterface::SCOPE_STORE,
                $scope
            );
    }

    /**
     * Get Area.
     *
     * @return string
     *
     * @throws LocalizedException
     */
    public function getArea()
    {
        return EbizDataHelper::getAreaCode();
    }

    /**
     * Get Unit of Measure.
     *
     * @param mixed $storeCode
     *
     * @return bool
     */
    public function getUnitOfMeasure(mixed $storeCode = "0")
    {
        $scope = $storeCode ?? 0;

        return $this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_UNIT_OF_MEASURE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Base Delete URl.
     *
     * @return string
     */
    public function getBaseDeleteURL()
    {
        return $this->urlBuilder->getBaseUrl();
    }

    /**
     * Get Base URl.
     *
     * @return mixed
     *
     * @throws NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
    }

    /**
     * Get Upload Econnect Status.
     *
     * @param mixed $storeCode
     */
    public function getEconnect(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_CONNECT,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Check if is Recurring Payments are Active.
     *
     * @param mixed $storeCode
     */
    public function isRecurringActive(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_RECURRING_PAYMENT,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Check Pay Later admin side.
     *
     * @param mixed $storeCode
     */
    public function getPayLaterActive(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_PAY_LATER,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Is Recurring Enabled.
     *
     * @param mixed $storeCode
     */
    public function isRecurringEnabled(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_RECURRING_PAYMENT,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Is isAvsCvvZipEnabled.
     *
     * @param int $storeCode
     */
    public function isAvsCvvZipEnabled(mixed $storeCode = "0"): bool
    {
        return true;
        // $scope = $storeCode ?? 0;
        // return (bool)$this->scopeConfig->getValue(self::SYSTEM_CONFIG_EBIZCHARGE_ENABLE_AVS_CVV_ZIPCODE,
        // ScopeInterface::SCOPE_STORE, $scope);
    }

    /**
     * If Econnect Upload Enabled.
     *
     * @param mixed $storeCode
     */
    public function isEconnectUploadEnabled(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_UPLOAD_CONNECT,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Is Econnect download Enabled.
     *
     * @param mixed $storeCode
     */
    public function isEconnectDownlaodEnabled(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_DOWNLOAD_CONNECT,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Is Shipping Method Selected.
     *
     * @param mixed $storeCode
     */
    public function isShippingSelected(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_SHIPPING_METHOD,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get EbizConfigShippingMethod.
     *
     * @param int $storeCode
     */
    public function getEbizConfigShippingMethod(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_SHIPPING_METHOD,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Default Country.
     *
     * @param int $storeCode
     */
    public function getDefaultCountryCode(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::COUNTRY_CODE_PATH,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Default Store Name.
     *
     * @param int $storeCode
     */
    public function getDefaultStoreName(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::STORE_INFORMATION_NAME,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Default Store Phone.
     *
     * @param int $storeCode
     */
    public function getDefaultStorePhone(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::STORE_INFORMATION_PHONE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Default Store hours.
     *
     * @param int $storeCode
     */
    public function getDefaultStoreHour(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::STORE_INFORMATION_HOURS,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Default Store Country Id.
     *
     * @param int $storeCode
     */
    public function getDefaultStoreCountryId(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::STORE_INFORMATION_COUNTRY_ID,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @return mixed
     */
    public function getVoidOrderEditFlow(mixed $storeCode = "0")
    {
        return $this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_VOID_ORDER_EDIT,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * Get Default Store Region Id.
     *
     * @param int $storeCode
     */
    public function getDefaultStoreRegionId(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::STORE_INFORMATION_REGION_ID,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Default Store Postcode.
     *
     * @param int $storeCode
     */
    public function getDefaultStorePostCode(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::STORE_INFORMATION_POSTCODE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Default Store City.
     *
     * @param int $storeCode
     */
    public function getDefaultStoreCity(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::STORE_INFORMATION_CITY,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Default Store Address.
     *
     * @param int $storeCode
     */
    public function getDefaultStoreAddress(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::STORE_INFORMATION_STREET_LINE1,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Default Store Merchant Vat Number.
     *
     * @param int $storeCode
     */
    public function getDefaultStoreMerchantVatNumber(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::STORE_INFORMATION_MERCHANT_VAT_NUMBER,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Recurring Frequency Options.
     *
     * @param mixed $selectedFrequency
     */
    public function getRecurringFrequencyOptions(mixed $selectedFrequency = "")
    {
        $frequencies = $this->frequencyType->toOptionArray();
        $selectedOptions = explode(',', $this->getRecurringFrequencies());
        if (empty($selectedOptions)) {
            $selectedOptions = array_keys($frequencies);
        }
        $options = '';
        foreach ($selectedOptions as $option) {
            $optionTitle = array_key_exists($option, $frequencies) ? $frequencies[$option] : $option;
            $selected = $option == $selectedFrequency ? ' selected' : '';
            $options .= sprintf(
                '<option value="%s" %s> %s </option>',
                $option,
                $selected,
                ucwords($optionTitle)
            );
        }
        // phpcs:ignore
        echo $options;
    }

    /**
     * Get Recurring Frequencies.
     *
     * @param mixed $storeCode
     * @return string
     */
    public function getRecurringFrequencies(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_RECURRING_FREQUENCY,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get config Saved Recurring Frequencies.
     *
     * @param string $selectedFrequency
     * @param int $storeCode
     * @return string
     */
    public function getConfigSavedRecurringFrequencies(string $selectedFrequency = "", int $storeCode = 0)
    {
        $configFrequencies = explode(',', $this->getRecurringFrequencies($storeCode));
        $htmlOptions = '';
        foreach ($configFrequencies as $option) {
            $selected = $option === $selectedFrequency ? ' selected' : '';
            $htmlOptions .= sprintf(
                '<option value="%s" %s> %s </option>',
                $option,
                $selected,
                ucwords($option)
            );
        }

        return $htmlOptions;
    }

    /**
     * Get System Logs File.
     *
     * @param mixed $storeCode
     *
     * @return string
     */
    /*public function getEbizchargeLogsFile($storeCode = null): string
    {
        $scope = $storeCode ?? 0;
        return (string)$this->scopeConfig->getValue(
       ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_LOGS_FILE, ScopeInterface::SCOPE_STORE, $scope);
    }*/

    /**
     * Get Cart Page URl.
     *
     * @return string
     *
     * @throws NoSuchEntityException
     */
    public function getCartPageURL()
    {
        return $this->storeManager->getStore()->getUrl('checkout/cart');
    }

    /**
     * Indefinite Recurring.
     *
     * Check if indefinite recurring is
     * enabled for customer or not
     *
     * @param mixed $storeCode
     */
    public function indefiniteRecurring(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_INDEFINITE_RECURRING,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get System Logs Enabled Disabled.
     *
     * @param mixed $storeCode
     */
    public function getEbizchargeEnableLogs(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_ENABLE_LOGS,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get System isGatewayEmailsEnabled.
     *
     * @param mixed $storeCode
     */
    public function isGatewayEmailsEnabled(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_CUSTOMER_ENABLE_GATEWAY_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get System isGatewayMerchantEmailsEnabled.
     *
     * @param mixed $storeCode
     * @return string
     */
    public function isGatewayMerchantEmailsEnabled(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_MERCHANT_ENABLE_GATEWAY_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Merchant Email
     *
     * @param mixed $storeCode
     * @return string
     */
    public function getMerchantEmail(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::TRANSACTIONAL_MERCHANT_OWNER_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get System getEmailCustomerReceiptTemplate.
     *
     * @param mixed $storeCode
     */
    public function getEmailCustomerReceiptTemplate(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_CUSTOMER_RECEIPT_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get System getLowStockEmailTemplate.
     *
     * @param mixed $storeCode
     */
    public function getLowStockEmailTemplate(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_LOW_STOCK_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get All Shipping Methods.
     *
     * @param null|mixed $store
     *
     * @return mixed
     */
    public function getAllShippingMethods(mixed $storeCode = "0")
    {
        return $this->scopeConfig->getValue('carriers', ScopeInterface::SCOPE_STORE, $storeCode);
    }

    /**
     * Get active/enabled payment methods.
     *
     * @return array
     */
    public function getActivePaymentMethods()
    {
        return $this->paymentConfig->getActiveMethods();
    }

    /**
     * Get Available shipping Methods.
     *
     * @param mixed $storeCode
     * @return array
     */
    public function getAvailableShippingMethods(mixed $storeCode = "0")
    {
        return $this->getActiveCarriers($storeCode);
    }

    /**
     * Get Active Carriers.
     *
     * @param null|mixed $store
     *
     * @return array
     */
    public function getActiveCarriers(mixed $storeCode = "0")
    {
        $carriers = [];
        $config = $this->scopeConfig->getValue('carriers', ScopeInterface::SCOPE_STORE, $storeCode);

        foreach (array_keys($config) as $carrierCode) {
            if (
                $this->scopeConfig->isSetFlag(
                    'carriers/' . $carrierCode . '/active',
                    ScopeInterface::SCOPE_STORE,
                    $storeCode
                )
            ) {
                $carrierModel = $this->carrierFactory->create($carrierCode, $storeCode);
                if ($carrierModel) {
                    $carriers[$carrierCode] = $carrierModel;
                }
            }
        }

        return $carriers;
    }

    /**
     * Algorithm Salt.
     *
     * @return string
     */
    public function algorithmSalt()
    {
        return '||xl' . rand(9, 8) . 't#' . rand(9, 9) . '%!}&';
    }

    /**
     * Is EBizCharge Module Active.
     *
     * @param mixed $storeCode
     * @return bool
     */
    public function isEbizchargeActive(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;
        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get EBizCharge Source API Key
     *
     * @param mixed $storeCode
     * @return string
     */
    public function getSourceKey(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;
        $configValue = "";
        try {
            $configValue = str_replace(
                ConfigModelInterface::SYSTEM_EBIZCHARGE_ENCRYPT_KEY_POSTFIX,
                "",
                $this->encryptor->decrypt((string)$this->scopeConfig->getValue(
                    ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_API_KEY,
                    ScopeInterface::SCOPE_STORE,
                    $scope
                ))
            );
        } catch (Exception $exception) {
            $configValue = "";
        }
        return $configValue;
    }

    /**
     * Get EBizCharge API ID.
     *
     * @param mixed $storeCode
     * @return string
     */
    public function getSourceId(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;
        $configValue = "";
        try {
            $configValue = str_replace(
                ConfigModelInterface::SYSTEM_EBIZCHARGE_ENCRYPT_KEY_POSTFIX,
                "",
                $this->encryptor->decrypt((string)$this->scopeConfig->getValue(
                    ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_API_ID,
                    ScopeInterface::SCOPE_STORE,
                    $scope
                ))
            );
        } catch (Exception $exception) {
            $configValue = "";
        }
        return $configValue;
    }

    /**
     * Get EBizCharge API PIN.
     *
     * @param mixed $storeCode
     * @return string
     */
    public function getSourcePin(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;
        $configValue = "";
        try {
            $configValue = str_replace(
                ConfigModelInterface::SYSTEM_EBIZCHARGE_ENCRYPT_KEY_POSTFIX,
                "",
                $this->encryptor->decrypt((string)$this->scopeConfig->getValue(
                    ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_API_PASSWORD,
                    ScopeInterface::SCOPE_STORE,
                    $scope
                ))
            );
        } catch (Exception $exception) {
            $configValue = "";
        }
        return $configValue;
    }

    /**
     * Encrypt String With Post Fix
     *
     * @param string $keyParam
     * @return string
     */
    public function addPostFix(string $keyParam = ""): string
    {
        if (!$this->hasPostFix($keyParam)) {
            $keyParam = $keyParam . ConfigModelInterface::SYSTEM_EBIZCHARGE_ENCRYPT_KEY_POSTFIX;
        }
        return $keyParam;
    }

    /**
     * Has Post fix
     *
     * @param string $keyParam
     * @return bool
     */
    public function hasPostFix(string $keyParam = ""): bool
    {
        return str_contains($keyParam, ConfigModelInterface::SYSTEM_EBIZCHARGE_ENCRYPT_KEY_POSTFIX);
    }

    /**
     *  Decrypt With Posted Fix
     *
     * @param string $keyParam
     * @return string
     */
    public function decryptWithPostFix(string $keyParam = ""): string
    {
        try {
            $keyParam = $this->encryptor->decrypt($keyParam);
            if ($this->hasPostFix($keyParam)) {
                $keyParam = str_replace(
                    ConfigModelInterface::SYSTEM_EBIZCHARGE_ENCRYPT_KEY_POSTFIX,
                    "",
                    $keyParam
                );
            }
        } catch (Exception $exception) {
            $keyParam = "";
        }
        return $keyParam;
    }

    /**
     * Is Post Fix exists
     *
     * @param string $keyParam
     * @return bool
     */
    public function isPostFixExits(string $keyParam = ""): bool
    {
        $isPostFixExists = false;
        try {
            $keyParam = $this->encryptor->decrypt($keyParam);
            if ($this->hasPostFix($keyParam)) {
                $isPostFixExists = true;
            }
        } catch (Exception $exception) {
            $isPostFixExists = false;
        }
        return $isPostFixExists;
    }

    /**
     * Get PCI Compliance Enabled.
     *
     * @return bool
     */
    public function getPciComplianceEnabled(mixed $storeCode = "0")
    {
        return (bool)(int)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_PCI_COMPLIANCE_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * Get PCI Compliance Action URl.
     *
     * @return mixed
     */
    public function getPciComplianceActionUrl(mixed $storeCode = "0")
    {
        return ConfigModelInterface::SYSTEM_CONFIG_DEFAULT_EBIZCHARGE_PCI_COMPLIANCE_ACTION_GATEWAY_URL;
        // return $this->scopeConfig->getValue(
        // self::SYSTEM_CONFIG_EBIZCHARGE_PCI_COMPLIANCE_ACTION_GATEWAY_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Pci Place Order Url.
     *
     * @return string
     *
     * @throws NoSuchEntityException
     */
    public function getPciPlaceOrderUrl()
    {
        return $this->getBaseUrl() . '/ebizcharge/pci/pciplaceorderaction/';
    }

    /**
     * Get Config Payment Action.
     *
     * @param mixed $storeCode
     */
    public function getPaymentAction(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_PAYMENT_ACTION,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Config Payment Form Type.
     *
     * @param mixed $storeCode
     */
    public function getPaymentFormType(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_PAYMENT_FORM_TYPE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Config Is Cards Tokenize Only
     *
     * @param mixed $storeCode
     * @return string
     */
    public function isCardsTokenizeOnly(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;
        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_TOKENIZE_CARDS_ONLY,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Is Save Guest Users
     *
     * @param mixed $storeCode
     * @return string
     */
    public function isSaveGuestUsers(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;
        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_SAVE_GUEST_USERS,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Current Date Time.
     *
     * @return string
     */
    public function getCurrentDateTime()
    {
        return $this->timezoneInterface->date()->format('Y-m-d');
    }

    /**
     * Get Current Time.
     *
     * @return string
     */
    public function getCurrentTime()
    {
        return $this->timezoneInterface->date()->format('H:i:s');
    }

    /**
     * Get Card Listing Url.
     *
     * @return string
     *
     * @throws NoSuchEntityException
     */
    public function getCardListingUrl()
    {
        return $this->getBaseUrl() . '/ebizcharge/cards/listaction/';
    }

    /**
     * Get Add Update Payment Method Url.
     *
     * @return string
     *
     * @throws NoSuchEntityException
     */
    public function getAddUpdatePaymentMethodUrl()
    {
        return $this->getBaseUrl() . 'ebizcharge/cards/addupdatepaymentmethodaction/';
    }

    /**
     * Get Payment Save Payment.
     *
     * @param mixed $storeCode
     */
    public function getPaymentSavePayment(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;
        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_SAVE_PAYMENT,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Is Same billing and Shipping Addresses.
     *
     * @param mixed $storeCode
     */
    public function isSameBillingAndShippingAddresses(mixed $storeCode = "0"): bool
    {
        $scope = $storeCode ?? 0;

        return (bool)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_IS_SAME_BILLING_SHIPPING_ADDRESSES,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Ajax Place Pci Order Url.
     *
     * @return string
     *
     * @throws NoSuchEntityException
     */
    public function getAjaxPlacePciOrderUrl()
    {
        return $this->getBaseUrl() . '/ebizcharge/checkout/pciplaceorderaction';
    }

    /**
     * Get Ajax Place Pci Order Url.
     *
     * @return string
     *
     * @throws NoSuchEntityException
     */
    public function getAjaxAddTransactionsUrl()
    {
        return $this->getBaseUrl() . '/ebizcharge/checkout/pciaddtransactionsaction';
    }

    /**
     * Redirect At Success Page.
     *
     * @return string
     *
     * @throws NoSuchEntityException
     */
    public function redirectSuccessPageUrl()
    {
        return $this->getBaseUrl() . 'checkout/onepage/success';
    }

    /**
     * Get Years.
     *
     * @return array
     */
    public function getYears()
    {
        return $this->paymentConfig->getYears();
    }

    /**
     * Get Months.
     *
     * @return array
     */
    public function getMonths()
    {
        return $this->paymentConfig->getMonths();
    }

    /**
     * Get Groups.
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->paymentConfig->getGroups();
    }

    /**
     * Get Payment Method Name.
     *
     * @param mixed $ccType
     *
     * @return mixed|string
     */
    public function getPaymentMethodName(mixed $ccType = '')
    {
        $ccTypes = $this->getCcTypes();
        $ccType = substr(strtoupper($ccType), 0, 1);
        $cardType = '';

        if (count($ccTypes) > 0) {
            foreach ($ccTypes as $ccKey => $type) {
                $ccKey = substr(strtoupper($ccKey), 0, 1);
                if ($ccKey === $ccType) {
                    $cardType = $type;
                }
            }
        }

        return $cardType;
    }

    /**
     * Get Cc Types.
     *
     * @return array
     */
    public function getCcTypes()
    {
        return $this->paymentConfig->getCcTypes();
    }

    /**
     * Get Transaction Response Type.
     *
     * @param null|mixed $arrayKey
     *
     * @return Phrase
     */
    public function getTransactionResponseType($arrayKey = null)
    {
        // phpcs:disable
        $responseType = [
            'A' => __(self::TRANSACTION_RESPONSE_TYPE_ACCEPTED),
            'E' => __(self::TRANSACTION_RESPONSE_TYPE_ERROR),
            'D' => __(self::TRANSACTION_RESPONSE_TYPE_DECLINED),
            '' => __(self::TRANSACTION_RESPONSE_TYPE_REJECTED),
        ];
        $type = __(self::TRANSACTION_RESPONSE_TYPE_REJECTED);
        // phpcs:enable

        if (null !== $arrayKey) {
            $type = isset($responseType[$arrayKey]) ? $responseType[$arrayKey] : $type;
        }

        return $type;
    }

    /**
     * Get Card type.
     *
     * @param null|mixed $cardKey
     *
     * @return mixed|string
     *
     * @throws NoSuchEntityException
     */
    public function getCardType(mixed $cardKey = "")
    {
        $storeId = $this->getStoreId();
        $cardTypes = $this->getSelectedPaymentCardTypes($storeId);
        // phpcs:ignore
        $cardType = __(self::NOT_AVAILABLE);

        if ("" !== $cardKey) {
            $type = isset($cardTypes[$cardKey]) ? $cardTypes[$cardKey] : $cardType;
        }

        return $type;
    }

    /**
     * Get Store Id.
     *
     * @return int
     *
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * Get Selected Payment Card Types.
     *
     * @param mixed $storeCode
     *
     * @throws NoSuchEntityException
     */
    public function getSelectedPaymentCardTypes(mixed $storeCode = "0"): array
    {
        $storeCode = 0 === $storeCode ? $this->getStoreId() : $storeCode;
        $selectedCardTypes = [];

        $allowedCreditCardTypes = $this->getCardTypes();
        $selectedCcTypes = explode(',', $this->getPaymentCctypes($storeCode));

        if (count($allowedCreditCardTypes) > 0) {
            foreach ($allowedCreditCardTypes as $cardKey => $allowedCreditCardType) {
                if (in_array($cardKey, $selectedCcTypes)) {
                    $cardKey = substr($cardKey, 0, 1);
                    if ('D' === $cardKey) {
                        $cardKey = $cardKey . 'S';
                    }
                    $selectedCardTypes[$cardKey] = $allowedCreditCardType;
                }
            }
        }

        return $selectedCardTypes;
    }

    /**
     * Get Card Types.
     *
     * @return array
     */
    public function getCardTypes()
    {
        $cardTypes = $this->paymentConfig->getCcTypes();

        $finalAllowedCardTypes = [];
        if (count($cardTypes) > 0) {
            foreach ($cardTypes as $cardKey => $cardType) {
                if (in_array($cardKey, $this->getAllowedCardTypes())) {
                    $finalAllowedCardTypes[$cardKey] = $cardType;
                }
            }
        }

        return $finalAllowedCardTypes;
    }

    /**
     * Get Allowed Card Types.
     *
     * @return string[]
     */
    public function getAllowedCardTypes()
    {
        return [
            ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_MAGE_PREFIX,
            ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_MAGE_PREFIX,
            ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_MAGE_PREFIX,
            ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_MAGE_PREFIX,
            ConfigModelInterface::CREDIT_CARD_TYPE_JCB_PREFIX,
            ConfigModelInterface::CREDIT_CARD_TYPE_OTHER_PREFIX,
        ];
    }

    /**
     * Get Credit card types.
     *
     * @param mixed $storeCode
     */
    public function getPaymentCctypes(mixed $storeCode = "0"): string
    {
        $scope = $storeCode ?? 0;

        return (string)$this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_CCTYPES,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get Short Cc Type.
     *
     * @param null|mixed $cardType
     */
    public function getShortCcType(mixed $cardType = ""): string
    {
        $selectedCardType = '';

        switch ($cardType) {
            case ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_SHORT_PREFIX;

                break;

            case ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_SHORT_PREFIX;

                break;

            case ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_SHORT_PREFIX;

                break;

            case ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_SHORT_PREFIX;

                break;

            case ConfigModelInterface::CREDIT_CARD_TYPE_BANK_OF_AMERICA_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_BANK_OF_AMERICA_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_BANK_OF_AMERICA_SHORT_PREFIX;

                break;

            default:
                $selectedCardType = $cardType;
        }

        return $selectedCardType;
    }

    /**
     * Get Short Cc Type 2.
     *
     * @param null|mixed $cardType
     */
    public function getLongCcType(mixed $cardType = ""): string
    {
        $selectedCardType = '';

        switch ($cardType) {
            case ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_VISA_CARD_MAGE_PREFIX;

                break;

            case ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_AMERICAN_EXPRESS_MAGE_PREFIX;

                break;

            case ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_MASTER_CARD_MAGE_PREFIX;

                break;

            case ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_DISCOVER_CARD_MAGE_PREFIX;

                break;

            case ConfigModelInterface::CREDIT_CARD_TYPE_BANK_OF_AMERICA_MAGE_PREFIX:
            case ConfigModelInterface::CREDIT_CARD_TYPE_BANK_OF_AMERICA_SHORT_PREFIX:
                $selectedCardType = ConfigModelInterface::CREDIT_CARD_TYPE_BANK_OF_AMERICA_MAGE_PREFIX;

                break;

            default:
                $selectedCardType = $cardType ?? '';
        }

        return $selectedCardType;
    }

    /**
     * Get Css Cards Icons.
     *
     * @return array
     */
    public function getCssCardsIcons()
    {
        return [
            'american' => 'american-express-card-icon',
            'citi' => 'citi-card-icon',
            'discover' => 'discover-card-icon',
            'master' => 'master-card-icon',
            'america' => 'bank-of-america-card-icon',
            'credit' => 'credit-card-icon',
            'bank' => 'bank-account-card-icon',
            'checking' => 'bank-account-card-icon',
            'savings' => 'bank-account-card-icon',
            'saving' => 'bank-account-card-icon',
            'jcb' => 'jcb-card-icon',
            'visa' => 'visa-card-icon',
        ];
    }

    /**
     * Get Store Manager.
     *
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Get Card AVS Validation URL.
     *
     * @throws LocalizedException
     */
    public function getCardAVSValidationURL(array $queryParams = []): string
    {
        return $this->urlBuilder->getUrl(
            'ebizcharge/cards/validatecvvavscards',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     * Get new order status configuration value.
     *
     * @param mixed $scopeCode
     * @return mixed
     */
    public function getNewOrderStatus(mixed $scopeCode = "0"): mixed
    {
        return $this->scopeConfig->getValue(
            ConfigModelInterface::SYSTEM_CONFIG_EBIZCHARGE_NEW_ORDER_STATUS,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get Calculate Surcharge Ajax URL.
     *
     * @param array $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getCalculateSurchargeAjaxUrl(array $queryParams = []): string
    {
        return $this->urlBuilder->getUrl(
            'ebizcharge/checkout/calculatesurcharge',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     * Get Surcharge Session Data Ajax URL.
     *
     * @param array $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getSurchargeSessionDataUrl(array $queryParams = []): string
    {
        return $this->urlBuilder->getUrl(
            'ebizcharge/checkout/surchargeSessionData',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     * Get Save 3D secure Data URL.
     *
     * @param array $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getSave3DSecureDataUrl(array $queryParams = []): string
    {
        return $this->urlBuilder->getUrl(
            'ebizcharge/checkout/save3DSecureData',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     * Get Weight Unit.
     *
     * @param null|mixed $storeCode
     *
     * @return mixed
     */
    public function getWeightUnit(mixed $storeCode = "0")
    {
        return $this->scopeConfig->getValue(
            'general/locale/weight_unit',
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * Get Store Currency.
     *
     * @param null|mixed $storeCode
     *
     * @return mixed
     */
    public function getStoreCurrency(mixed $storeCode = "0")
    {
        return $this->scopeConfig->getValue(
            'currency/options/base',
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * Validate Json String.
     *
     * @return false|mixed
     */
    public function validateJsonString(string $string = ''): mixed
    {
        $result = json_decode($string, true);

        return JSON_ERROR_NONE != json_last_error() ? false : $result;
    }

    /**
     *  Get Checkout Web Hosted Form URL
     *
     * @param array $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getCheckoutWebHostedFormUrl(array $queryParams = []): string
    {
        return $this->urlBuilder->getUrl(
            'ebizcharge/checkout/renderwebhostedformurl',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     * Get Cards WEbHosted Default Response URL
     *
     * @param array $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getCardsWebHostDefaultResponseUrl(array $queryParams = []): string
    {
        return $this->urlBuilder->getUrl(
            'ebizcharge/cards/cardswebhostedformresponse',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     * Get ACH Web Hosted Default Response URL
     *
     * @param array $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getAchWebHostDefaultResponseUrl(array $queryParams = []): string
    {
        return $this->urlBuilder->getUrl(
            'ebizcharge/ach/achwebhostedformresponse',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     * Get Cards Payment Methods Listing Url
     *
     * @param array $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getCardsPaymentMethodsListingUrl(array $queryParams = []): string
    {
        return $this->urlBuilder->getUrl(
            'ebizcharge/cards/listaction/',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     *  Get ACH Payment Methods Listing URL
     *
     * @param array $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getAchPaymentmethodsListingUrl(array $queryParams = []): string
    {
        return $this->urlBuilder->getUrl(
            'ebizcharge/ach/listaction/',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     *
     * Get Checkout Web Hosted Approved URl
     *
     * @param array $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getCheckoutWebHostedApprovedUrl(array $queryParams = []): string
    {
        $formKey = $this->formKey->getFormKey();

        return $this->urlBuilder->getUrl(
            'ebizcharge/checkout/renderwebhostedformresponse',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     * Get Checkout Web Hosted Error Url
     *
     * @param array $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getCheckoutWebHostedErrorUrl(array $queryParams = []): string
    {
        $formKey = $this->formKey->getFormKey();

        return $this->urlBuilder->getUrl(
            'ebizcharge/checkout/renderwebhostedformresponse',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     *
     * Get Checkout Web Hosted Declined URL
     *
     * @param array $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getCheckoutWebHostedDeclinedUrl(array $queryParams = []): string
    {
        $formKey = $this->formKey->getFormKey();

        return $this->urlBuilder->getUrl(
            'ebizcharge/checkout/renderwebhostedformresponse',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     *Get Payment WEb Form URl
     *
     * @param mixed $storeCode
     * @param array $queryParams
     * @return string
     * @throws LocalizedException
     */
    public function getPaymentWebFormUrl(mixed $storeCode = "0", array $queryParams = [])
    {
        return $this->urlBuilder->getUrl(
            'ebizcharge/checkout/renderwebhostedformresponse',
            $this->prepareUrlQueryParams($queryParams)
        );
    }

    /**
     * @return bool
     */
    public function validateFormKey($request)
    {
        return $this->formKeyValidator->validate($request);
    }

    /**
     * Check if admin side or front.
     *
     * @throws LocalizedException
     */
    public function isBackend(): bool
    {
        return Area::AREA_FRONTEND != EbizDataHelper::getAreaCode();
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getLogoUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'logo.png';
    }

    /**
     * Get Item Type.
     *
     * @param mixed $itemType
     *
     * @return string
     */
    public function getItemTypeMap(mixed $itemType = "")
    {
        switch ($itemType) {
            case 'simple':
                $itemTypeFinal = 'simple';

                break;

            case 'virtual':
                $itemTypeFinal = 'virtual';

                break;

            case 'downloadable':
                $itemTypeFinal = 'downloadable';

                break;

            case 'configurable':
                $itemTypeFinal = 'configurable';

                break;

            case 'grouped':
                $itemTypeFinal = 'grouped';

                break;

            case 'bundle':
                $itemTypeFinal = 'bundle';

                break;

            default:
                $itemTypeFinal = 'simple';
        }

        return $itemTypeFinal;
    }
}
