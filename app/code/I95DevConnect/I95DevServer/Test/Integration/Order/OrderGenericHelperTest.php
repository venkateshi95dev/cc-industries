<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Order;

/**
 * Helper Class contains generic functions for order test cases
 */
class OrderGenericHelperTest extends \PHPUnit\Framework\TestCase // NOSONAR
{
    /**
     * @var \I95DevConnect\I95DevServer\Test\Integration\DummyData
     */
    public $dummyData;
    /**
     * @var \I95DevConnect\I95DevServer\Model\I95DevServerRepository
     */
    public $i95devServerRepo;
    /**
     * @var \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository
     */
    public $erpMessageQueue;

    /**
     *
     * @param \I95DevConnect\I95DevServer\Test\Integration\DummyData   $dummyData
     * @param \I95DevConnect\I95DevServer\Model\I95DevServerRepository $i95devServerRepo
     * @param \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository  $erpMessageQueue
     */
    public function __construct(
        \I95DevConnect\I95DevServer\Test\Integration\DummyData $dummyData,
        \I95DevConnect\I95DevServer\Model\I95DevServerRepository $i95devServerRepo,
        \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository $erpMessageQueue
    ) {
        $this->dummyData = $dummyData;
        $this->i95devServerRepo = $i95devServerRepo;
        $this->erpMessageQueue = $erpMessageQueue;
    }

    /**
     * Create dummy order for order forward flow test case
     *
     * @author Divya Koona
     */
    public function orderPrerequistiesData()
    {
        $this->dummyData->createCustomer("GPCUST128");
        $this->dummyData->createSingleSimpleProduct('FLEXDECK');
        $this->order = $this->dummyData->createSingleOrder('ORDST3500');
    }

    /**
     * Create data in message queue and sync to Magento
     *
     * @param $orderData
     * @param $erpId
     * @param $erpCustomerId
     *
     * @return array
     * @author Divya Koona
     */
    public function processData($orderData, $erpId, $erpCustomerId)
    {
        $response = $this->createOrderInInboundMQ($orderData, $erpId, $erpCustomerId);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0]['status'],
            "Issue came in saving order to messagequeue"
        );
        $this->i95devServerRepo->syncMQtoMagento();
        return $this->getInboundMqData($erpId, $erpCustomerId);
    }

    /**
     * get inbound message queue collection by entity code, target id and ref name
     *
     * @param  $targetOrderId
     * @param  $targetCustomerId
     * @return array
     * @author Divya Koona
     */
    public function getInboundMqData($targetOrderId, $targetCustomerId)
    {
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('entity_code', 'Order')
            ->addFieldToFilter('target_id', $targetOrderId)
            ->addFieldToFilter('ref_name', $targetCustomerId)
            ->getData();
    }

    /**
     * create order in inbound messagequeue
     *
     * @param  $orderJsonData
     * @param  $targetOrderId
     * @param  $targetCustomerId
     * @return array
     * @author Divya Koona
     */
    public function createOrderInInboundMQ($orderJsonData, $targetOrderId, $targetCustomerId)
    {
        $this->i95devServerRepo->serviceMethod("createOrderList", $orderJsonData);
        return $this->getInboundMqData($targetOrderId, $targetCustomerId);
    }

    /**
     * Read data from json file
     *
     * @author Divya Koona
     * @param  $fileName
     * @return false|string
     */
    public function readJsonFile($fileName)
    {
        $path = realpath(dirname(__FILE__)) . $fileName;
        return file_get_contents($path);
    }
}
