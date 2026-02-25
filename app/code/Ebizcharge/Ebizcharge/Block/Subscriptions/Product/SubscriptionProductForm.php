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

namespace Ebizcharge\Ebizcharge\Block\Subscriptions\Product;

use Ebizcharge\Ebizcharge\Model\ConfigFactory as EbizConfigFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface as UrlEncoderInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Subscription Product Form block class
 *
 * Class SubscriptionProductForm
 */
class SubscriptionProductForm extends View
{
    /**
     * @var EbizConfigFactory
     */
    protected EbizConfigFactory $_configModelFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $_checkoutSession;


    /**
     * @param Context $context
     * @param UrlEncoderInterface $urlEncoder
     * @param EncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param CustomerSession $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param EbizConfigFactory $configFactory
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context                    $context,
        UrlEncoderInterface        $urlEncoder,
        EncoderInterface           $jsonEncoder,
        StringUtils                $string,
        Product                    $productHelper,
        ConfigInterface            $productTypeConfig,
        FormatInterface            $localeFormat,
        Session                    $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface     $priceCurrency,
        EbizConfigFactory          $configFactory,
        CheckoutSession                        $checkoutSession,
        StoreManagerInterface                   $storeManager,
        array                                    $data = []
    )
    {
        parent::__construct($context, $urlEncoder, $jsonEncoder, $string, $productHelper, $productTypeConfig, $localeFormat, $customerSession, $productRepository, $priceCurrency, $data);

        /** @var  _configModelFactory */
        $this->_configModelFactory = $configFactory;
        /** @var  _storeManager */
        $this->_storeManager = $storeManager;
        /** @var  _checkoutSession */
        $this->_checkoutSession = $checkoutSession;

    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function showRecurringForm(): bool
    {
        return $this->isEbizchargeActive() && $this->isRecurringEnabled() && $this->getLoggedInCustomerId();
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isEbizChargeActive(): bool
    {
        return $this->_configModelFactory->create()->isActive($this->getStoreId());
    }

    /**
     * Get Store Id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * Get Store Manager Interface
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isRecurringEnabled(): bool
    {
        $isEbizchargeActive = $this->isEbizChargeActive();
        $isRecurringEnabled = $this->_configModelFactory->create()->isRecurringEnabled($this->getStoreId());
        return $isEbizchargeActive && $isRecurringEnabled;
    }

    /**
     * @return int
     */
    public function getLoggedInCustomerId():int
    {
        return (int)$this->getCustomerId();
    }

    /**
     * @param $productId
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getCartRecurringInfo($productId): array
    {
        $infoBuyRequest = $this->getInfoBuyRequest($productId);
        return isset($infoBuyRequest["recurring"]) ? (array)$infoBuyRequest["recurring"] : [];
    }

    /**
     * @param $productId
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getInfoBuyRequest($productId): array
    {
        $infoBuyRequest = [];
        $cartItems = $this->getCartItems();
        if (count($cartItems) > 0) {
            foreach ($cartItems as $item) {
                if ($productId == $item->getProductId()) {
                    $infoBuyRequest = (array)$item->getBuyRequest()->getData();
                }
            }
        }
        return $infoBuyRequest;

    }

    /**
     * @return array|CartItemInterface[]|mixed
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getCartItems(): mixed
    {
        return $this->getQuote()->getItems() ?? [];
    }

    /**
     * @return CartInterface|Quote
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getQuote(): CartInterface|Quote
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isEndDateRecurringEnabled()
    {
        $storeId = $this->_configModelFactory->create()->getStoreId();
        return $this->_configModelFactory->create()->indefiniteRecurring($storeId);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function indefiniteRecurring(): bool
    {
        return (bool)$this->_configModelFactory->create()->indefiniteRecurring($this->getStoreId());
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getRecurringIndefiniteVisibility()
    {
        return $this->indefiniteRecurring() ? 'display: none' : '';
    }

    /**
     * Update Recurring in DB
     *
     * @return bool
     */
    public function updateRecurringInDb(): bool
    {
        $cartItemId = $this->getCartItemId();
        $cartProductId = $this->getCartProductId();
        $updateRecurrings = false;
        if ($cartItemId && $cartProductId) {
            $updateRecurrings = true;
        }
        return (bool)$updateRecurrings;
    }

    /**
     * Get Cart Item Id
     *
     * @return mixed
     */
    public function getCartItemId(): mixed
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * Get Cart Product Id
     *
     * @return mixed
     */
    public function getCartProductId(): mixed
    {
        return $this->getRequest()->getParam('product_id');
    }

    /**
     * Get Configured Frequencies
     *
     * @param null|mixed $selectedFrequency
     * @param int $storeCode
     * @return string
     */
    public function getConfiguredFrequencies($selectedFrequency = null, $storeCode = 0): string
    {
        return (string)$this->_configModelFactory->create()->getConfigSavedRecurringFrequencies($selectedFrequency, $storeCode);
    }
}
