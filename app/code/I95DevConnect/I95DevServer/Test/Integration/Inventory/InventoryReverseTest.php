<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Inventory;

/**
 * Test case for inventory reverse sync
 */
class InventoryReverseTest extends \PHPUnit\Framework\TestCase
{
    public const TESTSKU = 'TestSku2';
    public const STATUS = 'status';
    public const ERROR_ID = 'error_id';
    public const ISSUE001 = "Issue came in saving inventory to messagequeue";

    /**
     * @var $erpMessageQueue
     */
    public $erpMessageQueue;
    /**
     * @var $i95devServerRepo
     */
    public $i95devServerRepo;
    /**
     * @var $productFactory
     */
    public $productFactory;
    /**
     * @var $resourceConfig
     */
    public $resourceConfig;
    /**
     * @var $errorUpdateData
     */
    public $errorUpdateData;
    /**
     * @var $stockData
     */
    public $stockData;

    /**
     * Test case for inventory reverse sync
     *
     * @magentoDbIsolation enabled
     * @author             Hrusieksh Manna
     */
    public function testReverseInventory()
    {
        $file = "/Json/Inventory.json";
        $data = $this->readJsonFile($file);
        $collection = $this->processData($data);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0][self::STATUS],
            self::ISSUE001
        );
        $stockItem = $this->stockData->getStockItemBySku(self::TESTSKU);
        $qty = $stockItem->getQty();
        $this->assertEquals(98, $qty);
    }

    /**
     * Read json data from file
     *
     * @param $fileName
     *
     * @return false|string
     * @author Hrusikesh Manna
     */
    public function readJsonFile($fileName)
    {
        $path = realpath(dirname(__FILE__)) . $fileName;
        return file_get_contents($path);
    }

    /**
     * Process Inventory sync.
     *
     * @param  $inventoryJsonData
     * @return Object
     * @author Hrusikesh Manna
     */
    public function processData($inventoryJsonData)
    {
        $this->createSimpleProduct();
        $response = $this->i95devServerRepo->serviceMethod("createInventoryList", $inventoryJsonData);
        $this->assertEquals(true, $response->result);
        return $this->getInboundMqData();
    }

    /**
     * Create dummy product for test case
     *
     * @author Hrusikesh Manna
     */
    public function createSimpleProduct()
    {
        $SKU = self::TESTSKU;
        $this->productSKU = $SKU;
        $product = $this->productFactory->create();
        $product->setAttributeSetId(4);
        $product->setName($SKU);
        $product->setPrice(10);
        $product->setSku($SKU);
        $product->setVisibility(4);
        $product->setWebsiteIds([1]);
        $product->setTypeId('simple');
        $product->save();
        $this->productId = $product->getId();
    }

    /**
     * Get inbound collection by reference name
     *
     * @return Object
     * @author Hrusikesh Manna
     */
    public function getInboundMqData()
    {
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('ref_name', self::TESTSKU)
            ->getData();
    }

    /**
     * Test case for inventory reverse sync with backorders as true.
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testReverseInventoryWithBackOrders()
    {
        $file = "/Json/InventoryWithBackorders.json";
        $data = $this->readJsonFile($file);
        $collection = $this->processData($data);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0][self::STATUS],
            self::ISSUE001
        );
        $stockItem = $this->stockData->getStockItemBySku(self::TESTSKU);
        $backOrders = $stockItem->getBackorders();
        $this->assertEquals(true, $backOrders);
    }

    /**
     * Test case for inventory update reverse sync.
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testReverseInventoryUpdate()
    {
        $file = "/Json/Inventory.json";
        $data = $this->readJsonFile($file);
        $this->processData($data);
        $oldStockItem = $this->stockData->getStockItemBySku(self::TESTSKU);
        $oldQty = $oldStockItem->getQty();
        $inventoryUpdateFile = "/Json/InventoryUpdate.json";
        $inventoryUpdateData = $this->readJsonFile($inventoryUpdateFile);
        $response = $this->i95devServerRepo->serviceMethod("createInventoryList", $inventoryUpdateData);
        $this->assertEquals(true, $response->result);
        $collection = $this->getInboundMqData();
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0][self::STATUS],
            self::ISSUE001
        );
        $stockItem = $this->stockData->getStockItemBySku(self::TESTSKU);
        $updatedQty = $stockItem->getQty();
        $this->assertNotEquals($oldQty, $updatedQty);
        $this->assertEquals(2020, $updatedQty);
    }

    /**
     * Sync inventory with wrong sku
     *
     * @magentoDbIsolation enabled
     * @author             Hrusieksh Manna
     */
    public function testReverseInventoryWrongSku()
    {
        $file = "/Json/ReverseInventoryWrongSku.json";
        $data = $this->readJsonFile($file);
        $collection = $this->processData($data);
        $errorMsg = $this->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("Sku not Exists::TestSku21", $errorMsg[0]['msg']);
    }

    /**
     * Get error message by error Id
     *
     * @param $error_id
     *
     * @return object
     * @author Hrusikesh Manna
     */
    public function getErrorData($error_id)
    {
        return $this->errorUpdateData->getCollection()
            ->addFieldToFilter('id', $error_id)
            ->getData();
    }

    /**
     * Sync inventory with empty sku
     *
     * @magentoDbIsolation enabled
     * @author             Hrusieksh Manna
     */
    public function testReverseInventoryEmptySku()
    {
        $file = "/Json/ReverseInventoryEmptySku.json";
        $data = $this->readJsonFile($file);
        $collection = $this->processData($data);
        $errorMsg = $this->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_prod_005", $errorMsg[0]['msg']);
    }

    /**
     * Sync inventory with empty Qty
     *
     * @magentoDbIsolation enabled
     * @author             Hrusieksh Manna
     */
    public function testReverseInventoryEmptyQty()
    {
        $file = "/Json/ReverseInventoryEmptyQty.json";
        $data = $this->readJsonFile($file);
        $collection = $this->processData($data);
        $errorMsg = $this->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_prod_020", $errorMsg[0]['msg']);
    }

    /**
     * Intialized Objects
     *
     * @author Hrusikesh Manna
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->erpMessageQueue = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository::class
        );
        $this->i95devServerRepo = $objectManager->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->productFactory = $objectManager->create(
            \Magento\Catalog\Model\ProductFactory::class
        );
        $this->errorUpdateData = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\ErrorUpdateData::class
        );
        $this->stockData = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogInventory\Api\StockRegistryInterface::class
        );
    }
}
