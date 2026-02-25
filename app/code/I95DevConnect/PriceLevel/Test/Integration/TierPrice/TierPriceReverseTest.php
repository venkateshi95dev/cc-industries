<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Test\Integration\TierPrice;

/**
 * Test case for Tier Price reverse flow
 */
class TierPriceReverseTest extends \PHPUnit\Framework\TestCase
{
    public const ISSUE001 = "Issue came in saving tierprice from mq to magento";
    public const TESTPRO = 'TESTPRO';
    public const TESTPRO2 = 'TESTPRO2';
    public const ISNEWPRICELEVEL = 'isNewPriceLevel';
    public const JSONPATH = 'jsonPath';
    public const STATUS = 'status';

    /**
     * @var mixed
     */
    public $i95devServerRepo;
    /**
     * @var mixed
     */
    public $productFactory;
    /**
     * @var mixed
     */
    public $productRepository;
    /**
     * @var mixed
     */
    public $erpMessageQueue;
    /**
     * @var mixed
     */
    public $i95DevPriceLevelFactory;
    /**
     * @var mixed
     */
    public $i95devTierPriceFactory;
    /**
     * @var mixed
     */
    public $dummyData;
    /**
     * @var $priceLevel;
     */
    public $priceLevel;

    /**
     * @author Debashis S. Gopal
     */
    protected function setUp()
    {
        $this->i95devServerRepo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->productFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\Data\ProductInterfaceFactory::class
        );
        $this->productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepositoryFactory::class
        );
        $this->erpMessageQueue = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository::class
        );
        $this->i95DevPriceLevelFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\PriceLevel\Model\PriceLevelDataFactory::class
        );
        $this->i95devTierPriceFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\PriceLevel\Model\ItemPriceListDataFactory::class
        );
        $this->dummyData = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );
    }

    /**
     * Create a price level.
     *
     * @author Debashis S. Gopal
     */
    public function createNewPriceLevel()
    {
        $this->priceLevel = $this->i95DevPriceLevelFactory->create();
        $this->priceLevel->setData("pricelevel_code", 'TESTPL');
        $this->priceLevel->setData("description", 'Test Price Level');
        $this->priceLevel->save();
    }
    /**
     * Create Item tier price
     * @author Debashis S. Gopal
     */
    public function createTierPrice()
    {
        $tierPrice = $this->i95devTierPriceFactory->create();
        $tierPrice->setData("sku", self::TESTPRO);
        $tierPrice->setData("qty", 1.00000);
        $tierPrice->setData("price", 110.00000);
        $tierPrice->setData("pricelevel", 'Test');
        $tierPrice->save();
    }

    /**
     * Test case for Tier price reverse sync with already existing Price Level.
     *
     * @magentoDbIsolation enabled
     * @author Debashis S. Gopal
     */
    public function testCreateTierPriceForAlreasdyExistPriceLevel()
    {
        $path = realpath(dirname(__FILE__)) . "/Json/TierpriceErpData.json";
        $this->completeSync(['isPriceLevelExists' => true, self::JSONPATH => $path]);
        $mqCollection = $this->getInboundMqData(self::TESTPRO);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $mqCollection[0][self::STATUS],
            self::ISSUE001
        );
    }

    /**
     * Test case for tier price  reverse sync with new customer groups
     *
     * @magentoDbIsolation enabled
     * @author Debashis S. Gopal
     */
    public function testCreateTierPriceForNewPriceLevel()
    {
        $path = realpath(dirname(__FILE__)) . "/Json/TierpriceErpDataForNewCustomerGroup.json";
        $this->completeSync([self::ISNEWPRICELEVEL => true, self::JSONPATH => $path]);
        $mqCollection = $this->getInboundMqData(self::TESTPRO2);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $mqCollection[0][self::STATUS],
            self::ISSUE001
        );
    }

    /**
     * Test case for tier price  reverse sync with unavailable product
     *
     * @magentoDbIsolation enabled
     * @author Debashis S. Gopal
     */
    public function testCreateTierPriceWithInvalidProduct()
    {
        $path =  realpath(dirname(__FILE__)) . "/Json/TierpriceErpDataForNewCustomerGroup.json";
        $this->completeSync([self::ISNEWPRICELEVEL => true, 'isInvalidProduct' => true, self::JSONPATH => $path]);
        $mqCollection = $this->getInboundMqData(self::TESTPRO2);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $mqCollection[0][self::STATUS],
            self::ISSUE001
        );
    }

    /**
     * Test case for tier price  reverse sync with Empty Tier Price Data
     *
     * @magentoDbIsolation enabled
     * @author Debashis S. Gopal
     */
    public function testCreateTierPriceWithEmptyTierPriceData()
    {
        $path = realpath(dirname(__FILE__)) . "/Json/TierpriceErpDataWithEmptyTierPriceData.json";
        $this->completeSync(['isEmptytier' => true, self::JSONPATH => $path]);
        $mqCollection = $this->getInboundMqData('TESTPRO3');
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $mqCollection[0][self::STATUS],
            self::ISSUE001
        );
    }

    /**
     * Test case for tier price  reverse sync with Empty Tier Price Data
     *
     * @magentoDbIsolation enabled
     * @author Debashis S. Gopal
     */
    public function testCreateTierPriceWithEmptyPriceLevelKey()
    {
        $path = realpath(dirname(__FILE__)) . "/Json/TierpriceErpDataWithEmptyPriceLevelKey.json";
        $this->completeSync(['isEmptyPriceLevelKey' => true, self::JSONPATH => $path]);
        $mqCollection = $this->getInboundMqData('TESTPRO4');
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $mqCollection[0][self::STATUS],
            self::ISSUE001
        );
    }

    /**
     * Test case for tier price update.
     *
     * @magentoDbIsolation enabled
     * @author Debashis S. Gopal
     */
    public function testUpdateTierPrice()
    {
        $path = realpath(dirname(__FILE__)) . "/Json/TierpriceErpData.json";
        $this->completeSync(['updateTierPrice' => true, self::JSONPATH => $path]);
        $mqCollection = $this->getInboundMqData(self::TESTPRO);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $mqCollection[0][self::STATUS],
            self::ISSUE001
        );
    }

    /**
     * Call updateTierPriceList service method
     *
     * @param array $inputs
     *
     * @return mixed
     * @author Debashis S. Gopal
     */
    public function callCreateTierPriceServiceMethod($inputs)
    {
        $jsonPath = $inputs[self::JSONPATH];
        if (isset($inputs['isPriceLevelExists'])) {
            $this->dummyData->createSingleSimpleProduct(self::TESTPRO);
            $this->createNewPriceLevel();
        } elseif (isset($inputs['isEmptytier'])) {
            $this->dummyData->createSingleSimpleProduct("TESTPRO3");
        } elseif (isset($inputs['isEmptyPriceLevelKey'])) {
            $this->dummyData->createSingleSimpleProduct("TESTPRO4");
        } elseif (isset($inputs[self::ISNEWPRICELEVEL])) {
            if (!isset($inputs['isInvalidProduct'])) {
                $this->dummyData->createSingleSimpleProduct(self::TESTPRO2);
            }
        } elseif (isset($inputs['updateTierPrice'])) {
            $this->dummyData->createSingleSimpleProduct(self::TESTPRO);
            $this->createTierPrice();
        }
        $tierPriceJsonData = file_get_contents($jsonPath);
        return $this->i95devServerRepo->serviceMethod("updateTierPriceList", $tierPriceJsonData);
    }

    /**
     * Generic function for all test cases which will execute both updateTierPriceList and sync from MQ to Magento
     *
     * @author Debashis S. Gopal
     * @param array $inputs
     */
    public function completeSync($inputs)
    {
        $response = $this->callCreateTierPriceServiceMethod($inputs);
        $this->assertEquals(true, $response->result, $response->message);
        $this->i95devServerRepo->syncMQtoMagento();
    }

    /**
     * get inbound message queue collection by target_id
     *
     * @param $targetId
     *
     * @return Object
     * @author Debashis S. Gopal
     */
    public function getInboundMqData($targetId)
    {
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('target_id', $targetId)
            ->getData();
    }
}
