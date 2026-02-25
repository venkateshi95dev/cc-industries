<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Test\Integration\ConfigurableProducts;

/**
 * Test case for reverse flows of Configurable product.
 */
class ConfigProWithUnavailableAttributeTest extends \PHPUnit\Framework\TestCase
{
    public const CHILD_SKU = "child_sku";
    public const P_SKU = "parent_sku";

    /**
     * @var mixed
     */
    public $helper;

    /**
     * @var mixed
     */
    public $product;

    /**
     * @var mixed
     */
    public $dummyData;

    /**
     * @var mixed
     */
    public $i95devServerRepo;

    /**
     * @author Debashis S. Gopal
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->helper = $objectManager->create(
            Helper::class
        );

        $this->product = $objectManager->create(
            \Magento\Catalog\Model\ProductRepository::class
        );

        $this->dummyData = $objectManager->create(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );

        $this->i95devServerRepo = $objectManager->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
    }

    /**
     * Complete sync from putting data in inbound to sync to magento
     *
     * @param  $requestData
     * @return mixed
     * @author Debashis S. Gopal
     */
    public function completeSync($requestData)
    {
        if (!isset($requestData['unavailableChild'])) {
            $attributeWithKey = [
                "attributeWithKey" => [
                    "attributeCode" => "color",
                    "attributeValue" => "RED",
                    "attributeType" => "select"
                ]
            ];
            $this->dummyData->createSingleSimpleProduct($requestData[self::CHILD_SKU], $attributeWithKey);
        }
        $path = realpath(dirname(__FILE__)) . "/Json/" . $requestData['path'];
        $productJsonData = file_get_contents($path);
        $response = $this->helper->createConfigurableProductInInboundMQ($productJsonData, $requestData[self::P_SKU]);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0]['status'],
            "Issue came in saving product to messagequeue"
        );
        $this->i95devServerRepo->syncMQtoMagento();
        return $this->helper->getInboundMqData($requestData[self::P_SKU]);
    }

    /**
     * Test case for configurable product with With Unavailable Attribute
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @magentoConfigFixture current_website configurableproducts/i95dev_enabled_settings/is_enabled 1
     * @author               Debashis S. Gopal
     */
    public function testConfigProWithUnavailableAttribute()
    {
        $requestData = [
            'path' => "ConfigProWithUnavailableAttribute.json",
            'unavailableattr' => 1,
            self::CHILD_SKU => "Red_sirt",
            self::P_SKU => "sirt"
        ];
        $this->completeSync($requestData);
    }

    /**
     * Test case for configurable product with Invalid Type
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testConfigProWithInvalidType()
    {
        $requestData = [
            'path' => "ConfigProWithInvalidType.json",
            'wrongType' => 1,
            self::CHILD_SKU => "Red_sirt",
            self::P_SKU => "sirt"
        ];
        $this->completeSync($requestData);
    }
}
