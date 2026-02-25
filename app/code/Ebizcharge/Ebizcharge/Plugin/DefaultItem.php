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

namespace Ebizcharge\Ebizcharge\Plugin;


use Closure;
use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;


/**
 * Default item Plugin class
 *
 * Class DefaultItem
 */
class DefaultItem extends AbstractModel
{

    /**
     * Around Get Items Data
     *
     * @param $subject
     * @param Closure $proceed
     * @param Item $item
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundGetItemData($subject, Closure $proceed, Item $item): array
    {
        $data = $proceed($item);
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if ($isEbizChargeActive) {
            $productData = $this->getItemSubscriptionDetails($item);
            return array_merge($data, $productData);
        }
        return $data;
    }

    /**
     * Get Items Subscription Details
     *
     * @param $_item
     * @return array|string[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getItemSubscriptionDetails($_item = null): array
    {
        $itemBuyInfo = [
            "product_subscribed" => "",
            "product_frequency" => "",
            "product_qty_subscribed" => "0",
            "product_sdate" => "",
            "product_edate" => "",
            "recurring_price" => "0.00"
        ];

        if (is_object($_item)) {

            try {
                $quote = $this->checkoutSession->getQuote();

                /** @var  $quoteItems */
                $quoteItems = $quote->getAllVisibleItems() ?? [];
                $unSubscribedItems = $this->getQuoteUnRecurredItems();

                foreach ($quoteItems as $quoteItem) {
                    $buyRequest = $quoteItem->getBuyRequest();
                    $recurringData = [];
                    if ($buyRequest) {
                        $recurringData = $buyRequest->getData();
                    }

                    $productPrice = $quoteItem->getProduct()->getPrice() ?? 0;

                    if (isset($recurringData["recurring"]) && isset($recurringData["recurring"]["rec_frequency"]) && !empty($recurringData["recurring"]["rec_frequency"])) {

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
                        $quoteItem->save();
                    }
                }
                $quote->collectTotals();

                $buyRequest = $_item->getBuyRequest();

                if ($buyRequest) {
                    $recurringData = $buyRequest->getData("recurring") ?? [];
                    $quoteItemId = $_item->getItemId() ?? 0;
                    $quoteProductId = $_item->getProductId() ?? 0;
                    $price = $_item->getProduct()->getPrice() ?? 0;
                    $itemQty = $_item->getQty() ?? 0;

                    /** if JSON Decoder Recurring Data Array */
                    if ((!empty($recurringData)) && !empty($recurringData['rec_frequency'])) {

                        $rec_frequency = isset($recurringData['rec_frequency']) ? $recurringData['rec_frequency'] : "";
                        $sdate = isset($recurringData['sdate']) ? $recurringData['sdate'] : "";

                        if (!empty($recurringData['edate'])) {
                            $edate = isset($recurringData['edate']) ? $recurringData['edate'] : "";
                        } else {
                            $edate = $this->recurringFactory->create()->prepareIndefiniteRecurringDate($sdate);
                        }
                        /** Subscription product data logging */
                        // phpcs:ignore
                        $this->ebizchargeLogger->addInfo(__('Around getting Items Data plugin called for subscriptions ' . basename(__FILE__ . '.php')));

                        $itemBuyInfo = [
                            "product_subscribed" => __('Subscribed'),
                            "product_frequency" => $rec_frequency,
                            "product_qty_subscribed" => $itemQty,
                            "product_sdate" => $sdate,
                            "product_edate" => $edate,
                            "recurring_price" => $price
                        ];
                    }
                }
            } catch (Exception $ex) {
                $this->ebizchargeLogger->addError(__('Error occurred during adding subscribed item to cart ' .
                    $ex->getMessage()));

            }
        }

        return $itemBuyInfo;

    }


}
