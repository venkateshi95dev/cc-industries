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
class CPCheckProductDetailsTest extends ConfigProTestCase
{
    /**
     * Test case for check variants are set properly
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @magentoConfigFixture current_website configurableproducts/i95dev_enabled_settings/is_enabled 1
     * @author               Hrusikesh Manna
     */
    public function testcheckProductDetails()
    {
        $jsonData = $this->readJsonData($this->configProValidPath);
        $this->configurableProductPrerequistiesData($this->attributeWithKey);
        $this->processTestCase($jsonData);
        $inboundData = $this->getInboundMqData($jsonData);
        $productLoadData = $this->productRepo->load($inboundData[0]['magento_id']);
        $this->assertEquals(
            0,
            $productLoadData->getPrice(),
            "Configurable product price should set to '0'"
        );
        $this->assertEquals(
            0,
            $productLoadData->getQty(),
            "Configurable product price should set to '0'"
        );
        $_childProducts = $productLoadData->getTypeInstance()->getUsedProducts($productLoadData);
        $sku = null;
        foreach ($_childProducts as $simpleProduct) {
            $sku = $simpleProduct->getSku();
        }
        $this->assertEquals(
            'Red_shirt',
            $sku,
            "Child product not set to master product"
        );
    }
}
