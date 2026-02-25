<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Order;

/**
 * Test case for order set response
 */
class OrderSetResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Intiate objects
     *
     * @author Hrusikesh Manna
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->i95devServerRepo = $objectManager->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->errorUpdateData = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\ErrorUpdateData::class
        );
        $this->errorUpdateData = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\ErrorUpdateData::class
        );
        $this->magentoMessageQueue = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\I95DevMagMQRepository::class
        );
        $this->dummyData = $objectManager->create(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );
    }

    /**
     * Test case for order response
     *
     * @magentoDbIsolation  enabled
     * @magentoCache        all disabled
     * @magentoAppIsolation enabled
     * @author              Hrusikesh Manna
     */
    public function testSetOrderResponse()
    {
        $response = $this->processTestCase();
        $this->assertEquals(
            true,
            $response->result,
            "Unable to fetch customer from outbound MQ"
        );
        $responseAfterSync = $this->orderSetResponseService(false);
        $this->assertNotNull($responseAfterSync);
        $mqDataAfterSync = $this->getOutboundMqData();
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::COMPLETE,
            $mqDataAfterSync[0]['status'],
            "Status should be SUCCESS in outbound message queue"
        );
    }

    /**
     * Test case for set order response with invalid data
     *
     * @magentoDbIsolation  enabled
     * @magentoCache        all disabled
     * @magentoAppIsolation enabled
     * @author              Hrusikesh Manna
     */
    public function testCustomerResponseWithInvalidData()
    {
        $response = $this->processTestCase();
        $this->assertEquals(
            true,
            $response->result,
            "Unable to fetch customer from outbound MQ"
        );
        $this->orderSetResponseService(true);
        $mqDataAfterSync = $this->getOutboundMqData();
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $mqDataAfterSync[0]['status'],
            "Status should be ERROR in outbound message queue"
        );
        $errorMsg = $this->getErrorData($mqDataAfterSync[0]["error_id"]);
        $this->assertSame("Some error occured in response sync", $errorMsg[0]['msg']);
    }

    /**
     * Test case processor
     *
     * @author Hrusikesh Manna
     * @return I95DevConnect\MessageQueue\Model\I95DevReverseResponse
     */
    public function processTestCase()
    {
        $this->dummyData->createCustomer();
        $this->dummyData->createSingleSimpleProduct(1002);
        $order = $this->dummyData->createSingleOrder('');
        $this->order = $order;
        return $this->i95devServerRepo->serviceMethod(
            "getOrdersInfo",
            '{"requestData":[],"packetSize":10,"erp_name":"ERP"}'
        );
    }

    /**
     * Calls setOrderResponse service.
     *
     * @param $validation
     *
     * @return I95DevConnect\MessageQueue\Model\I95DevReverseResponse
     * @author Hrusikesh Koona
     */
    public function orderSetResponseService($validation)
    {
        $mqData = $this->getOutboundMqData();
        $msgId = $mqData[0]['msg_id'];

        if ($validation) {
            $request['requestData'][] = [
                'reference' => $this->order->getIncrementId(),
                'messageId' => $msgId,
                'message' => '',
                'result' => true,
                'targetId' => 'ORD_00011',
                'sourceId' => 1234
            ];
        } else {
            $request['requestData'][] = [
                'reference' => $this->order->getIncrementId(),
                'messageId' => $msgId,
                'message' => '',
                'result' => true,
                'targetId' => 'ORD_00011',
                'sourceId' => $this->order->getIncrementId()
            ];
        }

        return $this->i95devServerRepo->serviceMethod(
            "setOrdersResponse",
            json_encode($request)
        );
    }

    /**
     * Get outbound message queue collection by entity_code and magento_id
     *
     * @author Hrusikesh Manna
     * @return array
     */
    public function getOutboundMqData()
    {
        return $this->magentoMessageQueue->getCollection()
            ->addFieldToFilter('entity_code', 'Order')
            ->addFieldToFilter('magento_id', $this->order->getIncrementId())
            ->getData();
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
