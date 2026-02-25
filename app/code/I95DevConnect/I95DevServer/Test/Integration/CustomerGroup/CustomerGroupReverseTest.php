<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\CustomerGroup;

/**
 * CustomerGroup test case for reverse flows
 */
class CustomerGroupReverseTest extends \PHPUnit\Framework\TestCase
{
    public const JSONPATH = 'json_path';
    public const MGT_ID = "magento_id";
    public const TARGET_ID = "target_id";
    public const CGID = 'customer_group_id';
    public const STATUS = 'status';
    public const TESTGRP1 = 'TESTGRP1';

    /**
     * @var $i95devServerRepo
     */
    public $i95devServerRepo;
    /**
     * @var $erpMessageQueue
     */
    public $erpMessageQueue;
    /**
     * @var $i95devCustomerGroupFactory
     */
    public $i95devCustomerGroupFactory;

    /**
     * @author Debashis S. Gopal
     */
    protected function setUp(): void
    {
        $this->i95devServerRepo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->erpMessageQueue = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository::class
        );
        $this->i95devCustomerGroupFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\MessageQueue\Model\CustomerGroupFactory::class
        );

        $this->errorUpdateData = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\MessageQueue\Model\ErrorUpdateData::class
        );

        $this->groupRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\GroupRepositoryInterface::class
        );
    }

    /**
     * generic function for customer group test
     *
     * @param $requestData
     *
     * @return type
     * @author Debashis S. Gopal.code updated  - Added assertation for error cases.
     */
    public function createCustomerGroup($requestData)
    {
        $path = realpath(dirname(__FILE__)) . "/Json/" . $requestData[self::JSONPATH];
        $response = $this->callServiceMethod($path);
        $this->assertEquals(true, $response->result, $response->message);

        $this->i95devServerRepo->syncMQtoMagento();
        $targetId = $requestData[self::TARGET_ID];
        $mqCollection = $this->getInboundMqData($targetId);
        if (!isset($requestData['error_case'])) {
            $this->assertEquals(
                \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
                $mqCollection[0][self::STATUS],
                "Issue came in saving customer group from mq to magento"
            );
        } else {
            $this->assertEquals(
                \I95DevConnect\MessageQueue\Helper\Data::ERROR,
                $mqCollection[0][self::STATUS],
                "Status should be error."
            );
            $errorData = $this->getErrorData($mqCollection[0]["error_id"]);
            $this->assertSame($requestData['expected_error_msg'], $errorData, 'Wrong Error Message.');
        }

        return $mqCollection;
    }

    /**
     * get Customer group collection
     *
     * @param  type $targetId
     * @return type
     * @author Debashis S. Gopal. Code updated. Changed method parameter from $mqCollection to $targetId
     * as we are using targetId to get the collection.
     */
    public function getI95devGroupCollection($targetId)
    {
        return $this->i95devCustomerGroupFactory->create()->getCollection()->addFieldToFilter(
            'target_group_id',
            $targetId
        );
    }

    /**
     * Get Customer group by id.
     *
     * @author Debashis S. Gopal.
     * @param  int $id
     * @return \Magento\Customer\Api\GroupRepositoryInterface
     */
    public function getMagentoGroupById($id)
    {
        return $this->groupRepository->getById($id);
    }

    /**
     * Check group created in magento as well as in i95dev table.
     *
     * @author Debashis S. Gopal
     * @param  array $mqCollection
     */
    public function checkForExpectedResults($mqCollection)
    {
        $collection = $this->getI95devGroupCollection($mqCollection[0][self::TARGET_ID]);
        $this->assertEquals(1, $collection->getSize());
        $data = $collection->getData();
        $this->assertNotNull($data[0][self::CGID]);
        $this->assertEquals($data[0][self::CGID], $mqCollection[0][self::MGT_ID], "Response Id mismatch");

        $group = $this->getMagentoGroupById($mqCollection[0][self::MGT_ID]);
        $this->assertNotEmpty($group, "Group Not created in magento");
        $code = $group->getCode();
        $this->assertEquals($code, $mqCollection[0][self::TARGET_ID], 'Wrong group code set in magento.');
    }

    /**
     * Test case for customer group reverse sync
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testCreateCustomerGroup()
    {
        $mqCollection = $this->createCustomerGroup(
            [self::JSONPATH => 'CustomerGroupErpData.json', self::TARGET_ID => self::TESTGRP1]
        );
        $this->checkForExpectedResults($mqCollection);
    }

    /**
     * Test case for customer group reverse sync With Tax class
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testCreateCustomerGroupWithTaxclass()
    {
        $mqCollection = $this->createCustomerGroup(
            [self::JSONPATH => 'CustomerGroupWithTaxclass.json', self::TARGET_ID => self::TESTGRP1]
        );
        $this->checkForExpectedResults($mqCollection);
        $group = $this->getMagentoGroupById($mqCollection[0][self::MGT_ID]);
        $taxClassId = $group->getTaxClassId();
        $this->assertEquals(3, $taxClassId, 'Wrong TaxClassId set in magento.');
    }

    /**
     * Test case for Customer group creation from ERP to Magento with tax class which doesn't exist in magento.
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testCreateCustomerGroupWithNotExistTaxclass()
    {
        $requestData = [
            self::JSONPATH => 'CustomerGroupWithNotExistTaxclass.json',
            self::TARGET_ID => self::TESTGRP1,
            'error_case' => 1,
            'expected_error_msg' => 'Invalid value of "2" provided for the taxClassId field.'];
        $this->createCustomerGroup($requestData);
        $collection = $this->getI95devGroupCollection(self::TESTGRP1);
        $this->assertEquals(0, $collection->getSize(), "The Order should not sync to magento.");
    }

    /**
     * Test case for update customer group.
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testUpdateCustomerGroup()
    {
        $mqCollection = $this->createCustomerGroup(
            [self::JSONPATH => 'CustomerGroupErpData.json', self::TARGET_ID => self::TESTGRP1]
        );
        $collection = $this->getI95devGroupCollection(self::TESTGRP1);
        $data = $collection->getData();
        $this->assertEquals($data[0][self::CGID], $mqCollection[0][self::MGT_ID], "Response Id mismatch");

        $path = realpath(dirname(__FILE__)) . "/Json/UpdateCustomerGroup.json";
        $updateResponse = $this->callServiceMethod($path);
        $this->assertEquals(true, $updateResponse->result, $updateResponse->message);

        $this->i95devServerRepo->syncMQtoMagento();
        $updateMqCollection = $this->getInboundMqData(self::TESTGRP1);
        $this->checkForExpectedResults($updateMqCollection);
    }

    /**
     * Test case for Update Magento Default customer groups.
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testUpdateMagentoDefaultCustomerGroup()
    {
        $mqCollection = $this->createCustomerGroup(
            [self::JSONPATH => 'UpdateMagentoDefaultCustomerGroup.json', self::TARGET_ID => 'General']
        );
        $this->checkForExpectedResults($mqCollection);
    }

    /**
     * get inbound message queue collection by target_id
     *
     * @author Debashis S. Gopal. code updated. Changed method parameter from $mqCollection to $targetId,
     * as we are sending target id to get .
     * @param  $targetId
     * @return Object
     */
    public function getInboundMqData($targetId)
    {

        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter(self::TARGET_ID, $targetId)
            ->getData();
    }

    /**
     * get error message
     *
     * @param  int $errorId
     * @return string
     * @author Debashis S. Gopal
     */
    public function getErrorData($errorId)
    {
        $errorData = $this->errorUpdateData->getCollection()
            ->addFieldToFilter('id', $errorId)
            ->getData();
        return $errorData[0]['msg'];
    }

    /**
     * Call createCustomerGroupList service with given json data.
     *
     * @param  string $jsonPath
     * @return I95DevConnect\MessageQueue\Model\I95DevReverseResponse
     */
    public function callServiceMethod($jsonPath)
    {
        $customerGroupJsonData = file_get_contents($jsonPath);
        return $this->i95devServerRepo->serviceMethod("createCustomerGroupList", $customerGroupJsonData);
    }
}
