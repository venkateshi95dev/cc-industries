<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Product;

/**
 * Product test case for forward flows
 */
class ProductForwardTest extends \PHPUnit\Framework\TestCase
{
    public const MAGENTO_ID = "magento_id";
    public const ITSTEST = 'ITSTEST';

    /**
     * @author Debashis S. Gopal
     */
    protected function setUp(): void
    {
        $this->product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\Data\ProductInterfaceFactory::class
        );
        $this->productRepo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepositoryFactory::class
        );
        $this->i95devServerRepo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->magentoMessageQueue = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\MessageQueue\Model\I95DevMagMQRepository::class
        );
        $this->dummyData = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );
        $this->stockItem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory::class
        );
        $this->productExtension = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory::class
        );
    }

    /**
     * Create a simple product in magento.
     *
     * @author Debashis S. Gopal
     * @return int
     */
    public function createProduct()
    {
        $product = $this->product->create();
        $product->setSku(self::ITSTEST)
            ->setStatus(1)
            ->setAttributeSetId(4)
            ->setVisibility(1)
            ->setName(self::ITSTEST)
            ->setCustomAttribute("description", "Test Item for Integration Testing")
            ->setCustomAttribute("short_description", 'Test Item')
            ->setCost(48)
            ->setPrice(50)
            ->setTypeId("simple")
            ->setWeight(.54)
            ->setCustomAttribute("update_by", "Magento")
            ->setCustomAttribute("targetproductstatus", 'Sync in process');
        $result = $this->productRepo->create()->save($product);
        return $result->getId();
    }

    /**
     * Add packorders to the product.
     *
     * @param  int $productId
     * @return void
     * @author Debashis S. Gopal
     */
    public function addBackorders($productId)
    {
        $product = $this->productRepo->create()->getById($productId);
        $stockItem = $this->stockItem->create();
        $stockItem->setBackorders(1);
        $productExtension = $this->productExtension->create();
        $productExtension->setStockItem($stockItem);
        $product->setExtensionAttributes($productExtension);
        $this->productRepo->create()->save($product);
    }

    /**
     * @param  $EntityId
     * @param  $entityName
     * @param  $methodName
     * @return mixed
     */
    public function getEntityInfoData($EntityId, $entityName, $methodName)
    {
        $outboundData = $this->getOutbountMqData($EntityId);
        $this->assertNotNull($outboundData);
        $this->assertEquals($EntityId, $outboundData[0]['magento_id'], "Magento id is different");
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $outboundData[0]['status'],
            "Status should be PENDING in outbound message queue"
        );
        $response = $this->i95devServerRepo->serviceMethod(
            $methodName,
            '{"requestData":[],"packetSize":50,"erp_name":"ERP"}'
        );
        $this->assertEquals(true, $response->result, "Unable to fectch " . $entityName . " from outbound MQ");
        $this->assertEquals($outboundData[0]['magento_id'], $response->resultData[0]['sourceId']);
        $inputData = $response->resultData[0]['InputData'];
        $this->assertNotEmpty($inputData);
        $responseData = json_decode($this->dummyData->decryptDES($inputData), 1);
        $this->assertNotEmpty($responseData);

        return $responseData;
    }

    /**
     * Get outbound message queue collection data by magento_id
     *
     * @param $magentoId
     *
     * @return array
     * @author Debashis S. Gopal
     */
    public function getOutbountMqData($magentoId)
    {
        $collections = $this->magentoMessageQueue->getCollection()
            ->addFieldToFilter('entity_code', 'Product')
            ->addFieldToFilter(self::MAGENTO_ID, $magentoId);
        return $collections->getData();
    }

    /**
     * @authod Debashis S. Gopal
     *
     * Calls getProductsInfo service, and do all required assertion
     *
     * @param  int $productId
     * @return array
     */
    public function getProductsInfo($productId)
    {
        $responseData = $this->getEntityInfoData($productId, "product", "getProductsInfo");

        $this->assertEquals($responseData['sku'], self::ITSTEST, 'Wrong sku in response');
        $this->assertEquals($responseData['cost'], 48, 'Wrong cost in response');
        return $responseData;
    }

    /**
     * Test case for testGetProductInfo
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testGetProductInfo()
    {
        $productId = $this->createProduct();
        $this->getProductsInfo($productId);
    }
    /**
     * Test case for testGetProductInfo with baskorders enable
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testGetProductInfoWithBackorders()
    {
        $productId = $this->createProduct();
        $this->addBackorders($productId);
        $responseData = $this->getProductsInfo($productId);
        $this->assertEquals(1, $responseData['backorders'], 'backorders not set in response');
    }
}
