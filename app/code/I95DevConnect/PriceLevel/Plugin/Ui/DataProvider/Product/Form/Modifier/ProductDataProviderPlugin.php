<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Plugin\Ui\DataProvider\Product\Form\Modifier;

use I95DevConnect\PriceLevel\Helper\Data;
use Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider;

/**
 * Class for Product Advance Pricing
 */
class ProductDataProviderPlugin
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
    public $helper;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * After get meta plugin
     *
     * @param ProductDataProvider $subject
     * @param object $result
     * @return mixed
     */
    public function afterGetMeta(ProductDataProvider $subject, $result) // NOSONAR
    {
        if ($this->helper->isEnabled() && isset($result[self::ADVANCED_PRICING_MODEL]) &&
            isset($result[self::ADVANCED_PRICING_MODEL][self::CHILDREN][self::ADVANCED_PRICING]
                            [self::CHILDREN][self::TIER_PRICE][self::ARGUMENTS]['data'][self::CONFIG])
        ) {
            $metaTierPrice = $result[self::ADVANCED_PRICING_MODEL][self::CHILDREN]
            [self::ADVANCED_PRICING][self::CHILDREN][self::TIER_PRICE];
            $metaTierPrice[self::ARGUMENTS]['data'][self::CONFIG][self::DISABLED] = 1;
            $metaTierPriceChild = $metaTierPrice[self::CHILDREN][self::RECORD][self::CHILDREN];
            $metaTierPriceChild['website_id'][self::ARGUMENTS]['data'][self::CONFIG][self::DISABLED] = 1;
            $metaTierPriceChild['cust_group'][self::ARGUMENTS]['data'][self::CONFIG][self::DISABLED] = 1;
            $metaTierPriceChild['price_qty'][self::ARGUMENTS]['data'][self::CONFIG][self::DISABLED] = 1;
            $metaTierPriceChild['price'][self::ARGUMENTS]['data'][self::CONFIG][self::DISABLED] = 1;
            $metaTierPriceChild['actionDelete'][self::ARGUMENTS]['data'][self::CONFIG][self::DISABLED] = 1;
            $metaTierPrice[self::CHILDREN][self::RECORD][self::CHILDREN] = $metaTierPriceChild;
            $result[self::ADVANCED_PRICING_MODEL][self::CHILDREN]
            [self::ADVANCED_PRICING][self::CHILDREN][self::TIER_PRICE] = $metaTierPrice;
            return $result;
        } else {
            return $result;
        }
    }
}
