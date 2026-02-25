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

namespace Ebizcharge\Ebizcharge\Plugin\Checkout\Model;


use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;


/**
 * Default config provider Plugin
 *
 * Class DefaultConfigProvider
 */
class DefaultConfigProvider extends AbstractModel
{

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param callable $proceed
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, callable $proceed): array
    {
        $result = $proceed();
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);
        $quote = $this->checkoutSession->getQuote();
        $imageData = [];
        $productImages = [];

        if ($isEbizChargeActive && $this->isRecurringEnabled($this->getStoreId()))
        {
            try {
                if ($quote && is_object($quote)) {
                    /** @var  $quoteItems */
                    $quoteItems = $quote->getAllVisibleItems() ?? [];
                    $this->recurringFactory->create()->setRecurringAndSurchargeToQuoteItem($quote);

                    /** @var $arrangedQuoteItems */
                    $arrangedQuoteItems = $this->reArrangeSubscribedCartItems($quoteItems);
                    $configQuoteImages = $result['quoteItemData'];

                    if (count($arrangedQuoteItems) > 0) {
                        foreach ($quoteItems as $imagesItem) {
                            $isRecurredItem = $this->recurringFactory->create()->isRecurredItem($imagesItem);
                            if (!$isRecurredItem) {
                                $productImages[] = $imagesItem->getItemId();
                            }
                        }
                        foreach ($quoteItems as $imagesItem) {
                            $isRecurredItem = $this->recurringFactory->create()->isRecurredItem($imagesItem);
                            if ($isRecurredItem) {
                                $productImages[] = $imagesItem->getItemId();
                            }
                        }
                        foreach ($arrangedQuoteItems as $index => $quoteItem) {
                            $quoteItemId = $quoteItem->getItemId();
                            foreach ($configQuoteImages as $configQuoteItem) {
                                if (isset($configQuoteItem["item_id"]) && $configQuoteItem["item_id"] === $quoteItemId) {
                                    $buyRequestData = $quoteItem->getBuyRequest();
                                    $quoteItemId = $quoteItem->getItemId();
                                    $recurrings = $buyRequestData->getRecurring() ?? [];
                                    $recurringData = (array)$recurrings;

                                    $recurringActivated = false;
                                    $subscribedQty = 0;
                                    $frequency = "";
                                    $startDate = "";
                                    $expiryDate = "";
                                    $productPrice = $quoteItem->getProduct()->getFinalPrice() ?? 0;
                                    $result['quoteItemData'][$index]['product'] = $quoteItem->getProduct();
                                    $result['quoteItemData'][$index]['name'] = $quoteItem->getName();
                                    $result['quoteItemData'][$index]['sku'] = $quoteItem->getSku();
                                    $result['quoteItemData'][$index]['base_price'] = $quoteItem->getBasePrice();
                                    $result['quoteItemData'][$index]['base_price_incl_tax'] = $quoteItem->getBasePriceInclTax();
                                    $result['quoteItemData'][$index]['price'] = $quoteItem->getPrice();
                                    $result['quoteItemData'][$index]['price_incl_tax'] = $quoteItem->getPriceInclTax();
                                    $result['quoteItemData'][$index]['custom_price'] = $quoteItem->getCustomPrice();
                                    $result['quoteItemData'][$index]['original_custom_price'] = $quoteItem->getOriginalCustomPrice();
                                    $result['quoteItemData'][$index]['product_price'] = $productPrice;
                                    $result['quoteItemData'][$index]['calculation_price'] = $quoteItem->getBasePriceInclTax();
                                    $result['quoteItemData'][$index]['base_row_total'] = $quoteItem->getBaseRowTotal();
                                    $result['quoteItemData'][$index]['base_row_total_incl_tax'] = $quoteItem->getBaseRowTotalInclTax();
                                    $result['quoteItemData'][$index]['row_total'] = $quoteItem->getRowTotal();
                                    $result['quoteItemData'][$index]['row_total_incl_tax'] = $quoteItem->getRowTotalInclTax();
                                    $result['quoteItemData'][$index]['row_total_with_discount'] = $quoteItem->getRowTotalWithDiscount();
                                    $result['quoteItemData'][$index]['tax_amount'] = $quoteItem->getTaxAmount();

                                    if (isset($recurringData['rec_frequency']) && !empty($recurringData['rec_frequency'])) {

                                        $recurringActivated = "Subscribed";
                                        $subscribedQty = $quoteItem->getQty() ?? 0;
                                        $frequency = isset($recurringData['rec_frequency']) ? $recurringData['rec_frequency'] : "";
                                        $startDate = isset($recurringData['sdate']) ? $recurringData['sdate'] : "";
                                        $expiryDate = isset($recurringData['edate']) ? $recurringData['edate'] : "";
                                        $recurringIndefinitely = isset($recurringData['rec_indefinitely']) ? $recurringData['rec_indefinitely'] : "0";
                                        if ($recurringIndefinitely) {
                                            $expiryDate = $this->getIndefiniteRecurringDate($startDate);
                                        }

                                        $result['quoteItemData'][$index]['name'] = $quoteItem->getName();
                                        $result['quoteItemData'][$index]['base_price'] = $productPrice;
                                        $result['quoteItemData'][$index]['base_price_incl_tax'] = $productPrice;
                                        $result['quoteItemData'][$index]['price'] = $productPrice;
                                        $result['quoteItemData'][$index]['price_incl_tax'] = $productPrice;
                                        $result['quoteItemData'][$index]['custom_price'] = $productPrice;
                                        $result['quoteItemData'][$index]['original_custom_price'] = $productPrice;
                                        $result['quoteItemData'][$index]['product_price'] = $productPrice;
                                        $result['quoteItemData'][$index]['calculation_price'] = $productPrice;

                                    }

                                    $result['quoteItemData'][$index]['recurring']['subscribed'] = $recurringActivated;
                                    $result['quoteItemData'][$index]['recurring']['frequency'] = $frequency;
                                    $result['quoteItemData'][$index]['recurring']['qty_subscribed'] = $subscribedQty;
                                    $result['quoteItemData'][$index]['recurring']['item_qty'] = $subscribedQty;
                                    $result['quoteItemData'][$index]['recurring']['sdate'] = $startDate;
                                    $result['quoteItemData'][$index]['recurring']['edate'] = $expiryDate;
                                    $result['quoteItemData'][$index]['recurring']['recurring_price'] = $productPrice;

                                }
                            }
                        }
                        if (count($arrangedQuoteItems) > 0) {
                            foreach ($quoteItems as $key => $arrangedQuoteItem) {
                                $quoteItemId = $arrangedQuoteItem->getItemId();
                                $imageData[$quoteItemId] = $result['imageData'][$productImages[$key]];
                            }
                        }
                        /** logging to logger the plugin info */
                        $this->ebizchargeLogger->addInfo(__(
                            'Params are added to quoteItemData via plugin afterGetConfig through the file ' .
                            // phpcs:ignore
                            basename(__FILE__),
                            '.php'
                        ));
                    }
                }

            } catch (Exception $ex) {
                $this->ebizchargeLogger->addCritical(__("Exception occurred during adding quote Item data Error:" . $ex->getMessage()));
            }
        }
        $result['imageData'] = $imageData;

        return $result;
    }

}
