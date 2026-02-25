<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Test\Integration\ConfigurableProducts;

/**
 * Test case for set response after product sync to Magento
 */
class ProductSetResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var mixed
     */
    public $dummyData;

    /**
     * @var mixed
     */
    public $i95devServerRepo;

    /**
     * @var mixed
     */
    public $erpMessageQueue;

    /**
     * @var string[][]
     */
    public $attributeWithKey = ["attributeWithKey" =>
        ["attributeCode" => "color", "attributeValue" => "RED", "attributeType" => "select"]
    ];

    /**
     * @author Hrusikesh Manna
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->dummyData = $objectManager->create(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );
        $this->erpMessageQueue = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository::class
        );
        $this->i95devServerRepo = $objectManager->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
    }

    /**
     * Prepare data for test case
     *
     * @return object
     * @author Hrusikehs Manna
     */
    public function createConfigurableProduct()
    {
        $jsonData = $this->readJsonData('/Json/ConfigProValidErpData.json');
        $productJsonData = json_decode($jsonData, true);
        $this->dummyData->createSingleSimpleProduct('Red_shirt1', $this->attributeWithKey);
        $this->i95devServerRepo->serviceMethod("createConfigurableProductList", $jsonData);
        $this->i95devServerRepo->syncMQtoMagento();
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('ref_name', $productJsonData['RequestData'][0]['Reference'])
            ->getData();
    }

    /**
     * Test product response
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @magentoConfigFixture current_website configurableproducts/i95dev_enabled_settings/is_enabled 1
     * @author               Hrusikesh Manna
     */
    public function testConfigurableProductSetResponse()
    {
        $response = $this->createConfigurableProduct();
        $Mqresult = $this->i95devServerRepo->serviceMethod(
            "getMessageQueueResponseList",
            '{"requestData":[
                    {
                        "localMessageId":66,
                        "entityId":0,
                        "messageStatus":5,
                        "syncCounter":2,
                        "schedulerId":0,
                        "messageId":' . $response[0]['msg_id'] . '
                    }
                ],
                "packetSize":50,
                "erp_name":"ERP"
            }'
        );
        $this->assertEquals(true, $Mqresult->result, "Unable to fectch product from outbound MQ");
    }

    /**
     * Read content from file
     *
     * @param  type $fileName
     * @return text
     * @author Hrusikesh Manna
     */
    public function readJsonData($fileName)
    {
        $path = realpath(dirname(__FILE__)) . $fileName;
        return file_get_contents($path);
    }
}
