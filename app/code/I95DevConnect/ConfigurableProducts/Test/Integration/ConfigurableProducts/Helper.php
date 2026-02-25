<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Test\Integration\ConfigurableProducts;

use Exception;
use I95DevConnect\I95DevServer\Model\I95DevServerRepository;
use I95DevConnect\I95DevServer\Test\Integration\DummyData;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Create;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Reverse\Attribute;
use I95DevConnect\MessageQueue\Model\I95DevErpMQRepository;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Helper class for Configurable product test cases
 */
class Helper
{
    /**
     * @var Create
     */
    public $productCreate;

    /**
     * @var I95DevServerRepository
     */
    public $i95devServerRepo;

    /**
     * @var I95DevErpMQRepository
     */
    public $erpMessageQueue;

    /**
     * @var StockRegistryInterface
     */
    public $stockRegistry;

    /**
     * @var DummyData
     */
    public $dummyData;

    /**
     * @var ProductRepository
     */
    public $product;

    /**
     * @var Attribute
     */
    public $attributeCreate;

    /**
     * @var ProductFactory
     */
    public $productFactory;

    /**
     * @var int
     */
    public $productId;

    /**
     * @var object
     */
    public $order;

    /**
     *
     * @param Create $productCreate
     * @param I95DevServerRepository $i95devServerRepo
     * @param I95DevErpMQRepository $erpMessageQueue
     * @param DummyData $dummyData
     * @param ProductRepository $product
     * @param Attribute $attributeCreate
     * @param ProductFactory $productFactory
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct( // NOSONAR
        Create $productCreate,
        I95DevServerRepository $i95devServerRepo,
        I95DevErpMQRepository $erpMessageQueue,
        DummyData $dummyData,
        ProductRepository $product,
        Attribute $attributeCreate,
        ProductFactory $productFactory,
        StockRegistryInterface $stockRegistry
    ) {
        $this->productCreate = $productCreate;
        $this->i95devServerRepo = $i95devServerRepo;
        $this->erpMessageQueue = $erpMessageQueue;
        $this->dummyData = $dummyData;
        $this->product = $product;
        $this->attributeCreate = $attributeCreate;
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Create product in inbound messagequeue
     *
     * @param $productJsonData
     * @return array
     */
    public function createConfigurableProductInInboundMQ($productJsonData)
    {
        $this->i95devServerRepo->serviceMethod("createConfigurableProductList", $productJsonData);
        return $this->getInboundMqData();
    }

    /**
     * Get inbound message queue collection by ref name
     *
     * @return array
     * @author Debashis S. Gopal
     */
    public function getInboundMqData()
    {
        return $this->erpMessageQueue->getCollection()->getData();
    }

    /**
     * Create an order in Magento
     *
     * @param  $requestData
     * @return string|null
     * @throws Exception
     * @author Debashis S. Gopal
     */
    public function createOrderInMagento($requestData)
    {
        $this->dummyData->createCustomer();
        $parentProduct = $this->product->get($requestData['parent_sku'], true, 0, true);
        $parentProduct->setStatus(Status::STATUS_ENABLED);
        $parentProduct->setVisibility(4);
        $this->product->save($parentProduct);
        $this->dummyData->productSKU = $requestData['child_sku'];
        $this->order = $this->dummyData->createSingleOrder(1025, 1);
        return $this->order->getIncrementId();
    }

    /**
     * Process order reverse sync flow.
     *
     * @param  $requestData
     * @param  $productId
     * @return array
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @author Debashis S. Gopal
     */
    public function orderSyncWithConfigurablePro($requestData, $productId)
    {
        $this->productId = $productId;
        $this->dummyData->createCustomer();
        $this->addInventory($requestData);
        $path = realpath(dirname(__FILE__)) . "/Json/OrderReverse.json";
        $data = file_get_contents($path);
        return $this->syncOrder($data);
    }

    /**
     * Add inventory to child product.
     *
     * @param  $requestData
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @author Debashis S. Gopal
     */
    public function addInventory($requestData)
    {
        $this->dummyData->productId = $this->productId;
        $this->dummyData->addInventory();
        $this->enableProduct($requestData);
    }

    /**
     * Enable the child product and parent product
     *
     * @param  $requestData
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     * @author Debashis S. Gopal
     */
    public function enableProduct($requestData)
    {
        $_product = $this->product->get($requestData['child_sku'], true, 0, true);
        $_product->setStatus(Status::STATUS_ENABLED);
        $_product->setVisibility(4);
        $this->product->save($_product);

        $parentProduct = $this->product->get($requestData['parent_sku'], true, 0, true);
        $parentProduct->setStatus(Status::STATUS_ENABLED);
        $parentProduct->setVisibility(4);
        $this->product->save($parentProduct);
        $this->stockRegistry->getStockItem($parentProduct->getId());
    }

    /**
     * Create data in message queue and sync to Magento
     *
     * @param  $orderData
     * @return array
     */
    public function syncOrder($orderData)
    {
        $this->createOrderInInboundMQ($orderData);
        $this->i95devServerRepo->syncMQtoMagento();
        return $this->getInboundMqData();
    }

    /**
     * Create order in inbound messagequeue
     *
     * @param  $orderJsonData
     * @return array
     * @author Debashis S. Gopal
     */
    public function createOrderInInboundMQ($orderJsonData)
    {
        $this->i95devServerRepo->serviceMethod("createOrderList", $orderJsonData);
        return $this->getInboundMqData();
    }
}
