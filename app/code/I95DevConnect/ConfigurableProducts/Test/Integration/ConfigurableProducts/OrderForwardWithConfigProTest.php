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
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Create;
use I95DevConnect\MessageQueue\Model\I95DevErpMQRepository;
use I95DevConnect\MessageQueue\Model\I95DevMagMQRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test case for order with Configurable product reverse and forward flows.
 */
class OrderForwardWithConfigurableProductTest extends TestCase
{
    public const PSTR = 'parent_sku';
    public const STATUS_STR = 'status';
    public const MAGENTO_ID_STR = 'magento_id';
    public const SHIRT_STR = 'shirt';
    public const ORDER_ITEMS_STR = 'orderItems';

    /**
     * @var mixed
     */
    public $product;

    /**
     * @var mixed
     */
    public $i95devServerRepo;

    /**
     * @var mixed
     */
    public $dummyData;

    /**
     * @var mixed
     */
    public $helper;

    /**
     * @var mixed
     */
    public $productCreate;

    /**
     * @var mixed
     */
    public $erpMessageQueue;

    /**
     * @var mixed
     */
    public $stockRepo;

    /**
     * @var mixed
     */
    public $productRepo;

    /**
     * @var mixed
     */
    public $magentoMessageQueue;

    /**
     * @var int
     */
    public $productId;

    /**
     * @var int
     */
    public $orderId;

    /**
     * @author Debashis S. Gopal
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->product = $objectManager->create(
            ProductRepository::class
        );
        $this->i95devServerRepo = $objectManager->create(
            I95DevServerRepository::class
        );
        $this->dummyData = $objectManager->create(
            DummyData::class
        );

        $this->helper = $objectManager->create(
            Helper::class
        );
        $this->productCreate = $objectManager->create(
            Create::class
        );
        $this->erpMessageQueue = $objectManager->create(
            I95DevErpMQRepository::class
        );
        $this->stockRepo = $objectManager->create(
            StockRegistryInterface::class
        );
        $this->productRepo = $objectManager->create(
            Product::class
        );
        $this->magentoMessageQueue = Bootstrap::getObjectManager()->create(
            I95DevMagMQRepository::class
        );
    }

    /**
     * @param  $requestData
     * @return mixed
     */
    public function processTest($requestData)
    {
        $path = realpath(dirname(__FILE__)) . "/Json/" . $requestData['path'];
        $productJsonData = file_get_contents($path);

        $this->productId = $this->dummyData->createSingleSimpleProduct(
            $requestData['child_sku'],
            [["attributeCode" => "color", "attributeValue" => "RED", "attributeType" => "select"]]
        );

        $createResponse = $this->helper->createConfigurableProductInInboundMQ(
            $productJsonData,
            $requestData[self::PSTR]
        );
        $msg = "Issue came in saving product to messagequeue";
        $this->assertEquals(Data::PENDING, $createResponse[0][self::STATUS_STR], $msg);

        $this->i95devServerRepo->syncMQtoMagento();
        $collection = $this->helper->getInboundMqData($requestData[self::PSTR]);
        $msg = "Issue came in saving product from mq to magento";
        $this->assertEquals(Data::SUCCESS, $collection[0][self::STATUS_STR], $msg);

        $productData = $this->product->get($requestData['parent_sku']);
        $product_id = $productData->getEntityId();
        $this->assertEquals(
            $product_id,
            $collection[0][self::MAGENTO_ID_STR],
            "Issue came in saving product from mq to magento"
        );

        $this->orderId = $this->helper->createOrderInMagento($requestData);
        $outboundData = $this->getOutbountMqData($this->orderId);

        $this->assertNotNull($outboundData);
        $this->assertEquals($this->orderId, $outboundData[0][self::MAGENTO_ID_STR], "Magento id is different");
        $this->assertEquals(
            Data::PENDING,
            $outboundData[0][self::STATUS_STR],
            "Status should be PENDING in outbound message queue"
        );

        $response = $this->i95devServerRepo->serviceMethod("getOrdersInfo", '{"packetSize":30,"requestData":[]}');
        $this->assertEquals(true, $response->result, "Unable to fectch order from outbound MQ");
        $this->assertEquals($outboundData[0][self::MAGENTO_ID_STR], $response->resultData[0]['sourceId']);
        $inputData = $response->resultData[0]['InputData'];
        $this->assertNotEmpty($inputData);
        $responseData = json_decode($this->dummyData->decryptDES($inputData), 1);
        $this->assertNotEmpty($responseData);

        return $responseData;
    }

    /**
     * Test case for configurable product order forward sync
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @magentoConfigFixture current_website configurableproducts/i95dev_enabled_settings/is_enabled 1
     * @author               Hrusikesh Manna
     */
    public function testOrderForwardWithConfig()
    {
        $response = $this->processTest(
            ['child_sku' => 'Red_shirt', self::PSTR => self::SHIRT_STR, 'path' => 'ConfigProValidErpData.json']
        );
        $this->validateMagentoResponseData($response);
    }

    /**
     * Get outbound message queue collection data by magento_id
     *
     * @param  $magentoId
     * @return mixed
     * @author Debashis S. Gopal
     */
    public function getOutbountMqData($magentoId)
    {
        $collections = $this->magentoMessageQueue->getCollection()
            ->addFieldToFilter('entity_code', 'order')
            ->addFieldToFilter(self::MAGENTO_ID_STR, $magentoId);

        return $collections->getData();
    }

    /**
     * Validate fields in magento response data
     *
     * Fields: sourceId, shippingMethod, billingAddress, orderItems, payment, origin
     *
     * @param  array $responseData
     * @return void
     */
    public function validateMagentoResponseData($responseData)
    {
        $this->assertEquals($responseData['sourceId'], $this->orderId, 'Wrong sourceId in response');
        $this->assertEquals($responseData['shippingMethod'], 'flatrate_flatrate', 'Wrong shippingMethod in response');
        $this->assertNotEmpty($responseData['shippingAddress']);
        $this->assertNotEmpty($responseData['billingAddress']);

        $this->assertNotEmpty($responseData['payment']);
        $this->assertEquals($responseData['origin'], 'website', "Wrong value set for origin");
        $this->assertNotEmpty($responseData[self::ORDER_ITEMS_STR]);
        $this->assertEquals(2, count($responseData[self::ORDER_ITEMS_STR]), 'Order item count should be 2');
        foreach ($responseData[self::ORDER_ITEMS_STR] as $item) {
            if ($item['sku'] == self::SHIRT_STR) {
                $this->assertEquals($item['typeId'], 'configurable', 'typeId should be configurable');
            }
            if ($item['sku'] == 'Red_shirt') {
                $this->assertEquals($item['typeId'], 'simple', 'typeId should be simple');
                $this->assertEquals($item['parentSku'], self::SHIRT_STR, 'parent sku not setting in response');
            }
        }
    }
}
