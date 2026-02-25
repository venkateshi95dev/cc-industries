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
class CPConfigProWithValidDataTest extends ConfigProTestCase
{
    /**
     * Test case for test validation
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website configurableproducts/i95dev_enabled_settings/is_enabled 1
     * @author               Hrusikesh Manna
     */
    public function testValidation()
    {
        $jsonData = $this->readJsonData('/Json/ConfigProInvalidErpData.json');
        $arrayData = [];
        $errorId = [];
        foreach (json_decode($jsonData, true) as $data) {
            foreach ($data as $datas) {
                $arrayData[self::REQUEST_DATA_STR][] = $datas;
                $this->createRecord(json_encode($arrayData));
                $this->i95devServerRepo->syncMQtoMagento();
                $inboundMqData = $this->getInboundMqData($jsonData);

                $errorId[] = $inboundMqData[0]['error_id'];
            }
        }
        $this->assertEquals(3, count($errorId), "Issue Came In Data Validation");
    }
}
