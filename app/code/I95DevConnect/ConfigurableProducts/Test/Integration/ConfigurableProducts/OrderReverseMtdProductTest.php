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
class OrderReverseMtdProductTest extends OrderReverseTest
{
    public const PSTR = 'parent_sku';
    public const STATUS_STR = 'status';
    public const ATTRIBUTE_CODE_STR = "attributeCode";
    public const ATTRIBUTE_VALUE_STR = "attributeValue";
    public const ATTRIBUTE_TYPE_STR = "attributeType";
    public const SELECT_STR = "select";
    public const PRODUCT_STR = "Red_shirt-RED-FREE-XL-NEW";

    /**
     * Test case for MTD product order reverse sync
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @magentoConfigFixture current_website configurableproducts/i95dev_enabled_settings/is_enabled 1
     * @author               Debashis S. Gopal
     */
    public function testOrderReverseWithMtdProduct() // NOSONAR
    {
        $requestData = [
            'ordertestreverse' => 1,
            'path' => "MultiDimentionalProduct.json",
            "child_sku" => self::PRODUCT_STR,
            self::PSTR => "shirt"
        ];
        $productData = [
            "targetId" => self::PRODUCT_STR,
            "reference" => self::PRODUCT_STR,
            "sku" => self::PRODUCT_STR,
            "dateCreated" => "2019-07-02T00:00:00",
            "name" => self::PRODUCT_STR,
            "description" => self::PRODUCT_STR,
            "shortDescription" => self::PRODUCT_STR,
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
                [self::ATTRIBUTE_CODE_STR => "color",
                    self::ATTRIBUTE_VALUE_STR => "RED",
                    self::ATTRIBUTE_TYPE_STR => self::SELECT_STR],
                [self::ATTRIBUTE_CODE_STR => "style",
                    self::ATTRIBUTE_VALUE_STR => "FREE",
                    self::ATTRIBUTE_TYPE_STR => self::SELECT_STR],
                [self::ATTRIBUTE_CODE_STR => "size",
                    self::ATTRIBUTE_VALUE_STR => "XL",
                    self::ATTRIBUTE_TYPE_STR => self::SELECT_STR],
                [self::ATTRIBUTE_CODE_STR => "condition",
                    self::ATTRIBUTE_VALUE_STR => "NEW",
                    self::ATTRIBUTE_TYPE_STR => self::SELECT_STR]
            ]
        ];
        $this->completeSync($requestData, $productData);
    }
}
