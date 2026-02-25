<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Invoice;

/**
 * invoice test case for reverse flows
 */
class InvoiceReverseTest extends \PHPUnit\Framework\TestCase
{
    public const TARGET_INVOICE_ID_STR = "target_invoice_id";
    public const SOURCE_INVOICE_ID_STR = "source_invoice_id";
    public const TARGET_ADDRESS_ID_STR = "target_address_id";
    public const ERROR_ID_STR = "error_id";
    public const STATUS_STR = "status";
    public const SAVING_MQ_TO_MAGENTO_STR = "Issue came in saving invoice from mq to magento";
    public const SAVING_MQ_STR = "Issue came in saving invoice to messagequeue";
    public const TARGET_ID_STR = "targetId";
    public const TARGET_ORDER_MSG_STR = 'target_order_status not updated.';
    public const INVOICED_STR = 'Invoiced';

    /**
     * @var $customSalesInvoice
     */
    public $customSalesInvoice;
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
     * @var $dummyData
     */
    public $dummyData;

    /**
     * @author Arushi Bansal
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
        $this->customSalesInvoice = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\SalesInvoiceFactory::class
        );
        $this->invoiceOrder = $objectManager->create(
            \Magento\Sales\Api\InvoiceOrderInterfaceFactory::class
        );
        $this->invoiceItem = $objectManager->create(
            \Magento\Sales\Api\Data\InvoiceItemCreationInterfaceFactory::class
        );
        $this->invoiceRepo = $objectManager->create(
            \Magento\Sales\Api\InvoiceRepositoryInterfaceFactory::class
        );
        $this->customInvoice = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\SalesInvoiceFactory::class
        );
        $this->baseHelperData = $objectManager->create(
            \I95DevConnect\MessageQueue\Helper\Data::class
        );
        $this->scopeConfig = $objectManager->create(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        );
        $this->dummyData = $objectManager->get(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );
        $this->invoice = $objectManager->get(
            \Magento\Sales\Model\Order\Invoice::class
        );
        $this->timezone = $objectManager->get(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
        );
        $this->erpMessageQueue = $objectManager->get(
            \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository::class
        );
    }

    /**
     * prepare invoice data for test cases of simple product
     *
     * @param  string $invoiceJsonData
     * @return object
     * @author Arushi Bansal
     */
    public function invoicePrerequistiesData($invoiceJsonData)
    {
        $this->dummyData->createCustomer();
        $this->dummyData->createSingleSimpleProduct(1000);
        $this->dummyData->addInventory();
        $this->order = $this->dummyData->createSingleOrder(1025);
        $this->i95devServerRepo->serviceMethod("createInvoiceList", $invoiceJsonData);

        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('ref_name', 1025)
            ->getData();
    }

    /**
     * @magentoDbIsolation enabled
     *
     * @param $path
     *
     * @return Object
     * @author Arushi Bansal
     */
    public function initialInvoiceTest($path)
    {
        $path = realpath(dirname(__FILE__)) . "/Json/" . $path;
        $invoiceJsonData = file_get_contents($path);

        $response = $this->invoicePrerequistiesData($invoiceJsonData);

        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0][self::STATUS_STR],
            self::SAVING_MQ_STR
        );

        $this->i95devServerRepo->syncMQtoMagento();

        $collection = $this->dummyData->getInboundMqData(1025);

        if ($collection[0][self::STATUS_STR] == \I95DevConnect\MessageQueue\Helper\Data::ERROR) {
            return $collection;
        } else {
            return $this->invoiceSuccess($collection);
        }
    }

    /**
     * @param  $collection
     * @return object
     */
    public function invoiceSuccess($collection, $invoiceStatus = "Invoiced")
    {
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0][self::STATUS_STR],
            self::SAVING_MQ_TO_MAGENTO_STR
        );
        $collection = $this->customSalesInvoice->create()
            ->getCollection()->addFieldToFilter(self::TARGET_INVOICE_ID_STR, 103036);
        $this->assertEquals(1, $collection->getSize());
        $data = $collection->getData();
        $this->assertNotNull($data[0][self::SOURCE_INVOICE_ID_STR]);
        $targetOrderStatus = $this->dummyData->getTargetOrderStatus(1025);
        $this->assertEquals($invoiceStatus, $targetOrderStatus, self::TARGET_ORDER_MSG_STR);

        return $data;
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     */
    public function testInvoiceComments()
    {
        $this->initialInvoiceTest("InvoicePullData.json");
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Arushi Bansal
     */
    public function testReverseSplitInvoice()
    {

        $path = realpath(dirname(__FILE__)) . "/Json/InvoicePartialSimpleProd.json";
        $invoiceJsonData = file_get_contents($path);

        $response = $this->invoicePrerequistiesData($invoiceJsonData);

        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0][self::STATUS_STR],
            self::SAVING_MQ_STR
        );

        $this->i95devServerRepo->syncMQtoMagento();

        $collection = $this->dummyData->getInboundMqData(1025);

        $this->invoiceSuccess($collection, "Partially Invoiced");
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Arushi Bansal
     */
    public function testReverseInvoiceWithoutTargetOrderId()
    {
        $collection = $this->initialInvoiceTest("InvoiceWithoutTargetOrderId.json");
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $collection[0][self::STATUS_STR]
        );

        $errorMsg = $this->dummyData->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("i95dev_empty_targetOrderId", $errorMsg);
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Arushi Bansal
     */
    public function testReverseInvoiceWithoutTargetInvoiceId()
    {
        $collection = $this->initialInvoiceTest("InvoiceWithoutTargetInvoiceId.json");
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $collection[0][self::STATUS_STR],
            self::SAVING_MQ_TO_MAGENTO_STR
        );

        $errorMsg = $this->dummyData->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("i95dev_empty_targetInvoiceId", $errorMsg);
    }

    /**
     * generic function for checking invalid fields
     *
     * @author Arushi Bansal
     * @param  $path
     * @param  $expectedString
     */
    public function checkInvalidFields($path, $expectedString)
    {
        $path = realpath(dirname(__FILE__)) . "/Json/" . $path;
        $invoiceJsonData = file_get_contents($path);

        $response = $this->invoicePrerequistiesData($invoiceJsonData);

        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0][self::STATUS_STR],
            self::SAVING_MQ_STR
        );

        $this->i95devServerRepo->syncMQtoMagento();

        $collection = $this->dummyData->getInboundMqData(1025);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $collection[0][self::STATUS_STR],
            self::SAVING_MQ_TO_MAGENTO_STR
        );

        $errorMsg = $this->dummyData->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertStringStartsWith($expectedString, $errorMsg);
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Arushi Bansal
     */
    public function testReverseInvoiceWithoutInvoiceItemEntity()
    {
        $this->checkInvalidFields(
            "InvoiceWithoutInvoiceItemEntity.json",
            "i95dev_invoice_itemNotExist"
        );
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Arushi Bansal
     */
    public function testReverseInvoiceWithoutOrderItemId()
    {
        $this->checkInvalidFields(
            "InvoiceWithoutOrderItemId.json",
            "i95dev_empty_invoiceOrderItemId"
        );
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Arushi Bansal
     */
    public function testReverseInvoiceWithoutQuantityToInvoice()
    {
        $this->checkInvalidFields(
            "InvoiceWithoutQuantityToInvoice.json",
            "i95dev_empty_qtyToInvoice"
        );
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Arushi Bansal
     */
    public function testReverseInvalidInvoiceItem()
    {
        $this->checkInvalidFields(
            "InvoiceInvalidInvoiceItem.json",
            "i95dev_invalid_invoice_item"
        );
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Arushi Bansal
     */
    public function testReverseInvoiceOrderNotExists()
    {
        $this->checkInvalidFields("InvoiceOrderNotExists.json", "i95dev_order_not_exists");
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Kavya Koona
     */
    public function testInvoiceWithCreatedDate()
    {
        $collection = $this->initialInvoiceTest("InvoiceWithCreatedDate.json");
        $id = $collection[0][self::SOURCE_INVOICE_ID_STR];
        $targetOrderStatus = $this->dummyData->getTargetOrderStatus(1025);
        $this->assertEquals(self::INVOICED_STR, $targetOrderStatus, self::TARGET_ORDER_MSG_STR);
        $createdDate = $this->timezone->convertConfigTimeToUtc("8/29/2018 12:00:00 AM");
        $createdAt = $this->invoice->load($id)->getData('created_at');
        $this->assertEquals($createdDate, $createdAt, "createdDate is not set properly");
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Arushi Bansal
     */
    public function testInvoiceAlreadyExists()
    {
        $this->doInvoice([self::TARGET_ID_STR => 103036]);
        $this->i95devServerRepo->syncMQtoMagento();
        $collection = $this->dummyData->getInboundMqData(1025);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $collection[0][self::STATUS_STR],
            self::SAVING_MQ_TO_MAGENTO_STR
        );
        $errorMsg = $this->dummyData->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("invoice_already_sync", $errorMsg);
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Arushi Bansal
     */
    public function testInvoiceAlreadyExistsInMagento()
    {
        $result = $this->doInvoice();
        $this->i95devServerRepo->syncMQtoMagento();
        $collection = $this->dummyData->getInboundMqData(1025);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0][self::STATUS_STR],
            self::SAVING_MQ_TO_MAGENTO_STR
        );
        $collection = $this->customSalesInvoice->create()
            ->getCollection()->addFieldToFilter(self::TARGET_INVOICE_ID_STR, 103036);
        $targetOrderStatus = $this->dummyData->getTargetOrderStatus(1025);
        $this->assertEquals(self::INVOICED_STR, $targetOrderStatus, self::TARGET_ORDER_MSG_STR);
        $this->assertEquals(1, $collection->getSize());
        $data = $collection->getData();
        $this->assertNotNull($data[0][self::SOURCE_INVOICE_ID_STR]);
        $this->assertEquals($data[0][self::SOURCE_INVOICE_ID_STR], $result->getIncrementId());
    }

    /**
     * @magentoDbIsolation  enabled
     * @magentoAppIsolation enabled
     * @author              Arushi Bansal
     */
    public function testInvoiceAlreadyExistsInMagentoAndCustomInvoiceTable()
    {
        $result = $this->doInvoice(['create_custom_invoice' => 1]);

        $this->i95devServerRepo->syncMQtoMagento();
        $collection = $this->dummyData->getInboundMqData(1025);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0][self::STATUS_STR],
            self::SAVING_MQ_TO_MAGENTO_STR
        );
        $collection = $this->customSalesInvoice->create()
            ->getCollection()->addFieldToFilter(self::TARGET_INVOICE_ID_STR, 103036);
        $this->assertEquals(1, $collection->getSize());
        $data = $collection->getData();
        $targetOrderStatus = $this->dummyData->getTargetOrderStatus(1025);
        $this->assertEquals(self::INVOICED_STR, $targetOrderStatus, self::TARGET_ORDER_MSG_STR);
        $this->assertNotNull($data[0][self::SOURCE_INVOICE_ID_STR]);
        $this->assertEquals($data[0][self::SOURCE_INVOICE_ID_STR], $result->getIncrementId());
    }

    /**
     * @param array $requestData
     *
     * @return mixed
     */
    public function doInvoice($requestData = [])
    {
        $path = realpath(dirname(__FILE__)) . "/Json/InvoiceAuthorizePaymentMethod.json";
        $invoiceJsonData = file_get_contents($path);
        $response = $this->invoicePrerequistiesData($invoiceJsonData);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0][self::STATUS_STR],
            self::SAVING_MQ_STR
        );

        foreach ($this->order->getItems() as $item) {
            $order_item_id = $item->getItemId();
        }
        $item = $this->invoiceItem->create();
        $item->setOrderItemId($order_item_id);
        $item->setQty(2);
        $itemArr[] = $item;
        $result = $this->invoiceOrder->create()->execute(
            $this->order->getId(),
            false,
            $itemArr,
            false,
            false
        );
        $result = $this->invoiceRepo->create()->get($result);
        if (!empty($requestData)) {
            $customInvoiceData = $this->customInvoice->create();
            $customInvoiceData->setSourceInvoiceId($result->getIncrementId());
            $customInvoiceData->setCreatedDt($this->baseHelperData->date->gmtDate());
            $customInvoiceData->setUpdatedDt($this->baseHelperData->date->gmtDate());
            if (isset($requestData[self::TARGET_ID_STR])) {
                $customInvoiceData->setTargetInvoiceId($requestData[self::TARGET_ID_STR]);
                $customInvoiceData->setTargetInvoicedQty(2);
                $customInvoiceData->setCreatedDt($this->baseHelperData->date->gmtDate());
                $customInvoiceData->setUpdatedDt($this->baseHelperData->date->gmtDate());
                $customInvoiceData->setUpdateBy('ERP');
                $customInvoiceData->setTargetInvoicedQty(2);
            }
            $customInvoiceData->save();
        }
        return $result;
    }
}
