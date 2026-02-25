<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\BaseConnector;

/**
 * Class responsible for forward order work flow
 */
class I95DevServerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Initiate objects
     *
     * @author Hrusikesh Manna
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->i95devServerRepo = $objectManager->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->dummyData = $objectManager->create(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );
    }

    /**
     * Test case for if connector disabled
     *
     * @magentoConfigFixture current_website i95dev_messagequeue/i95dev_extns/enabled 0
     * @magentoDbIsolation   enabled
     * @author               Hrusikesh Manna
     */
    public function testIsConnectorDisabled()
    {
        $this->processTestCase();
        $orderInfo = $this->i95devServerRepo->serviceMethod("getOrdersInfo");
        $this->assertEquals(
            false,
            $orderInfo->getResultdata()[0]['result'],
            "Response should false while connector disabled."
        );
    }

    /**
     * Test connector with invalid service method
     *
     * @magentoDbIsolation enabled
     * @author             Hrusikesh Manna
     */
    public function testConnectorWithinvalidServiceMethod()
    {
        $this->processTestCase();
        $orderInfo = $this->i95devServerRepo->serviceMethod(null);
        $this->assertEquals(
            false,
            $orderInfo->getResultdata()[0]['result'],
            "Response should false while invalid service method given."
        );
    }

    /**
     * @author Hrusieksh Manna
     */
    public function processTestCase()
    {
        $this->dummyData->createCustomer();
        $this->dummyData->createSingleSimpleProduct(10001);
        $this->dummyData->addInventory();
        $this->order = $this->dummyData->createSingleOrder(1027);
    }
}
