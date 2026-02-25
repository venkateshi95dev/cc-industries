<?php

namespace I95DevConnect\ConfigurableProducts\Test\Integration\ConfigurableProducts;

use I95DevConnect\I95DevServer\Model\I95DevServerRepository;
use I95DevConnect\I95DevServer\Test\Integration\DummyData;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\DataPersistence\Product\Product\Create;
use Magento\Catalog\Model\ProductRepository;
use Magento\Sales\Model\OrderFactory;
use PHPUnit\Framework\TestCase;

/**
 * OrderReverseTest class for integration test
 */
class OrderReverseTest extends TestCase // NOSONAR
{
    public const STATUS_STR = 'status';
    public const PSTR = 'parent_sku';

    /**
     * @var object
     */
    public $product;

    /**
     * @var object
     */
    public $i95devServerRepo;

    /**
     * @var object
     */
    public $dummyData;

    /**
     * @var object
     */
    public $helper;

    /**
     * @var object
     */
    public $productCreate;

    /**
     * @var object
     */
    public $order;

    /**
     * @var int
     */
    public $productId;

    /**
     * @author Debashis S. Gopal
     */
    protected function setUp()
    {
        $this->product = Bootstrap::getObjectManager()->create(
            ProductRepository::class
        );
        $this->i95devServerRepo = Bootstrap::getObjectManager()->create(
            I95DevServerRepository::class
        );

        $this->dummyData = Bootstrap::getObjectManager()->create(
            DummyData::class
        );

        $this->helper = Bootstrap::getObjectManager()->create(
            Helper::class
        );
        $this->productCreate = Bootstrap::getObjectManager()->create(
            Create::class
        );
        $this->order = Bootstrap::getObjectManager()->create(
            OrderFactory::class
        );
    }

    /**
     * Complete sync from putting data in inbound to sync to magento
     *
     * @param  array $requestData
     * @param  array $data
     * @author Hrusikesh Manna
     */
    public function completeSync($requestData, $data)
    {
        $result = $this->createChildProducts($data);

        $this->productId = $result->resultData;
        $path = realpath(dirname(__FILE__)) . "/Json/" . $requestData['path'];
        $productJsonData = file_get_contents($path);

        $response = $this->helper->createConfigurableProductInInboundMQ($productJsonData, $requestData[self::PSTR]);
        $this->assertEquals(
            Data::PENDING,
            $response[0][self::STATUS_STR],
            "Issue came in saving product to messagequeue"
        );
        $this->i95devServerRepo->syncMQtoMagento();
        $collection = $this->helper->getInboundMqData($requestData[self::PSTR]);
        $this->assertEquals(
            Data::SUCCESS,
            $collection[0][self::STATUS_STR],
            "Issue came in saving product from mq to magento"
        );
        $product_data = $this->product->get($requestData['parent_sku']);
        $product_id = $product_data->getEntityId();
        $this->assertEquals(
            $product_id,
            $collection[0]['magento_id'],
            "Issue came in saving product from mq to magento"
        );
        if (isset($requestData['ordertestreverse'])) {
            $collection = $this->helper->orderSyncWithConfigurablePro($requestData, $this->productId);
            $this->assertEquals(
                Data::SUCCESS,
                $collection[0][self::STATUS_STR],
                "Issue came in saving order from mq to magento"
            );
            $order_data = $this->order->create()->loadByIncrementId($collection[0]['magento_id']);
            $this->assertNotEmpty($order_data, 'Order not created in magento');
        }
    }

    /**
     * Creating child product
     *
     * @param  $data
     * @return mixed
     * @author Debashis S. Gopal
     */
    public function createChildProducts($data)
    {
        return $this->productCreate->createProduct($data, 'Product', null);
    }
}
