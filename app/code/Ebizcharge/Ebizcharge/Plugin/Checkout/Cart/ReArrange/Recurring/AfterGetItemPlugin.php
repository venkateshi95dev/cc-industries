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
class AfterGetItemPlugin
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
     * After Get Item
     *
     * @param Renderer $subject
     * @param $item
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetItem(Renderer $subject, $item)
    {
        $buyRequest = $item->getBuyRequest() ?? null;
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if($isEbizChargeActive) {
            $quote = $this->checkoutSession->getQuote();
            $quoteSubtotal = (float)$quote->getSubtotal() ?? 0;
            $quoteItems = $quote->getAllItems() ?? [];

            if (is_object($buyRequest)) {
                $recurrings = $buyRequest->getData() ?? [];

                if ($configFactory->isRecurringActive($storeId)) {
                    if (isset($recurrings["recurring"]) &&
                        isset($recurrings["recurring"]["rec_activate"]) &&
                        (int)($recurrings["recurring"]["rec_activate"]) === 1)
                    {

                        if ($quoteSubtotal >= 0) {
                            $productPrice = $item->getProduct()->getPrice() ?? 0;
                            $item->setCalculation_price($productPrice);
                        }
                    }
                }
            }
        }

        return $item;
    }


}
