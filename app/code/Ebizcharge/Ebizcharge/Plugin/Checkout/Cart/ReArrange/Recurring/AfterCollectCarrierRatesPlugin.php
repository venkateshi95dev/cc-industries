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

namespace Ebizcharge\Ebizcharge\Plugin\Checkout\Cart\ReArrange\Recurring;


use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Shipping\Model\Shipping;
use Magento\Shipping\Model\Shipping\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Shipping\Carrier\RateResult;


/**
 * Add Product to Cart Plugin
 */
class AfterCollectCarrierRatesPlugin
{

    /**
     * @var ShippingConfig
     */
    protected ShippingConfig $shippingConfig;

    /**
     * @var ConfigFactory
     */
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
     * Main Constructor of the class
     *
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
     * After collect Carrier Rates
     *
     * @param Shipping $subject
     * @param $resultRates
     * @param $request
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterCollectCarrierRates(
        Shipping $subject,
                 $resultRates,
                 $request
    )
    {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if ($isEbizChargeActive) {
            $quote = $this->checkoutSession->getQuote();
            $quoteAllVisibleItems = $quote->getAllVisibleItems() ?? [];
            $totalQuoteItems = count($quoteAllVisibleItems) ?? 0;
            $isRecurringEnabled = $configFactory->isRecurringEnabled($storeId);

            if ($isRecurringEnabled) {
                $carrierResult = $resultRates->getResult();
                $carrierRates = $carrierResult->getAllRates() ?? [];
                $shippAbleItems = $this->getShippAbleItems();
                $shippingAmountInclTax = (float)$quote->getShippingAddress()->getShippingAmount() ?? 0;
                $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

                $totalRecurredItems = $shippAbleItems["recurred_items"] ?? 0;
                $totalUnRecurredItems = $shippAbleItems["un_recurred_items"] ?? 0;
                if ($totalRecurredItems && $totalRecurredItems > 0 && $carrierRates && count($carrierRates) > 0) {
                    foreach ($carrierRates as $carrierRate) {
                        $carrierCode = $carrierRate->getMethod() ?? "";
                        /** @var  $carrierModel */
                        $carrierModel = $this->getCarriersByCode($carrierCode);

                        if (is_object($carrierModel)) {
                            if ($carrierModel->getConfigData("active")) {
                                $carrierShippingPrice = (float)$carrierModel->getConfigData("price");
                                if ($totalRecurredItems > 0) {
                                    $carrierType = $carrierModel->getConfigData("type");
                                    $carrierPrice = $carrierRate->getPrice() ?? 0;
                                    $carrierCost = $carrierRate->getCost() ?? 0;
                                    $carrierRate->setCost($carrierPrice);
                                    $carrierRate->setPrice($carrierCost);

                                    if ($carrierType === "I") {
                                        $shipAbleItems = (float)$totalQuoteItems;
                                        if ($totalUnRecurredItems > 0) {
                                            $shipAbleItems = $shipAbleItems - $totalRecurredItems;
                                            $carrierShippingPrice = $shipAbleItems * $carrierShippingPrice;
                                        }
                                        $carrierRate->setCost($carrierShippingPrice);
                                        $carrierRate->setPrice($carrierShippingPrice);

                                    }
                                }
                                if (count($quoteAllVisibleItems) > 0) {
                                    foreach ($quoteAllVisibleItems as $quoteItem) {
                                        $quoteItem->setEcShippingAmount($carrierShippingPrice)
                                            ->setEcSurchargeAmount(0)
                                            ->save();
                                    }
                                }

                            }
                        }

                    }
                }
            }
        }

        return [$resultRates];
    }

    /**
     *
     * @return CartInterface|Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getShippAbleItems(): array
    {
        $shippableItems = [
            "recurred_items" => 0,
            "un_recurred_items" => 0
        ];
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStore()->getStoreId();
        $allItems = $this->getQuote()->getAllItems() ?? [];

        if (count($allItems) > 0) {
            foreach ($allItems as $item) {
                $buyRequest = $item->getBuyRequest() ?? null;
                if (is_object($buyRequest)) {
                    $recurrings = $buyRequest->getData() ?? [];
                    if ($configFactory->isRecurringActive($storeId)) {
                        if (isset($recurrings["recurring"]) &&
                            isset($recurrings["recurring"]["rec_activate"]) &&
                            (int)($recurrings["recurring"]["rec_activate"]) === 1) {
                            $shippableItems["recurred_items"]++;
                        } else {
                            $shippableItems["un_recurred_items"]++;
                        }
                    }
                }

            }
        }

        return $shippableItems;

    }

    /**
     * Get Carriers By Code
     *
     * @param mixed $carrierCode
     * @return array|null
     */
    public function getCarriersByCode(mixed $carrierCode = ""): ?array
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
