<?php

namespace I95DevConnect\ConfigurableProducts\Test\Integration\ConfigurableProducts;

use I95DevConnect\I95DevServer\Model\I95DevServerRepository;
use I95DevConnect\I95DevServer\Test\Integration\DummyData;
use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\I95DevErpMQRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use PHPUnit\Framework\TestCase;

/**
 * ConfigProTestCase class for integration test
 */
class ConfigProTestCase extends TestCase // NOSONAR
{
    public const REQUEST_DATA_STR = 'RequestData';

    /**
     * @var object
     */
    public $product;

    /**
     * @var string
     */
    public $dummyData;

    /**
     * @var object
     */
    public $i95devServerRepo;

    /**
     * @var object
     */
    public $erpMessageQueue;

    /**
     * @var object
     */
    public $productRepo;

    /**
     * @var string
     */
    public $configProValidPath = '/Json/ConfigProValidErpData.json';

    /**
     * @var string[][]
     */
    public $attributeWithKey = [
        "attributeWithKey" => ["attributeCode" => "color", "attributeValue" => "RED", "attributeType" => "select"]
    ];

    /**
     * @author Debashis S. Gopal
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->product = $objectManager->create(
            ProductRepository::class
        );
        $this->dummyData = $objectManager->create(
            DummyData::class
        );
        $this->erpMessageQueue = $objectManager->create(
            I95DevErpMQRepository::class
        );
        $this->i95devServerRepo = $objectManager->create(
            I95DevServerRepository::class
        );
        $this->productRepo = $objectManager->create(
            Product::class
        );
    }

    /**
     * Prepare data for test case
     *
     * @param  $attribute
     * @return mixed
     * @author Hrusikesh Manna
     */
    public function configurableProductPrerequistiesData($attribute)
    {
        return $this->dummyData->createSingleSimpleProduct('Red_shirt', $attribute);
    }

    /**
     * Process Test Case
     *
     * @param  array $data
     * @author Hrusieksh Manna
     */
    public function processTestCase($data)
    {
        $response = $this->createRecord($data);
        $this->assertEquals(
            Data::PENDING,
            $response[0]['status'],
            "Issue came in saving configurable product to messagequeue"
        );
        $this->i95devServerRepo->syncMQtoMagento();
    }

    /**
     * Get inbound message queue collection by ref name
     *
     * @param  $requestData
     * @return Object
     * @author Hrusikesh Manna
     */
    public function getInboundMqData($requestData)
    {
        $arrayData = json_decode($requestData, true);
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('ref_name', $arrayData[self::REQUEST_DATA_STR][0]['Reference'])
            ->getData();
    }

    /**
     * Create Record In Inbound Message Queue
     *
     * @param  $productJsonData
     * @return mixed
     * @author Hrusieksh Manna
     */
    public function createRecord($productJsonData)
    {
        $array = json_decode($productJsonData, true);
        $this->i95devServerRepo->serviceMethod("createConfigurableProductList", $productJsonData);
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('ref_name', $array[self::REQUEST_DATA_STR][0]['Reference'])
            ->getData();
    }

    /**
     * Read content from file
     *
     * @param  array $fileName
     * @return array
     * @author Hrusikesh Manna
     */
    public function readJsonData($fileName)
    {
        $path = realpath(dirname(__FILE__)) . $fileName;
        return file_get_contents($path);
    }
}
