<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Test\Integration\ConfigurableProducts;

use I95DevConnect\MessageQueue\Helper\Data;

/**
 * Test case for reverse flows of Configurable product.
 */
class ConfigurableProductMultiDimensionalReverseTest extends ConfigProTestCase
{
    public const ATTRIBUTE_CODE_STR = "attributeCode";
    public const ATTRIBUTE_VALUE_STR = "attributeValue";
    public const ATTRIBUTE_TYPE_STR = "attributeType";
    public const SELECT_STR = "select";

    /**
     * @var string[][]
     */
    public $attributeWithKeyMtd = [
        "attributeWithKey" => [
            self::ATTRIBUTE_CODE_STR => "color",
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
    ];

    /**
     * Prepare data for test case
     *
     * @param  $attribute
     * @return mixed
     * @author Hrusikesh Manna
     */
    public function configurableProductPrerequistiesData($attribute)
    {
        return $this->dummyData->createSingleSimpleProduct('Red_shirt-RED-FREE-XL-NEW', $attribute);
    }

    /**
     * Test case for create multi dimensional product
     *
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @magentoConfigFixture current_website configurableproducts/i95dev_enabled_settings/is_enabled 1
     * @magentoDbIsolation   enabled
     * @author               Hrusikesh Manna
     */
    public function testMultidimentionalProductCreate()
    {
        $jsonData = $this->readJsonData('/Json/MultiDimentionalProduct.json');
        $productId = $this->configurableProductPrerequistiesData($this->attributeWithKeyMtd);
        $productData = $this->productRepo->load($productId);
        $data = [
            $productData->getAttributeText('color'),
            $productData->getAttributeText('style'),
            $productData->getAttributeText('size'),
            $productData->getAttributeText('condition')
        ];

        $this->assertEquals(
            count($data),
            4, // NOSONAR
            "Issue came to set  attribute to child product"
        );

        $this->processTestCase($jsonData);
        $inboundData = $this->getInboundMqData($jsonData);
        $this->assertEquals(
            Data::SUCCESS,
            $inboundData[0]['status'],
            "Issue came in saving configurable product From Message Queue to Magento "
        );
    }
}
