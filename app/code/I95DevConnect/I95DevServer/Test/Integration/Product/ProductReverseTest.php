<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Product;

/**
 * product test case for reverse flows
 */
class ProductReverseTest extends \PHPUnit\Framework\TestCase
{
    public const REF_NAME = "JABRA";
    public const STATUS = "status";
    public const ISSUE001 = "Issue came in saving product to messagequeue";
    public const ISSUE002 = "Issue came in saving product from mq to magento";
    public const QTY_STATUS = "quantity_and_stock_status";

    /**
     * @var $productFactory
     */
    public $productFactory;
    /**
     * @var $i95devServerRepo
     */
    public $i95devServerRepo;
    /**
     * @var $erpMessageQueue
     */
    public $erpMessageQueue;
    /**
     * @var $errorUpdateData
     */
    public $errorUpdateData;

    /**
     * @author Arushi Bansal
     */
    protected function setUp(): void
    {
        $this->product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $this->i95devServerRepo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->erpMessageQueue = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository::class
        );
        $this->errorUpdateData = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\MessageQueue\Model\ErrorUpdateData::class
        );
        $this->storeManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Store\Model\StoreManagerInterface::class
        );
        $this->dummyData = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );
    }

    /**
     * get inbound message queue collection by ref name
     *
     * @return Object
     * @author Arushi Bansal
     */
    public function getInboundMqData()
    {
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('ref_name', self::REF_NAME)
            ->getData();
    }

    /**
     * create product in inbound messagequeue
     *
     * @param $productJsonData
     *
     * @return array
     * @author Arushi Bansal
     */
    public function createProductInInboundMQ($productJsonData)
    {
        $this->i95devServerRepo->serviceMethod("createProductList", $productJsonData);
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('ref_name', self::REF_NAME)
            ->getData();
    }

    /**
     * @param $path
     * @param $sku
     *
     * @return mixed
     */
    public function productIntitalData($path, $sku)
    {
        $path = realpath(dirname(__FILE__)) . "/Json/" . $path;
        $productJsonData = file_get_contents($path);
        $response = $this->createProductInInboundMQ($productJsonData);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0][self::STATUS],
            self::ISSUE001
        );
        $this->i95devServerRepo->syncMQtoMagento();
        $collection = $this->getInboundMqData();
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0][self::STATUS],
            self::ISSUE002
        );
        $product = $this->product->get($sku);
        $product_id = $product->getEntityId();
        $this->assertEquals(
            $product_id,
            $collection[0]['magento_id'],
            self::ISSUE002
        );
        return $product;
    }

    /**
     * test basic happy path for a product
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @author               Arushi Bansal
     */
    public function testReverseProductUpdate()
    {
        $product = $this->productIntitalData("Product.json", self::REF_NAME);

        $qty = $product->getData(self::QTY_STATUS);
        $this->assertEquals(112, $qty['qty'], "Quantity of product did not synced properly");

        // Product with differnt quanity
        $pathUpdate = realpath(dirname(__FILE__)) . "/Json/ProductUpdate.json";
        $productUpdateJsonData = file_get_contents($pathUpdate);
        $response = $this->createProductInInboundMQ($productUpdateJsonData);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[1][self::STATUS],
            self::ISSUE001
        );

        $this->i95devServerRepo->syncMQtoMagento();
        $collection = $this->getInboundMqData();

        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[1][self::STATUS],
            self::ISSUE002
        );
        $product = $this->product->get(self::REF_NAME);
        $product_id = $product->getEntityId();
        $qty = $product->getData(self::QTY_STATUS);
        $this->assertEquals(
            $product_id,
            $collection[1]['magento_id'],
            self::ISSUE002
        );
        $this->assertEquals(112, $qty['qty'], "Quantity of product changed while product update");
    }

    /**
     * create Product with backorders true
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @author               Arushi Bansal
     */
    public function testProductWithBackordersTrue()
    {
        $product = $this->productIntitalData("ProductWithBackordersTrue.json", self::REF_NAME);
        $backorders = $product->getExtensionAttributes()->getStockItem()->getBackorders();
        $this->assertEquals(1, $backorders);
    }

    /**
     * create Product sku with space
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @author               Arushi Bansal
     */
    public function testProductSkuWithSpace()
    {
        $this->productIntitalData("skuWithSpace.json", "JABRA Test");
    }

    /**
     * create Product sku with percent
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @author               Arushi Bansal
     */
    public function testProductSkuWithPercent()
    {
        $this->productIntitalData("skuWithPercent.json", "JABRA%20");
    }

    /**
     * create Product with cost attribute
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @author               Arushi Bansal
     */
    public function testProductWithCost()
    {
        $product = $this->productIntitalData("ProductWithCost.json", self::REF_NAME);
        $cost = $product->getCost();
        $this->assertEquals(100, $cost);
    }

    /**
     * @param $path
     * @param $errMsg
     */
    public function checkForErrorData($path, $errMsg)
    {
        $path = realpath(dirname(__FILE__)) . "/Json/" . $path;
        $productJsonData = file_get_contents($path);

        $response = $this->createProductInInboundMQ($productJsonData);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0][self::STATUS],
            self::ISSUE001
        );

        $this->i95devServerRepo->syncMQtoMagento();

        $collection = $this->getInboundMqData();
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $collection[0][self::STATUS]
        );
        $msg = $this->dummyData->getErrorData($collection[0]['error_id']);
        $this->assertSame($errMsg, $msg);
    }

    /**
     * create Product with blank name
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @author               Arushi Bansal
     */
    public function testProductWithNameBlank()
    {
        $this->checkForErrorData("ProductNameBlank.json", "i95dev_prod_001");
    }

    /**
     * create Product without name
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @author               Arushi Bansal
     */
    public function testProductWithoutName()
    {
        $this->checkForErrorData("ProductWithoutName.json", "i95dev_prod_001");
    }

    /**
     * create Product with sku value empty
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @author               Arushi Bansal
     */
    public function testProductWithSkuBlank()
    {
        $this->checkForErrorData("ProductSkuBlank.json", "i95dev_prod_005");
    }

    /**
     * create Product without sku
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @author               Arushi Bansal
     */
    public function testProductWithoutSku()
    {
        $this->checkForErrorData("ProductWithoutSku.json", "i95dev_prod_005");
    }

    /**
     * create Product with empty price
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @author               Arushi Bansal
     */
    public function testProductWithPriceBlank()
    {
        $this->checkForErrorData("ProductWithBlankPrice.json", "i95dev_prod_014");
    }

    /**
     * create Product without price
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @author               Arushi Bansal
     */
    public function testProductWithoutPrice()
    {
        $this->checkForErrorData("ProductWithoutPrice.json", "i95dev_prod_014");
    }

    /**
     * create Product with non taxable
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @author               kavya koona
     */
    public function testProductWithNonTaxable()
    {
        $product = $this->productIntitalData("ProductWithNonTaxable.json", self::REF_NAME);
        $this->getProductTaxByStoreId($product->getEntityId(), 0);
    }

    /**
     * create Product with taxable
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_set 4
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/attribute_group 7
     * @author               kavya koona
     */
    public function testProductWithTaxable()
    {
        $product = $this->productIntitalData("ProductWithTaxable.json", self::REF_NAME);
        $this->getProductTaxByStoreId($product->getEntityId(), 2);
    }

    /**
     * get Product tax class Id
     *
     * @param  type $productId
     * @param  type $expectedVal
     * @author kavya koona
     */
    public function getProductTaxByStoreId($productId, $expectedVal)
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        $product = $this->product->getById($productId, $storeId);
        $this->assertEquals(
            $expectedVal,
            $product->getTaxClassId(),
            "Tax class of product did not synced properly for the store with Id" . $storeId
        );
    }
}
