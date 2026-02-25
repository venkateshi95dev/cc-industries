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
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Carrier\AbstractCarrierInterface;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Shipping\Model\Shipping\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Shipping\Carrier\RateResult;


/**
 * Add Product to Cart Plugin
 */
class AfterSetProductToCartObserver implements ObserverInterface
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
        /** @var  $checkoutSession */
        $this->checkoutSession = $checkoutSession;

    }

    /**
     * Set custom price after product is added to the cart
     *
     * @param Observer $observer
     * @return $this|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if ($isEbizChargeActive) {

            $quoteItem = $observer->getEvent()->getData('quote_item');
            $quote = $this->checkoutSession->getQuote();
            $quoteItems = $quote->getAllItems() ?? [];
            $quoteSubTotal = (float)$quote->getSubtotal() ?? 0;
            $unSubscribedItems = $this->getQuoteUnRecurredItems();

            if ($quoteItem instanceof Item) {
                $buyRequest = $quoteItem->getBuyRequest() ?? null;
                $configFactory = $this->configFactory->create();
                $storeId = $configFactory->getStore()->getStoreId();

                if ($buyRequest && $configFactory->isRecurringActive($storeId)) {

                    foreach ($quoteItems as $quoteItem) {
                        $buyRequest = $quoteItem->getBuyRequest() ?? null;
                        $recurringData = $buyRequest->getData() ?? [];
                        $productPrice = $quoteItem->getProduct()->getPrice() ?? 0;

                        if (isset($recurringData["recurring"]) && isset($recurringData["recurring"]["rec_activate"]) && (bool)($recurringData["recurring"]["rec_activate"]) === true) {

                            $recurringData["recurring"]["recurring_price"] = $quoteItem->getProduct()->getPrice() ?? 0;
                            $recurringData["recurring"]["price_incl_tax"] = $quoteItem->getProduct()->getPrice() ?? 0;
                            $quoteItem->setRecurring(isset($recurringData["recurring"]) ? $recurringData["recurring"] : []);

                            if (count($unSubscribedItems) > 0) {
                                $customPrice = 0.00;
                                $quoteItem->setCustomPrice($customPrice);
                                $quoteItem->setOriginalCustomPrice($customPrice);
                                $quoteItem->getProduct()->setIsSuperMode(true);
                            } else {
                                $quoteItem->setCustomPrice($productPrice);
                                $quoteItem->setOriginalCustomPrice($productPrice);
                                $quoteItem->getProduct()->setIsSuperMode(true);
                            }


                        }
                    }
                }
            }
        }
        return $this;

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
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteUnRecurredItems()
    {

        $unSubscribedItems = [];
        $quote = $this->checkoutSession->getQuote();

        if ($quote && is_object($quote)) {
            $quoteItems = $quote->getAllVisibleItems() ?? [];
            /** @var  $quoteItems */
            if (count($quoteItems) > 0) {
                foreach ($quoteItems as $index => $quoteItem) {
                    $buyRequestData = $quoteItem->getBuyRequest();
                    $recurringData = (array)$buyRequestData->getRecurring() ?? [];
                    if (isset($recurringData['rec_activate']) && !empty($recurringData['rec_activate']) && isset($recurringData['rec_frequency']) && !empty($recurringData['rec_frequency'])) {

                    } else {
                        $unSubscribedItems[] = $quoteItem;
                    }
                }
            }

        }
        return $unSubscribedItems;
    }

    /**
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteRecurredItems()
    {

        $subscribedItems = [];
        $quote = $this->checkoutSession->getQuote();

        if ($quote && is_object($quote)) {
            $quoteItems = $quote->getAllVisibleItems() ?? [];
            /** @var  $quoteItems */
            if (count($quoteItems) > 0) {
                foreach ($quoteItems as $index => $quoteItem) {
                    $buyRequestData = $quoteItem->getBuyRequest();
                    $recurringData = (array)$buyRequestData->getRecurring() ?? [];
                    if (isset($recurringData['rec_activate']) && !empty($recurringData['rec_activate']) && isset($recurringData['rec_frequency']) && !empty($recurringData['rec_frequency'])) {
                        $subscribedItems[] = $quoteItem;
                    }
                }
            }

        }

        return $subscribedItems;
    }

    /**
     * param $carrierCode
     * @return AbstractCarrierInterface|null
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
