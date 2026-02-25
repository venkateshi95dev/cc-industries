<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Helper;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use I95DevConnect\PriceLevel\Model\ItemPrice;
use I95DevConnect\PriceLevel\Helper\Data;

/**
 * Helper Class for Module
 */
class PriceLevel extends AbstractHelper
{
    /**
     * scopeConfig for system Configuration
     *
     * @var string
     */
    public $scopeConfig;

    /**
     *
     * @var \I95DevConnect\PriceLevel\Model\DataPersistence\PriceLevel
     */
    public $priceLevelCreate;

    /**
     * @var ItemPrice
     */
    public $itemPrice;

    /**
     * @var Data
     */
    public $helper;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * Class constructor to include all the dependencies
     *
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param \I95DevConnect\PriceLevel\Model\DataPersistence\PriceLevel $priceLevelCreate
     * @param ItemPrice $itemPrice
     * @param Data $helper
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        \I95DevConnect\PriceLevel\Model\DataPersistence\PriceLevel $priceLevelCreate,
        ItemPrice $itemPrice,
        Data $helper
    ) {

        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->priceLevelCreate = $priceLevelCreate;
        $this->itemPrice = $itemPrice;
        $this->helper = $helper;
    }

    /**
     * Validate if the price level exists in Magento or not if not create a new price level
     *
     * @param string $erpPriceLevel
     * @return array
     * @throws LocalizedException
     * @throws Exception
     */
    public function validatePricelevel($erpPriceLevel)
    {
        $priceLevelData = $this->priceLevelCreate->getPriceLevelData($erpPriceLevel);
        if (empty($priceLevelData)) {
            $priceLevelCreateData = ['targetId' => $erpPriceLevel, 'priceLevelDescription' => $erpPriceLevel];
            $result = $this->priceLevelCreate->create($priceLevelCreateData, '');
            if (!$result->resultData) {
                $this->logger->createLog(
                    __METHOD__,
                    "Error Occured while creating new Price Level",
                    LoggerInterface::I95EXC,
                    'critical'
                );
            } else {
                $priceLevelData = $this->priceLevelCreate->getPriceLevelData($erpPriceLevel);
            }
        }
        return $priceLevelData;
    }

    /**
     * Set Item Price
     *
     * @param [] $items
     * @param string $customerId
     * @return void
     */
    public function setItemPrice($items, $customerId)
    {
        foreach ($items as $item) {
            $qty = $item->getQty();
            $sku = $item->getSku();
            $actualFinalPrice = $item->getProduct()->getPrice();
            $product = $this->helper->productRepository->get($sku);
            $productFinalPrice = $product->getFinalPrice();
            if ($item->getProduct()->getTypeId() == 'bundle') {
                $bundleOptionPrice = 0;
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $bundleOptions = $options['bundle_options'] ?? [];
                foreach ($bundleOptions as $selectedOption) {
                    $value = $selectedOption['value'] ?? [];
                    $price = $value[0]['price'] ?? 0;
                    $bundleOptionPrice += $price;
                }
                $actualFinalPrice += $bundleOptionPrice;
                $productFinalPrice += $bundleOptionPrice;
            }

            $qty = ($qty == '') ? 1 : $qty;
            $getTirePrice = $this->itemPrice->getItemFinalPrice($product, $customerId, $qty);
            $finalPrice = min($productFinalPrice, $getTirePrice);
            $currencyCodeTo = $this->helper->storeManager->getStore()->getCurrentCurrency()->getCode();
            $currencyCodeFrom = $this->helper->storeManager->getStore()->getBaseCurrency()->getCode();
            $rate = $this->helper->priceCurrencyFactory->create()->load($currencyCodeFrom)
                ->getAnyRate($currencyCodeTo);
            $this->setFinalPrice($finalPrice, $actualFinalPrice, $rate, $item, $qty, $productFinalPrice);
        }
    }

    /**
     * Set final price
     *
     * @param mixed $finalPrice
     * @param mixed $actualFinalPrice
     * @param float $rate
     * @param mixed $item
     * @param int $qty
     * @param mixed $productFinalPrice
     * @return void
     */
    public function setFinalPrice(
        $finalPrice,
        $actualFinalPrice,
        $rate,
        $item,
        $qty,
        $productFinalPrice
    ) {
        if (!empty($finalPrice) && $actualFinalPrice >= $finalPrice) {
            $finalPrice = $finalPrice * $rate;
        } else {
            $finalPrice = $productFinalPrice * $rate;
        }
        // if ($item->getCustomPrice() != 0) {
        //     $finalPrice = min($item->getCustomPrice(), $finalPrice);
        // }
        $item->setCustomPrice($finalPrice);
        $item->setOriginalCustomPrice($finalPrice);
        $item->getProduct()->setIsSuperMode(true);
        $item->setRowTotal($finalPrice * $qty);
        $item->setBaseRowTotal($finalPrice * $qty);
    }
}
