<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Shipment;

/**
 * shipment test case for reverse flows
 */
class ShipmentReverseTest extends \PHPUnit\Framework\TestCase
{
    public const STATUS = "status";
    public const ISSUE001 = "Issue came in saving shipment to messagequeue";
    public const ISSUE002 = "Issue came in saving shipment from mq to magento";
    public const ORDER_STATUS = 'target_order_status not updated.';

    /**
     * @var $customer
     */
    public $customer;
    /**
     * @var $customerAddress
     */
    public $customerAddress;
    /**
     * @var $productFactory
     */
    public $productFactory;
    /**
     * @var $quote
     */
    public $quote;
    /**
     * @var $quoteManagement
     */
    public $quoteManagement;
    /**
     * @var $quoteItemModel
     */
    public $quoteItemModel;
    /**
     * @var $quoteAddressInterface
     */
    public $quoteAddressInterface;
    /**
     * @var $orderModel
     */
    public $orderModel;
    /**
     * @var $stockRegistry
     */
    public $stockRegistry;
    /**
     * @var $customSalesOrder
     */
    public $customSalesOrder;
    /**
     * @var $i95devServerRepo
     */
    public $i95devServerRepo;
    /**
     * @var $erpMessageQueue
     */
    public $erpMessageQueue;
    /**
     * @var $errorUpdateData
     */
    public $errorUpdateData;
    /**
     * @var $customSalesInvoice
     */
    public $customSalesInvoice;
    /**
     * @var $eavAttribute
     */
    public $eavAttribute;
    /**
     * @var $attributeOption
     */
    public $attributeOption;
    /**
     * @var $productAttributeOption
     */
    public $productAttributeOption;
    /**
     * @var $invoiceItem
     */
    public $invoiceItem;
    /**
     * @var $invoiceRepo
     */
    public $invoiceRepo;
    /**
     * @var $customInvoice
     */
    public $customInvoice;
    /**
     * @var $baseHelperData
     */
    public $baseHelperData;
    /**
     * @var $scopeConfig
     */
    public $scopeConfig;

    /**
     * @author Hrusikesh Manna
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->customSalesOrder = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\SalesOrderFactory::class
        );
        $this->i95devServerRepo = $objectManager->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->erpMessageQueue = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository::class
        );
        $this->errorUpdateData = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\ErrorUpdateData::class
        );
        $this->customSalesShipment = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\SalesShipmentFactory::class
        );
        $this->scopeConfig = $objectManager->create(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        );
        $this->dummyData = $objectManager->get(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );
    }

    /**
     * prepare shipment data for test cases of simple product
     *
     * @param  string $shipmentJsonData
     * @return object
     * @author Hrusikesh Manna
     */
    public function shipmentPrerequistiesData($shipmentJsonData)
    {
        $this->dummyData->createCustomer();
        $this->dummyData->createSingleSimpleProduct(1001);
        $this->dummyData->addInventory();
        $this->order = $this->dummyData->createSingleOrder(1026);
        $this->i95devServerRepo->serviceMethod("createShipmentList", $shipmentJsonData);
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('ref_name', 1026)
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

    /**
     * get inbound message queue collection by ref name
     *
     * @return Object
     * @author Hrusikesh Manna
     */
    public function getInboundMqData()
    {
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('ref_name', 1026)
            ->getData();
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Hrusikesh Manna
     */
    public function testReverseShipment()
    {
        $file = "/Json/ShipmentPullData.json";
        $this->processTestCase($file);
        $targetOrderStatus = $this->dummyData->getTargetOrderStatus(1026);
        $this->assertEquals('Shipped', $targetOrderStatus, self::ORDER_STATUS);
    }
    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     */
    public function testShipmentComments()
    {
        $this->testReverseShipment();
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Hrusikesh Manna
     */
    public function testReverseShipmentWithoutTargetOrderId()
    {
        $path = realpath(dirname(__FILE__)) . "/Json/ShipmentWithoutTargetOrderId.json";
        $shipmentJsonData = file_get_contents($path);

        $response = $this->shipmentPrerequistiesData($shipmentJsonData);

        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0][self::STATUS],
            self::ISSUE001
        );

        $this->i95devServerRepo->syncMQtoMagento();

        $collection = $this->getInboundMqData();
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $collection[0][self::STATUS]
        );

        $errorMsg = $this->getErrorData($collection[0]["error_id"]);
        $this->assertSame("i95dev_empty_targetOrderId", $errorMsg[0]['msg']);
    }

    /**
     * generic function for checking invalid fields
     *
     * @param $path
     * @param $expectedString
     *
     * @author Arushi Bansal
     */
    public function checkInvalidFields($path, $expectedString)
    {
        $path = realpath(dirname(__FILE__)) . "/Json/" . $path;
        $shipmentJsonData = file_get_contents($path);

        $response = $this->shipmentPrerequistiesData($shipmentJsonData);

        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0][self::STATUS],
            self::ISSUE001
        );

        $this->i95devServerRepo->syncMQtoMagento();

        $collection = $this->getInboundMqData();
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $collection[0][self::STATUS],
            self::ISSUE002
        );

        $errorMsg = $this->getErrorData($collection[0]["error_id"]);
        $this->assertSame($expectedString, $errorMsg[0]['msg']);
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Hrusikesh Manna
     */
    public function testReverseShipmentWithoutTargetShipmentId()
    {
        $this->checkInvalidFields(
            "shipmentWithoutTargetShipmentId.json",
            "i95dev_shipment_order_shipmentid"
        );
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Hrusieksh Manna
     */
    public function testReverseShipmentWithoutShipmentItemEntity()
    {
        $this->checkInvalidFields(
            "shipmentWithoutShipmentItemEntity.json",
            "i95dev_shipment_itemNotExist"
        );
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Hrusikesh Manna
     */
    public function testReversePartialShipment()
    {
        $file = "/Json/partialShipmentPullData.json";
        $this->processTestCase($file);
        $targetOrderStatus = $this->dummyData->getTargetOrderStatus(1026);
        $this->assertEquals('Partially Shipped', $targetOrderStatus, self::ORDER_STATUS);
    }

    /**
     * @param $fileName
     *
     * @return bool
     */
    public function processTestCase($fileName)
    {
        $path = realpath(dirname(__FILE__)) . $fileName;
        $shipmentJsonData = file_get_contents($path);
        $response = $this->shipmentPrerequistiesData($shipmentJsonData);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0][self::STATUS],
            self::ISSUE001
        );
        $this->i95devServerRepo->syncMQtoMagento();
        $collection = $this->getInboundMqData();
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0][self::STATUS],
            self::ISSUE002
        );
        $collection = $this->customSalesShipment->create()
            ->getCollection()->addFieldToFilter('target_shipment_id', 102195);
        $this->assertEquals(1, $collection->getSize());
        $data = $collection->getData();
        $this->assertNotNull($data[0]['source_shipment_id']);
        return true;
    }
}
