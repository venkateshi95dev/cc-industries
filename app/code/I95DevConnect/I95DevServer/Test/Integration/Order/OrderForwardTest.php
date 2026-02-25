<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Order;

/**
 * Class responsible for forward order work flow
 */
class OrderForwardTest extends \PHPUnit\Framework\TestCase
{
    public const MAGENTO_ID = "magento_id";
    public const REFERENCE = "reference";
    public const WRONG_RESPONSE = "Wrong reference in response";
    public const ODA = "orderDocumentAmount";
    public const ODA_RESPONSE = "Wrong orderDocumentAmount in response";
    public const COMMENTS = "comments";
    public const EMAIL_ID = "hrusikesh.manna@jiva.com";

    /**
     * Get outbound message queue collection data by magento_id
     *
     * @param  $magentoId
     * @return array
     * @author Debashis S. Gopal
     */
    public function getOutbountMqData($magentoId)
    {
        $collections = $this->magentoMessageQueue->getCollection()
            ->addFieldToFilter('entity_code', 'Order')
            ->addFieldToFilter(self::MAGENTO_ID, $magentoId);
        return $collections->getData();
    }

    /**
     * Testcase for order creation from magento to ERP, With equal billing and shipping address
     *
     * @magentoDbIsolation  enabled
     * @magentoCache        all disabled
     * @magentoAppIsolation enabled
     * @author              Sravani Polu
     */
    public function testOrderWithSameBillingAndShippingAddress()
    {
        $this->orderPrerequistiesData();
        $responseData = $this->getOrdersInfo($this->order->getIncrementId());
        $this->assertEquals($responseData[self::REFERENCE], self::EMAIL_ID, self::WRONG_RESPONSE);
        $this->assertEquals($responseData[self::ODA], 40, self::ODA_RESPONSE); // NOSONAR
    }

    /**
     * Create order in magento
     *
     * @param array $requestData
     *
     * @author Sravani Polu
     */
    public function orderPrerequistiesData($requestData = [])
    {
        $this->dummyData->createCustomer();
        $this->dummyData->createSingleSimpleProduct(1000);
        $this->order = $this->dummyData->createSingleOrder(null, null, $requestData);
    }

    /**
     * Call getOrdersInfo service and checks required assertion.
     *
     * @param  string $orderId
     * @return array
     * @author Debashis S. Gopal
     */
    public function getOrdersInfo($orderId)
    {
        $responseData = $this->dummyData->getEntityInfoData($orderId, "order", "getOrdersInfo");

        $this->validateMagentoResponseData($responseData);
        return $responseData;
    }

    /**
     * Validate fields in magento response data
     * Fields: sourceId, shippingMethod, billingAddress, orderItems, payment, origin
     *
     * @param  array $responseData
     * @return void
     */
    public function validateMagentoResponseData($responseData)
    {
        $orderId = $this->order->getIncrementId();
        $this->assertEquals($responseData['sourceId'], $orderId, 'Wrong sourceId in response');
        $this->assertEquals($responseData['shippingMethod'], 'flatrate_flatrate', 'Wrong shippingMethod in response');
        $this->assertNotEmpty($responseData['shippingAddress']);
        $this->assertNotEmpty($responseData['billingAddress']);
        $this->assertNotEmpty($responseData['orderItems']);
        $this->assertNotEmpty($responseData['payment']);
        $this->assertEquals($responseData['origin'], 'website', "Wrong value set for origin");
    }

    /**
     * Testcase for order creation from magento to ERP, With different billing and shipping address
     *
     * @magentoDbIsolation  enabled
     * @magentoCache        all disabled
     * @magentoAppIsolation enabled
     * @author              Sravani Polu
     */
    public function testOrderWithDifferentBillingAndShippingAddress()
    {
        $this->orderPrerequistiesData(['isDifferentAddress' => 1]);
        $responseData = $this->getOrdersInfo($this->order->getIncrementId());
        $this->assertNotEquals($responseData['shippingAddress'], $responseData['billingAddress']);
        $this->assertEquals($responseData[self::REFERENCE], self::EMAIL_ID, self::WRONG_RESPONSE);
        $this->assertEquals($responseData[self::ODA], 40, self::ODA_RESPONSE); // NOSONAR
    }

    /**
     * Testcase for order creation from magento to ERP With Custom Price.
     * Validated Fields: orderDocumentAmount, specialPrice(in order line item level), price(in order line item level).
     *
     * @magentoCache        all disabled
     * @magentoAppIsolation enabled
     * @magentoDbIsolation  enabled
     * @author              Debashis S. Gopal
     */
    public function testGetOrderWithCustomPrice()
    {
        $this->orderPrerequistiesData(['custom_price' => 8]);
        $responseData = $this->getOrdersInfo($this->order->getIncrementId());
        $this->assertEquals(
            $responseData[self::REFERENCE],
            self::EMAIL_ID,
            self::WRONG_RESPONSE
        );
        $this->assertEquals(
            $responseData[self::ODA],
            36, // NOSONAR
            self::ODA_RESPONSE
        );
        $orderItem = $responseData['orderItems'][0];
        $this->assertNotEquals(
            $orderItem['specialPrice'],
            $orderItem['price'],
            "price and Specialprice must be different"
        );
        $this->assertEquals($orderItem['specialPrice'], 8, 'Wrong value for specialPrice'); // NOSONAR
    }

    /**
     * Testcase for order creation from magento to ERP With Comment.
     * Validated Field: comments
     *
     * @magentoDbIsolation  enabled
     * @magentoCache        all disabled
     * @magentoAppIsolation enabled
     * @author              Debashis S. Gopal
     */
    public function testGetOrderWithComment()
    {
        $this->orderPrerequistiesData(['comment' => 'Test case comment']);
        $responseData = $this->getOrdersInfo($this->order->getIncrementId());
        $this->assertEquals(
            $responseData[self::REFERENCE],
            self::EMAIL_ID,
            self::WRONG_RESPONSE
        );
        $this->assertNotEmpty($responseData[self::COMMENTS]);
        $this->assertEquals(
            $responseData[self::COMMENTS][0]['comment'], // NOSONAR
            'Test case comment', // NOSONAR
            'Wrong comment'
        ); // NOSONAR
        $this->assertEquals(
            $responseData[self::COMMENTS][0]['source'],
            'admin', // NOSONAR
            'Wrong source'
        );
    }

    /**
     * Testcase for guest order creation from magento to ERP
     * Validated Fields: isGuest(in customer array)
     *
     * @magentoDbIsolation enabled
     * @author             Sravani Polu
     */
    public function testGetGuestOrder()
    {
        $this->guestOrderData();
        $responseData = $this->getOrdersInfo($this->order->getIncrementId());
        $this->assertEquals(
            $responseData[self::REFERENCE], // NOSONAR
            'jbutt@gmail.com', // NOSONAR
            self::WRONG_RESPONSE
        );
        $this->assertEmpty($responseData['targetCustomerId']);
        $this->assertNotEmpty($responseData['customer']);
        $this->assertEquals(
            $responseData['customer']['isGuest'], // NOSONAR
            true, // NOSONAR
            "isGuest field should be true"
        );
    }

    /**
     * Create Guest order in magento
     *
     * @return void
     * @author Debashis S. Gopal
     */
    public function guestOrderData()
    {
        $this->dummyData->createSingleSimpleProduct(1000);
        $this->order = $this->dummyData->createGuestOrder('');
    }

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->orderModel = $objectManager->create(
            \Magento\Sales\Model\Order::class
        );
        $this->stockRegistry = $objectManager->create(
            \Magento\CatalogInventory\Api\StockRegistryInterface::class
        );
        $this->customSalesOrder = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\SalesOrderFactory::class
        );
        $this->magentoMQ = $objectManager->create(
            \I95DevConnect\MessageQueue\Api\Data\I95DevMagMQInterfaceFactory::class
        );
        $this->magentoMQRepo = $objectManager->create(
            \I95DevConnect\MessageQueue\Api\I95DevMagMQRepositoryInterfaceFactory::class
        );
        $this->i95devServerRepo = $objectManager->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->magentoMessageQueue = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\MessageQueue\Model\I95DevMagMQRepository::class
        );
        $this->dummyData = $objectManager->create(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );
    }
}
