<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Plugin\Pricing\Price;

use I95DevConnect\PriceLevel\Helper\Data;
use I95DevConnect\PriceLevel\Model\ItemPrice;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Pricing\SaleableInterface;

/**
 * MinimalTierPriceCalculator shows minimal value of Tier Prices.
 */
class MinimalTierPriceCalculator
{
    /**
     *
     * @var ItemPrice
     */
    public $i95PriceList;

    /**
     * @var Data
     */
    public $priceLevelHelper;

    /**
     *
     * @var Magento\Customer\Model\SessionFactory
     */
    public $customerSessionFactory;

    /**
     * Class constructor to include all the dependencies
     *
     * @param ItemPrice $i95PriceList
     * @param Data $priceLevelHelper
     * @param SessionFactory $customerSessionFactory
     */
    public function __construct(
        ItemPrice $i95PriceList,
        Data $priceLevelHelper,
        SessionFactory $customerSessionFactory
    ) {
        $this->i95PriceList = $i95PriceList;
        $this->priceLevelHelper = $priceLevelHelper;
        $this->customerSessionFactory = $customerSessionFactory;
    }

    /**
     * Get raw value of "as low as" as a minimal among tier prices.
     *
     * @param \Magento\Catalog\Pricing\Price\MinimalTierPriceCalculator $subject
     * @param object $result
     * @param SaleableInterface $saleableItem
     *
     * @return float|null
     */
    public function afterGetValue(
        \Magento\Catalog\Pricing\Price\MinimalTierPriceCalculator $subject,//NOSONAR
        $result,
        SaleableInterface $saleableItem
    ) {
        $isEnabled = $this->priceLevelHelper->isEnabled();
        if ($isEnabled) {
            $customerId = $this->customerSessionFactory->create()->getId();
            $parentSkus = $this->priceLevelHelper->getParentIdsByChildSku($saleableItem->getSku());
            $oneQtyPrice = $this->i95PriceList->getItemFinalPrice($saleableItem, $customerId, 1);
            if (!empty($parentSkus) && $oneQtyPrice == 0) {
                $parentSku = isset($parentSkus[0]) ? $parentSkus[0] : '';
                $tierPriceList = $this->i95PriceList->getItemPriceListDisplay(
                    $customerId,
                    $parentSku
                );
            } else {
                $tierPriceList = $this->i95PriceList->getItemPriceListDisplay(
                    $customerId,
                    $saleableItem->getSku()
                );
            }
            $tierPrices = [];
            foreach ($tierPriceList as $tierPrice) {
                $price = $this->priceLevelHelper->convertPrice($tierPrice['price']);
                $tierPrices[] = $price;
            }
            return $tierPrices ? min($tierPrices) : null;
        } else {
            return $result;
        }
    }
}
