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

namespace Ebizcharge\Ebizcharge\ViewModel;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Framework\Locale\ResolverInterface as ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Store\Model\ScopeInterface;

/**
 * AddAch customer account details
 *
 * Class AddAch
 */
class AchCard implements ArgumentInterface
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
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var SessionFactory
     */
    private SessionFactory $customerSession;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var PaymentConfig
     */
    private PaymentConfig $paymentConfig;

    /**
     * @var ResolverInterface
     */
    private ResolverInterface $localeResolver;

    /**
     * Main Class Constructor
     *
     * @param ResolverInterface $localeResolver
     * @param PaymentConfig $paymentConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param SessionFactory $customerSession
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ResolverInterface    $localeResolver,
        PaymentConfig        $paymentConfig,
        ScopeConfigInterface $scopeConfig,
        SessionFactory       $customerSession,
        UrlInterface         $urlBuilder
    ) {
        /** @var  paymentConfig */
        $this->paymentConfig = $paymentConfig;
        /** @var  scopeConfig */
        $this->scopeConfig = $scopeConfig;
        /** @var  customerSession */
        $this->customerSession = $customerSession;
        /** @var  urlBuilder */
        $this->urlBuilder = $urlBuilder;
        /** @var  localeResolver */
        $this->localeResolver = $localeResolver;
    }

    /**
     * Get save url for save action
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->urlBuilder->getUrl(
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
        return $this->scopeConfig->getValue(
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
        return $this->scopeConfig->getValue(
            static::EBIZCHARGE_CC_TYPES_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get customer billing address
     *
     * @return false|Address
     */
    public function getCustomerBillingAddress()
    {
        return $this->customerSession->create()->getCustomer()->getDefaultBillingAddress();
    }

    /**
     * Get CC types for payment
     *
     * @return array
     */
    public function getCcTypes(): array
    {
        return $this->paymentConfig->getCcTypes();
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
            $this->localeResolver->getLocale()
        )['calendar']['gregorian']['monthNames']['format']['wide'];

        foreach ($months as $key => $month) {
            $monthNum = ++$key < 10 ? '0' . $key : $key;
            $monthNames[$monthNum] = $month;
        }
        return $monthNames;
    }

    /**
     * Retrieve years list
     *
     * @return array
     */
    public function getYears(): array
    {
        return range(date('Y'), date('Y') + 10);
    }
}
