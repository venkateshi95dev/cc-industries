<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Test\Integration\ConfigurableProducts;

use I95DevConnect\I95DevServer\Model\I95DevServerRepository;
use I95DevConnect\I95DevServer\Test\Integration\DummyData;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\I95DevErpMQRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test case for reverse flows of Configurable product.
 */
class ConfigurableProductReverseTest extends ConfigProTestCase
{
    /**
     * Test case for check module is enabled or disabled
     *
     * @author               Hrusikesh Manna
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website configurableproducts/i95dev_enabled_settings/is_enabled 0
     */
    public function testModuleStatus()
    {
        $jsonData = $this->readJsonData($this->configProValidPath);
        $this->configurableProductPrerequistiesData($this->attributeWithKey);
        $this->processTestCase($jsonData);
        $inboundData = $this->getInboundMqData($jsonData);
        $errorMessage = $this->dummyData->readErrorMsg($inboundData[0]['error_id']);
        $this->assertEquals(
            'configurable_pro_01',
            $errorMessage,
            "Issue came with validate module status"
        );
    }
}
