<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Test\Integration\PriceLevel;

/**
 * Test case for Price Level reverse flow
 */
class PriceLevelReverseTest extends \PHPUnit\Framework\TestCase
{
    public const ISSUE001 = "Issue came in saving price level from mq to magento";
    public const PLTEST = 'PLTEST';
    public const ISUPDATECASE = 'isUpdateCase';
    public const JSONPATH = 'jsonPath';
    public const STATUS = 'status';

    /**
     * @var mixed
     */
    public $i95devServerRepo;
    /**
     * @var mixed
     */
    public $erpMessageQueue;
    /**
     * @var mixed
     */
    public $priceLevelDataFactory;
    /**
     * @var $priceLevel
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
        $this->erpMessageQueue = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository::class
        );
        $this->priceLevelDataFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\PriceLevel\Model\PriceLevelDataFactory::class
        );
    }

    /**
     * Create New Price Level
     *
     * @author Debashis S. Gopal
     */
    public function createPriceLevel()
    {
        $this->priceLevel = $this->priceLevelDataFactory->create();
        $this->priceLevel->setData("pricelevel_code", self::PLTEST);
        $this->priceLevel->setData("description", 'Integration Testing');
        $this->priceLevel->save();
    }

    /**
     * Test case for creating new price level.
     *
     * @magentoDbIsolation enabled
     * @author Debashis S. Gopal
     */
    public function testCreatePriceLevel()
    {
        $jsonPath = realpath(dirname(__FILE__)) . "/Json/PriceLevelErpData.json";
        $this->callPriceLevelService([self::ISUPDATECASE => false, self::JSONPATH => $jsonPath]);
        $mqCollection = $this->getInboundMqData(self::PLTEST);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $mqCollection[0][self::STATUS],
            self::ISSUE001
        );
    }

    /**
     * Test case for updating price level.
     *
     * @magentoDbIsolation enabled
     * @author Debashis S. Gopal
     */
    public function testUpdatePriceLevel()
    {
        $jsonPath = realpath(dirname(__FILE__)) . "/Json/PriceLevelUpdateErpData.json";
        $this->callPriceLevelService([self::ISUPDATECASE => true, self::JSONPATH => $jsonPath]);
        $mqCollection = $this->getInboundMqData(self::PLTEST);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $mqCollection[0][self::STATUS],
            self::ISSUE001
        );
    }

    /**
     * Test case for create price level with missing data.
     *
     * @magentoDbIsolation enabled
     * @author Debashis S. Gopal
     */
    public function testPriceLevelWithMissingData()
    {
        $jsonPath = realpath(dirname(__FILE__)) . "/Json/PriceLeveMissingErpData.json";
        $this->callPriceLevelService([self::ISUPDATECASE => false, self::JSONPATH => $jsonPath]);
        $mqCollection = $this->getInboundMqData('PLTEST1');
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $mqCollection[0][self::STATUS],
            self::ISSUE001
        );
    }

    /**
     * calls createPriceLevelsList service method
     *
     * @param array $inputs
     */
    public function callPriceLevelService($inputs)
    {
        if ($inputs[self::ISUPDATECASE]) {
            $this->createPriceLevel();
        }
        $jsonPath = $inputs[self::JSONPATH];
        $priceLevelJsonData = file_get_contents($jsonPath);
        $response = $this->i95devServerRepo->serviceMethod("createPriceLevelsList", $priceLevelJsonData);
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
