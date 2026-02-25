<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_TierPrice
 */

namespace I95DevConnect\TierPrice\Plugin\Ui\DataProvider\Product\Form\Modifier;

use I95DevConnect\MessageQueue\Helper\Data;

/**
 * Product Data Provider class used for tier price
 */
class ProductDataProvider
{
    public const CHILDREN = "children";
    public const ADVANCED_PRICING_MODEL = 'advanced_pricing_modal';
    public const ADVANCED_PRICING = 'advanced-pricing';
    public const TIER_PRICE = 'tier_price';
    public const RECORD = 'record';
    public const ARGUMENTS = 'arguments';
    public const CONFIG = "config";
    public const DISABLED = "disabled";

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * ProductDataProvider constructor.
     *
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * To get Meta Data
     *
     * @param     \Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider $subject
     * @param     array                                                             $result
     * @return    int
     * @author    i95Dev Team
     * @updatedBy Subhan. Added componentType and formElement to fields
     */
    public function afterGetMeta(
        \Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider $subject, // NOSONAR
        $result
    ) {
        // @codingStandardsIgnoreStart
        if ($this->dataHelper->isEnabled()
        && isset($result[self::ADVANCED_PRICING_MODEL])
        && isset($result[self::ADVANCED_PRICING_MODEL][self::CHILDREN][self::ADVANCED_PRICING][self::CHILDREN][self::TIER_PRICE][self::ARGUMENTS]['data'][self::CONFIG])
        ) {
            $metaTierPrice = $result[self::ADVANCED_PRICING_MODEL][self::CHILDREN]
            [self::ADVANCED_PRICING][self::CHILDREN][self::TIER_PRICE];
            $metaTierPrice[self::ARGUMENTS]['data'][self::CONFIG][self::DISABLED] = 1;
            $metaTierPriceChild = $metaTierPrice[self::CHILDREN][self::RECORD][self::CHILDREN];
            $metaTierPriceChild['price_qty'][self::ARGUMENTS]['data'][self::CONFIG][self::DISABLED] = 1;
            $metaTierPriceChild['website_id'][self::ARGUMENTS]['data'][self::CONFIG][self::DISABLED] = 1;
            $metaTierPriceChild['price'][self::ARGUMENTS]['data'][self::CONFIG][self::DISABLED] = 1;
            $metaTierPriceChild['actionDelete'][self::ARGUMENTS]['data'][self::CONFIG][self::DISABLED] = 1;
            $metaTierPriceChild['cust_group'][self::ARGUMENTS]['data'][self::CONFIG][self::DISABLED] = 1;
            $metaTierPrice[self::CHILDREN][self::RECORD][self::CHILDREN] = $metaTierPriceChild;
            $result[self::ADVANCED_PRICING_MODEL][self::CHILDREN]
            [self::ADVANCED_PRICING][self::CHILDREN][self::TIER_PRICE] = $metaTierPrice;
            return $result;
        } else {
            return $result;
        }
        // @codingStandardsIgnoreEnd
    }
}
