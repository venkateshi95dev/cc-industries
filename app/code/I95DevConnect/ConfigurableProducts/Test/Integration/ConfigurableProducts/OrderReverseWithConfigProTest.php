<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Test\Integration\ConfigurableProducts;

/**
 * Test case for order with Configurable product reverse and forward flows.
 */
class OrderReverseWithConfigProTest extends OrderReverseTest
{
    public const PSTR = 'parent_sku';
    public const RED_SHIRT_STR = 'Red_shirt';
    public const STATUS_STR = 'status';

    /**
     * Test case or configurable product order reverse sync
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @magentoConfigFixture current_website configurableproducts/i95dev_enabled_settings/is_enabled 1
     * @author               Debashis S. Gopal
     */
    public function testOrderReverseWithConfigurableProduct() // NOSONAR
    {
        $requestData = [
            'ordertestreverse' => 1,
            'path' => "ConfigProValidErpData.json",
            "child_sku" => self::RED_SHIRT_STR,
            self::PSTR => "shirt"
        ];
        $productData = [
            "targetId" => self::RED_SHIRT_STR,
            "reference" => self::RED_SHIRT_STR,
            "sku" => self::RED_SHIRT_STR,
            "dateCreated" => "2019-07-02T00:00:00",
            "name" => self::RED_SHIRT_STR,
            "description" => self::RED_SHIRT_STR,
            "shortDescription" => self::RED_SHIRT_STR,
            "weight" => "30.2",
            "price" => "26",
            "unitOfMeasure" => "PCS",
            "isVisible" => 1,
            self::STATUS_STR => 1,
            "updatedTime" => "2019-07-02T08:42:53",
            "parentSku" => "sirt",
            "retailVariantId" => "color",
            "qty" => 0,
            "attributeWithKey" => [
                ["attributeCode" => "color", "attributeValue" => "RED", "attributeType" => "select"]
            ]
        ];
        $this->completeSync($requestData, $productData);
    }
}
