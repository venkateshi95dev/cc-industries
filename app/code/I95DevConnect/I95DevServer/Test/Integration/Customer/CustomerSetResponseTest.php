<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Customer;

/**
 * Test case for customer SetResponse
 */
class CustomerSetResponseTest extends \PHPUnit\Framework\TestCase
{
    public const SOURCE_ID_STR = "sourceId";
    public const TARGET_ID_STR = "targetId";
    public const C00011_STR = "C00011";
    /**
     * @author Divya Koona
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->i95devServerRepo = $objectManager->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->magentoMessageQueue = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\I95DevMagMQRepository::class
        );
        $this->dummyData = $objectManager->create(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );
        $this->errorUpdateData = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\ErrorUpdateData::class
        );
    }

    /**
     * Get outbound message queue collection by entity_code and magento_id
     *
     * @author Divya Koona
     * @return array
     */
    public function getOutboundMqData()
    {
        return $this->magentoMessageQueue->getCollection()
            ->addFieldToFilter('entity_code', 'Customer')
            ->addFieldToFilter('magento_id', $this->dummyData->customerId)
            ->getData();
    }
    /**
     * Test case for setCustomersResponse with invalid data.
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testCustomerWithInvalidData()
    {
        $response = $this->callGetCustomersInfo();
        $this->assertEquals(true, $response->result, "Unable to fetch customer from outbound MQ");
        $responseAfterSync = $this->callSetResponseService(true);
        $this->assertNotNull($responseAfterSync);
        $mqDataAfterSync = $this->getOutboundMqData();
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $mqDataAfterSync[0]['status'],
            "Status should be ERROR in outbound message queue"
        );
        $errorMsg = $this->getErrorData($mqDataAfterSync[0]["error_id"]);
        $this->assertSame("Customer Not Found :: 1234", $errorMsg[0]['msg']);
    }

    /**
     * Test case for setCustomersResponse.
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testSetCustomersResponse()
    {
        $response = $this->callGetCustomersInfo();
        $this->assertEquals(true, $response->result, "Unable to fetch customer from outbound MQ");
        $responseAfterSync = $this->callSetResponseService(false);
        $this->assertNotNull($responseAfterSync);
        $mqDataAfterSync = $this->getOutboundMqData();
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::COMPLETE,
            $mqDataAfterSync[0]['status'],
            "Status should be ERROR in outbound message queue"
        );
    }

    /**
     * Calls getCustomersInfo service.
     *
     * @author Divya Koona
     * @return I95DevConnect\MessageQueue\Model\I95DevReverseResponse
     */
    public function callGetCustomersInfo()
    {
        $this->dummyData->createCustomer();
        return $this->i95devServerRepo->serviceMethod(
            "getCustomersInfo",
            '{"requestData":[],"packetSize":50,"erp_name":"ERP"}'
        );
    }

    /**
     * Calls setCustomersResponse service.
     *
     * @param $invalid
     *
     * @return I95DevConnect\MessageQueue\Model\I95DevReverseResponse
     * @author Divya Koona
     */
    public function callSetResponseService($invalid)
    {
        $mqData = $this->getOutboundMqData();
        $msgId = $mqData[0]['msg_id'];
        $request = [];
        $addressResponse = ['addresses' => [[
            self::TARGET_ID_STR => "PRIMARY",
            self::SOURCE_ID_STR => $this->dummyData->addressId,
            "targetCustomerId" => self::C00011_STR
        ]]];
        if ($invalid) {
            $request['requestData'][] = [
                'reference' => 'hrusikesh.manna@jiva.com',
                'messageId' => $msgId,
                'message' => '',
                'result' => true,
                self::TARGET_ID_STR => self::C00011_STR,
                self::SOURCE_ID_STR => 1234
            ];
        } else {
            $request['requestData'][] = [
                'reference' => 'hrusikesh.manna@jiva.com',
                'messageId' => $msgId,
                'message' => '',
                'result' => true,
                self::TARGET_ID_STR => self::C00011_STR,
                self::SOURCE_ID_STR => $this->dummyData->customerId,
                'inputData' => $this->dummyData->encryptAES(json_encode($addressResponse))
            ];
        }
        return $this->i95devServerRepo->serviceMethod("setCustomersResponse", json_encode($request));
    }

    /**
     * get error message
     *
     * @param  int $error_id
     * @return string
     * @author Hrusikesh Manna
     */
    public function getErrorData($error_id)
    {
        return $this->errorUpdateData->getCollection()
            ->addFieldToFilter('id', $error_id)
            ->getData();
    }
}
