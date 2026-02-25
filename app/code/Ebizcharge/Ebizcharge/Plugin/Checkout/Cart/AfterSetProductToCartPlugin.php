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

namespace Ebizcharge\Ebizcharge\Plugin\Checkout\Cart;


use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Shipping\Model\Shipping\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Shipping\Carrier\RateResult;
use Magento\Tax\Block\Item\Price\Renderer;


/**
 * Add Product to Cart Plugin
 */
class AfterSetProductToCartPlugin
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
        /** @var  configFactory */
        $this->configFactory = $configFactory;
        /** @var  ebizchargeLogger */
        $this->ebizchargeLogger = $ebizchargeLogger;
        /** @var  shippingConfig */
        $this->shippingConfig = $shippingConfig;
        /** @var  checkoutSession */
        $this->checkoutSession = $checkoutSession;

    }

    /**
     * After Set Product
     *
     * @param Item $subject
     * @param AbstractItem $item
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterSetProduct(Item $subject, AbstractItem $item): mixed
    {
        $buyRequest = $item->getBuyRequest() ?? null;
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if($isEbizChargeActive) {
            if ($buyRequest && $configFactory->isRecurringActive($storeId)) {
                $recurringData = $buyRequest->getData() ?? [];
                // dump($item->getBuyRequest());exit;
                if (isset($recurringData["recurring"]) && isset($recurringData["recurring"]["rec_activate"]) && (bool)($recurringData["recurring"]["rec_activate"]) === true) {

                    $recurringData["recurring"]["recurring_price"] = $item->getProduct()->getPrice() ?? 0;
                    $recurringData["recurring"]["price_incl_tax"] = $item->getProduct()->getPrice() ?? 0;
                    // $item->setRecurring(isset($recurringData["recurring"]) ? $recurringData["recurring"] : []);
                    $this->ebizchargeLogger->addCritical(__("after set " . $item->getItemId(), 100, $recurringData));
                    // $customPrice = 0.00;
                    //  $item->setCustomPrice($customPrice);
                    //  $item->setOriginalCustomPrice($customPrice);
                    // $item->getProduct()->setIsSuperMode(true);
                }
            }
        }

        return $item;
    }


    /**
     * @param Renderer $subject
     * @param $item
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterGetItem(Renderer $subject, $item)
    {
        $buyRequest = $item->getBuyRequest() ?? null;
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if($isEbizChargeActive) {
            if (is_object($buyRequest)) {
                $recurrings = $buyRequest->getData() ?? [];

                if ($configFactory->isRecurringActive($storeId)) {
                    if (isset($recurrings["recurring"]) && isset($recurrings["recurring"]["rec_activate"]) && (int)($recurrings["recurring"]["rec_activate"]) === 1) {

                        //  $productPrice = $item->getProduct()->getPrice() ?? 0;
                        // $item->setCalculation_price($productPrice);

                    }
                }
            }
        }

        return $item;
    }

    /**
     * @return mixed
     */
    protected function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

}
