<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Customer;

/**
 * Integration test cases Class for Customer Messageque Status
 */
class CustomerMessagequeStatusTest extends \PHPUnit\Framework\TestCase
{
    public const SUCCESS = 1;
    public const ERROR = 0;
    public const REQ_DATA = 'requestData';
    public const L_MSG_ID = "localMessageId";
    public const ENTITYID = "entityId";
    public const MSG_STATUS = 'messageStatus';
    public const STATUS = 'status';
    public const SYNC_C = "syncCounter";
    public const SCDL_ID = "schedulerId";
    public const MSGID = "messageId";
    public const MSG_ID = "msg_id";
    public const SET_MSG_LIST = "setMessageQueueResponseAckList";

    /**
     * @author Hrusieksh Manna
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->i95devServerRepo = $objectManager->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->erpMessageQueue = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository::class
        );
    }

    /**
     * Test case for customer response tO ERP
     *
     * @magentoDbIsolation enabled
     * @author             Hrusikesh Manna
     */
    public function testCustomerMessageQueStatus()
    {
        $file = "/Json/Customer.json";
        $result = $this->processData($file, 'ERPCUST001');
        $requestData = [];
        $requestData[self::REQ_DATA][] = [
            self::L_MSG_ID => '3790',
            self::ENTITYID => '0',
            self::MSG_STATUS => $result[0][self::STATUS],
            self::SYNC_C => 1,
            self::SCDL_ID => "",
            self::MSGID => $result[0][self::MSG_ID]];
        $response = $this->i95devServerRepo->serviceMethod(
            "getMessageQueueResponseList",
            json_encode($requestData, true)
        );
        $this->assertEquals(self::SUCCESS, $response->getResult(), "Issue came in customer set response");
    }

    /**
     * Test customer response to ERP with invalid address info
     *
     * @magentoDbIsolation enabled
     * @author             Hrusikesh Manna
     */
    public function testCustomerInvalidAddressResponse()
    {
        $file = "/Json/CustomerInvalidAddressResponse.json";
        $result = $this->processData($file, 'ERPCUST002');
        $requestData = [];
        $requestData[self::REQ_DATA][] = [
            self::L_MSG_ID => '3791',
            self::ENTITYID => '0',
            self::MSG_STATUS => $result[0][self::STATUS],
            self::SYNC_C => 1,
            self::SCDL_ID => "",
            self::MSGID => $result[0][self::MSG_ID]
            ];
        $response = $this->i95devServerRepo->serviceMethod(
            "getMessageQueueResponseList",
            json_encode($requestData, true)
        );
        $this->assertEquals(self::SUCCESS, $response->getResult(), "Issue came in customer set response");
    }

    /**
     * Test case for set Acknowledgment
     *
     * @magentoDbIsolation enabled
     * @author             Hrusikesh Manna
     */
    public function testSetAck()
    {
        $file = "/Json/Customer.json";
        $this->ackCommonCode($file, '3792', 'ERPCUST001');
    }

    /**
     * Test case for set Acknowledgment with error data
     *
     * @magentoDbIsolation enabled
     * @author             Hrusikesh Manna
     */
    public function testAckWithErrorData()
    {
        $file = "/Json/CustomerInvalidAddressResponse.json";
        $this->ackCommonCode($file, '3793', 'ERPCUST002');
    }

    /**
     * @param $file
     * @param $l_msg_id
     * @param $target_id
     */
    public function ackCommonCode($file, $l_msg_id, $target_id)
    {
        $result = $this->processData($file, $target_id);
        $requestData = [];
        $requestData[self::REQ_DATA][] = [
            self::L_MSG_ID => $l_msg_id,
            self::ENTITYID => '0',
            self::MSG_STATUS => $result[0][self::STATUS],
            self::SYNC_C => 1,
            self::SCDL_ID => "",
            self::MSGID => $result[0][self::MSG_ID]
        ];
        $this->i95devServerRepo->serviceMethod(
            self::SET_MSG_LIST,
            json_encode($requestData, true)
        );
    }

    /**
     * Test case address sync to Magento
     *
     * @magentoDbIsolation enabled
     * @author             Hrusikesh Manna
     */
    public function testAddressSync()
    {
        $file = "/Json/CustomerValidAddressResponse.json";
        $result = $this->processData($file, 'ERPCUST003');
        $requestData[self::REQ_DATA][] = [
            self::L_MSG_ID => '3794',
            self::ENTITYID => '0',
            self::MSG_STATUS => $result[0][self::STATUS],
            self::SYNC_C => 1,
            self::SCDL_ID => "",
            self::MSGID => $result[0][self::MSG_ID]
            ];
        $this->i95devServerRepo->serviceMethod(
            self::SET_MSG_LIST,
            json_encode($requestData, true)
        );
    }

    /**
     * Process test case
     *
     * @param text   $file
     * @param string $targetId
     *
     * @return array
     */
    public function processData($file, $targetId)
    {
        $customerData = $this->readJsonFile($file);

        $response = $this->createCustomersIninboundMQ($customerData, $targetId);

        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0][self::STATUS],
            "Issue came in saving customer to messagequeue"
        );
        $this->i95devServerRepo->syncMQtoMagento();
        return $this->getInboundMqData($targetId);
    }

    /**
     * Create customer in inbound messageque
     *
     * @param  text   $customerJsonData
     * @param  string $targetCustomerId
     * @return array
     */
    public function createCustomersIninboundMQ($customerJsonData, $targetCustomerId)
    {
        $this->i95devServerRepo->serviceMethod("createCustomersList", $customerJsonData);
        return $this->getInboundMqData($targetCustomerId);
    }

    /**
     * Read data from json file
     *
     * @author Hrusieksh Manna
     * @param  $fileName
     * @return false|string
     */
    public function readJsonFile($fileName)
    {
        $path = realpath(dirname(__FILE__)) . $fileName;
        return file_get_contents($path);
    }

    /**
     * get inbound message queue collection by ref name
     *
     * @param  $targetCustomerId
     * @return array
     * @author Debashis S. Gopal. getCollection by 'ref_name'
     * changed to getCollection by 'target_id', as we are passing target id to this function.
     */
    public function getInboundMqData($targetCustomerId)
    {
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('target_id', $targetCustomerId)
            ->getData();
    }
}
