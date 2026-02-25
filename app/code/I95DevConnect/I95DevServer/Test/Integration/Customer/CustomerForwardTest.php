<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Customer;

/**
 * Customer test case for forward flows
 */
class CustomerForwardTest extends \PHPUnit\Framework\TestCase
{
    public const SOURCEID = "sourceId";
    public const STATUS = "status";
    public const MGT_ID = "magento_id";
    public const GET_CSTR_INFO = "getCustomersInfo";
    public const REQ_DATA = '{"requestData":[],"packetSize":50,"erp_name":"ERP"}';
    public const ERROR_MSG = "Unable to fetch customer from outbound MQ";
    public const IN_DATA = "InputData";
    public const STATUS_CHECK = "Status should be REQUEST TRANSFERED in outbound message queue";

    /**
     * @author Divya Koona
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->dummyData = $objectManager->create(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );
        $this->i95devServerRepo = $objectManager->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->magentoMessageQueue = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\I95DevMagMQRepository::class
        );
        $this->customerRepo = $objectManager->create(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );

        $this->customerAddressFactory = $objectManager->create(
            \Magento\Customer\Model\AddressFactory::class
        );

        $this->customerAddressRepo = $objectManager->create(
            \Magento\Customer\Api\AddressRepositoryInterface::class
        );

        $this->groupFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\Data\GroupInterfaceFactory::class
        );
        $this->groupRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\GroupRepositoryInterface::class
        );
    }

    /**
     * Get outbound message queue collection data by magento_id
     *
     * @param $magentoId
     *
     * @return array
     * @author Divya Koona
     */
    public function getOutboundMqData($magentoId)
    {
        $collections = $this->magentoMessageQueue->getCollection()
            ->addFieldToFilter('entity_code', 'Customer')
            ->addFieldToFilter(self::MGT_ID, $magentoId);
        return $collections->getData();
    }

    /**
     * Process getCustomersInfo service and check the required assertion
     *
     * @param  int $customerId
     * @return string
     */
    public function getCustomerInfo($customerId)
    {
        $outboundData = $this->getOutboundMqData($customerId);
        $this->assertNotNull($outboundData);
        $this->assertEquals($customerId, $outboundData[0][self::MGT_ID], "Magento id is different");
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $outboundData[0][self::STATUS],
            "Status should be PENDING in outbound message queue"
        );
        $response = $this->i95devServerRepo->serviceMethod(
            self::GET_CSTR_INFO,
            self::REQ_DATA
        );
        $this->assertEquals(true, $response->result, self::ERROR_MSG);
        $this->assertNotEmpty($response->resultData);
        $this->assertEquals(1, count($response->resultData));
        $this->assertEquals($outboundData[0][self::MGT_ID], $response->resultData[0][self::SOURCEID]);
        $inputData = $response->resultData[0][self::IN_DATA];
        $this->assertNotEmpty($inputData);
        return json_decode($this->dummyData->decryptDES($inputData));
    }
    /**
     * Test case for testGetCustomersInfo
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testGetCustomerWithAddress()
    {
        $this->dummyData->createCustomerWithoutAddress();
        $customerId = $this->dummyData->customerId;
        $customerDataArray = $this->getCustomerInfo($customerId);
        $this->assertEmpty($customerDataArray->addresses);
        $this->assertEquals('hrusikesh.manna@jiva.com', $customerDataArray->email);
        $outboundDataAfterSync = $this->getOutboundMqData($customerId);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $outboundDataAfterSync[0][self::STATUS],
            self::STATUS_CHECK
        );
    }

    /**
     * Test case for testGetCustomersInfo
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testGetCustomerWithAddressInfo()
    {
        $this->dummyData->createCustomer();
        $customerId = $this->dummyData->customerId;
        $customerDataArray = $this->getCustomerInfo($customerId);
        $this->assertNotEmpty($customerDataArray->addresses);
        $this->assertEquals(1, count($customerDataArray->addresses));
        $address = $customerDataArray->addresses[0];
        $this->assertEquals(1, $address->isDefaultBilling);
        $this->assertEquals(1, $address->isDefaultShipping);
        $outboundDataAfterSync = $this->getOutboundMqData($customerId);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $outboundDataAfterSync[0][self::STATUS],
            self::STATUS_CHECK
        );
    }

    /**
     * Test case for testGetCustomersInfo
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testCustomerUpdate()
    {
        $this->dummyData->createCustomerWithoutAddress();
        $customerId = $this->dummyData->customerId;
        $customerDataArray = $this->getCustomerInfo($customerId);
        $this->updateCustomer($customerId);
        $updatedResponse = $this->i95devServerRepo->serviceMethod(
            self::GET_CSTR_INFO,
            self::REQ_DATA
        );
        $latestOutboundData = $this->getOutboundMqData($customerId);
        $this->assertEquals(true, $updatedResponse->result, self::ERROR_MSG);
        $this->assertNotEmpty($updatedResponse->resultData);
        $this->assertEquals(1, count($updatedResponse->resultData));
        $this->assertEquals($latestOutboundData[0][self::MGT_ID], $updatedResponse->resultData[0][self::SOURCEID]);
        $updatedInputData = $updatedResponse->resultData[0][self::IN_DATA];
        $this->assertNotEmpty($updatedInputData);
        $updatedCustomerDataArray = json_decode($this->dummyData->decryptDES($updatedInputData), 1);
        $this->assertNotEquals($customerDataArray, $updatedCustomerDataArray);
        $this->assertEquals('hrusikesh_update.manna@jiva.com', $updatedCustomerDataArray['email']);
        $this->assertEquals('Hrusikesh_update', $updatedCustomerDataArray['firstName']);
    }

    /**
     * Test case for testGetCustomersInfo
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testGetCustomerWithMultipleAddress()
    {
        $this->dummyData->createCustomer();
        $customerId = $this->dummyData->customerId;
        $this->addAddress($customerId);
        $customerDataArray = $this->getCustomerInfo($customerId);
        $this->assertNotEmpty($customerDataArray->addresses);
        $this->assertEquals(2, count($customerDataArray->addresses));
        $outboundDataAfterSync = $this->getOutboundMqData($customerId);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $outboundDataAfterSync[0][self::STATUS],
            self::STATUS_CHECK
        );
    }

    /**
     * Test case for testGetCustomersInfo
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testGetCustomerGroupUpdate()
    {
        $this->dummyData->createCustomer();
        $customerId = $this->dummyData->customerId;
        $this->updateCustomerGroup($customerId);
        $updatedResponse = $this->i95devServerRepo->serviceMethod(
            self::GET_CSTR_INFO,
            self::REQ_DATA
        );
        $this->assertEquals(true, $updatedResponse->result, self::ERROR_MSG);
        $this->assertNotEmpty($updatedResponse->resultData);
        $this->assertEquals(1, count($updatedResponse->resultData));
        $updatedInputData = $updatedResponse->resultData[0][self::IN_DATA];
        $this->assertNotEmpty($updatedInputData);
        $updatedCustomerDataArray = json_decode($this->dummyData->decryptDES($updatedInputData), 1);
        $this->assertEquals('ITSGRP', $updatedCustomerDataArray['customerGroup']);
    }

    /**
     * Test case for testGetCustomersInfo
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testGetCustomerAddressUpdate()
    {
        $this->dummyData->createCustomer();
        $customerId = $this->dummyData->customerId;
        $customerDataArray = $this->getCustomerInfo($customerId);
        $oldAddress = $customerDataArray->addresses[0];
        $this->addressUpdate($customerId);
        $updatedResponse = $this->i95devServerRepo->serviceMethod(
            self::GET_CSTR_INFO,
            self::REQ_DATA
        );
        $this->assertNotEmpty($updatedResponse->resultData);
        $this->assertEquals(1, count($updatedResponse->resultData));
        $updatedInputData = $updatedResponse->resultData[0][self::IN_DATA];
        $this->assertNotEmpty($updatedInputData);
        $updatedCustomerDataArray = json_decode($this->dummyData->decryptDES($updatedInputData), 1);
        $updatedAddress = $updatedCustomerDataArray['addresses'][0];
        $this->assertNotEmpty($updatedAddress);
        $this->assertNotEquals($oldAddress, $updatedAddress);
        $this->assertEquals('9975445445', $updatedAddress['telephone']);
        $this->assertEquals('99585', $updatedAddress['postcode']);
    }

    /**
     * Update Customer address
     *
     * @param  int $customerId
     * @return void
     */
    public function addressUpdate($customerId)
    {
        $customer = $this->customerRepo->getById($customerId);
        $address = $customer->getAddresses()[0];
        $address->setTelephone('9975445445')->setPostcode('99585');
        $this->customerAddressRepo->save($address);
    }

    /**
     * Update Customer
     *
     * @param  int $customerId
     * @return void
     */
    public function updateCustomer($customerId)
    {
        $customer = $this->customerRepo->getById($customerId);
        $customer->setEmail('hrusikesh_update.manna@jiva.com')
            ->setFirstName('Hrusikesh_update');
        $this->customerRepo->save($customer);
    }

    /**
     * Update customer group to customer
     *
     * @param  int $customerId
     * @return void
     */
    public function updateCustomerGroup($customerId)
    {
        $group = $this->groupFactory->create();
        $group->setCode('ITSGRP')
            ->setTaxClassId(3);
        $id = $this->groupRepository->save($group)->getId();
        $customer = $this->customerRepo->getById($customerId);
        $customer->setGroupId($id);
        $this->customerRepo->save($customer);
    }

    /**
     * Add address to customer
     *
     * @param  int $customerId
     * @return void
     */
    public function addAddress($customerId)
    {
        $customerAddress = $this->customerAddressFactory->create();
        $customerAddress->setCustomerId($customerId)
            ->setRefName(60001)
            ->setFirstname('Hrusikesh')
            ->setLastname('Manna')
            ->setCountryId('CA')
            ->setRegionId(66)
            ->setRegion('Alberta')
            ->setPostcode('GB-W1 3AL')
            ->setCity('Toronto')
            ->setTelephone('989454445')
            ->setStreet("Brounch road")
            ->save();
        return $customerAddress->getId();
    }
}
