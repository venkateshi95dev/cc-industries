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

namespace Ebizcharge\Ebizcharge\Observer\Cart\Recurring;


use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Shipping\Model\Shipping\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Shipping\Carrier\RateResult;


/**
 * Add Product to Cart Plugin
 */
class AfterSetShippingMethodToCartObserver implements ObserverInterface
{

    /**
     * @var ShippingConfig
     */
    protected ShippingConfig $shippingConfig;

    protected ConfigFactory $configFactory;

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $ebizchargeLogger;

    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;


    /**
     * @param ConfigFactory $configFactory
     * @param ShippingConfig $shippingConfig
     * @param CheckoutSession $checkoutSession
     * @param EbizchargeLogger $ebizchargeLogger
     */
    public function __construct(
        ConfigFactory    $configFactory,
        ShippingConfig   $shippingConfig,
        CheckoutSession  $checkoutSession,
        EbizchargeLogger $ebizchargeLogger

    )
    {
        /** @var $configFactory */
        $this->configFactory = $configFactory;
        /** @var $ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var $shippingConfig */
        $this->shippingConfig = $shippingConfig;
        /** @var $checkoutSession */
        $this->checkoutSession = $checkoutSession;

    }

    /**
     * Set custom price after product is added to the cart
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if ($isEbizChargeActive) {
            if ($quote instanceof Quote) {
                // Get the current shipping method
                $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            }
        }
    }

    /**
     * @return CartInterface|Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * @param $carrierCode
     * @return array
     */
    public function getCarriersByCode($carrierCode = "")
    {

        $carrierModel = null;
        $activeCarriers = $this->shippingConfig->getActiveCarriers();

        if (array_key_exists($carrierCode, $activeCarriers)) {
            $carrierModel = $activeCarriers[$carrierCode];
            return $carrierModel;
        }

        return $carrierModel;
    }


}
