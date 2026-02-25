<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Order;

/**
 * Class responsible for all success cases of reverse order work flow
 */
class OrderReverseTest extends \PHPUnit\Framework\TestCase
{
    public const ORDST3500 = "ORDST3500";
    public const FREESHIPPING = "freeshipping_freeshipping";
    public const FLEXDECK = "FLEXDECK";
    public const CHECKMO = "checkmo";
    public const ID = "654321";

    /**
     * @author Divya Koona
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->dummyData = $objectManager->create(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );
        $this->order = $objectManager->create(
            \Magento\Sales\Model\OrderFactory::class
        );
        $this->orderTestGenericHelper = $objectManager->create(
            OrderGenericHelperTest::class
        );
    }

    /**
     * Testcase for Sales order creation from ERP to magento with header level discount amount.
     *
     * @magentoDbIsolation   enabled
     * @magentoAppIsolation  enabled
     * @magentoCache         all disabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @magentoConfigFixture current_store carriers/flatrate/active 1
     * @magentoConfigFixture current_store payment/checkmo/active 1
     * @author               Debashis S. Gopal
     */
    public function testReverseOrderWithHeaderLevelDiscount()
    {
        $file = "/Json/OrderWithDiscount.json";
        $getOrder = $this->processAndGetOrder($file);
        $this->compareOrderTotals($getOrder, 215, -40, 55, 'flatrate_flatrate', self::CHECKMO, '24124');
        foreach ($getOrder->getAllItems() as $eachItem) {
            if ($eachItem->getSku() == self::FLEXDECK) {
                $this->compareOrderItems($eachItem, 10, 100, 2, 40);
            }
        }
    }

    /**
     * Testcase for Sales order creation from ERP to magento with header level discount amount,
     * and item level discount.
     *
     * @magentoDbIsolation   enabled
     * @magentoCache         all disabled
     * @magentoAppIsolation  enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @magentoConfigFixture current_store carriers/flatrate/active 1
     * @magentoConfigFixture current_store payment/checkmo/active 1
     * @author               Divya Koona
     */
    public function testReverseOrderWithLineLevelDiscount()
    {
        $file = "/Json/OrderWithLineLevelDiscount.json";
        $this->dummyData->createSingleSimpleProduct('FLEXDECK2');
        $getOrder = $this->processAndGetOrder($file);

        $this->compareOrderTotals($getOrder, 175, -50, 5, 'flatrate_flatrate', self::CHECKMO, '24124');
        foreach ($getOrder->getAllItems() as $eachItem) {
            if ($eachItem->getSku() == self::FLEXDECK) {
                $this->compareOrderItems($eachItem, 10, 100, 2, 40);
            } elseif ($eachItem->getSku() == 'FLEXDECK2') {
                $this->compareOrderItems($eachItem, 10, 10, 2, 10);
            }
        }
    }

    /**
     * Testcase for Sales order creation from ERP to magento by providing item level custom prices.
     *
     * @magentoDbIsolation   enabled
     * @magentoAppIsolation  enabled
     * @magentoCache         all disabled
     * @magentoConfigFixture current_store carriers/freeshipping/active 1
     * @magentoConfigFixture current_store payment/checkmo/active 1
     * @author               Debashis S. Gopal
     */
    public function testReverseOrderWithMarkDownPrice()
    {
        $file = "/Json/OrderWithMarkDownPrice.json";
        $getOrder = $this->processAndGetOrder($file);
        $this->compareOrderTotals($getOrder, 180, 0, 0, self::FREESHIPPING, self::CHECKMO, self::ID);
        foreach ($getOrder->getAllItems() as $eachItem) {
            if ($eachItem->getSku() == self::FLEXDECK) {
                $this->compareOrderItems($eachItem, 10, 90, 2, 0);
            }
        }
    }

    /**
     * Testcase for Zero subtotal order creation from ERP to Magento.
     *
     * @magentoDbIsolation   enabled
     * @magentoAppIsolation  enabled
     * @magentoConfigFixture current_store carriers/freeshipping/active 1
     * @magentoConfigFixture current_store payment/checkmo/active 1
     * @author               Debashis S. Gopal
     */
    public function testReverseZeroSubtotalOrder()
    {
        $file = "/Json/ZeroSubtotalOrder.json";
        $getOrder = $this->processAndGetOrder($file);
        $this->compareOrderTotals($getOrder, 0, 0, 0, self::FREESHIPPING, self::CHECKMO, self::ID);
        foreach ($getOrder->getAllItems() as $eachItem) {
            if ($eachItem->getSku() == self::FLEXDECK) {
                $this->compareOrderItems($eachItem, 10, 0, 1, 0);
            }
        }
    }

    /**
     * Testcase for Sales order creation from ERP to magento,
     * by providing item level custom prices along with discoun amount.
     *
     * @magentoDbIsolation   enabled
     * @magentoAppIsolation  enabled
     * @magentoConfigFixture current_store carriers/freeshipping/active 1
     * @magentoConfigFixture current_store payment/checkmo/active 1
     * @author               Divya Koona
     */
    public function testReverseOrderWithItemCustomPriceAndDiscont()
    {
        $file = "/Json/OrderWithItemCustomPriceAndDiscont.json";
        $getOrder = $this->processAndGetOrder($file);
        $this->compareOrderTotals($getOrder, 20, -4, 0, self::FREESHIPPING, self::CHECKMO, self::ID);
        foreach ($getOrder->getAllItems() as $eachItem) {
            if ($eachItem->getSku() == self::FLEXDECK) {
                $this->compareOrderItems($eachItem, 10, 8, 3, 4);
            }
        }
    }

    /**
     * test order sync with mark down price, free shipping method and cash payment method
     *
     * @magentoDbIsolation   enabled
     * @magentoAppIsolation  enabled
     * @magentoConfigFixture current_store carriers/freeshipping/active 1
     * @magentoConfigFixture current_store payment/checkmo/active 1
     * @author               Divya Koona
     */
    public function testReverseOrderWithDuplecateItems()
    {
        $file = "/Json/OrderWithDuplecateItems.json";
        $getOrder = $this->processAndGetOrder($file);
        $this->compareOrderTotals($getOrder, 30, 0, 0, self::FREESHIPPING, self::CHECKMO, self::ID);
        foreach ($getOrder->getAllItems() as $eachItem) {
            if ($eachItem->getSku() == self::FLEXDECK) {
                $this->compareOrderItems($eachItem, 10, 10, 3, 0);
            }
        }
    }

    /**
     * Create order entry in Inbound MQ, process from MQ to Magento and then return Magento order object
     *
     * @param  string $file
     * @return object
     * @author Divya Koona
     */
    public function processAndGetOrder($file)
    {
        $this->dummyData->createCustomer();
        $this->dummyData->createSingleSimpleProduct(self::FLEXDECK);
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, 'C00011');

        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0]['status'],
            "Issue came in saving order from mq to magento"
        );
        return $this->order->create()->loadByIncrementId($collection[0]['magento_id']);
    }

    /**
     * Get target order id from custom table based source order id
     *
     * @param  string $incrementId
     * @return string
     * @author Divya Koona
     */
    public function getTargetOrderId($incrementId)
    {
        $loadCustomOrder = $this->dummyData->customSalesOrder->create()->load($incrementId, 'source_order_id');
        return $loadCustomOrder->getTargetOrderId();
    }

    /**
     * Get check number from custom table based on Magento order id
     *
     * @param  string $incrementId
     * @return string
     * @author Divya Koona
     */
    public function getCheckNumber($incrementId)
    {
        $checkNumberModel = $this->dummyData->chequeNumberModel->load($incrementId, 'source_order_id');
        return $checkNumberModel->getTargetChequeNumber();
    }

    /**
     * Compare created order values with the provided values
     *
     * @param  object $getOrder
     * @param  float  $grandTotal
     * @param  float  $discountAmount
     * @param  float  $shippingAmount
     * @param  string $shippingMethod
     * @param  string $paymentMethod
     * @param  string $chkNum
     * @author Divya Koona
     */
    public function compareOrderTotals(
        $getOrder,
        $grandTotal,
        $discountAmount,
        $shippingAmount,
        $shippingMethod,
        $paymentMethod,
        $chkNum = null
    ) {
        $targetOrderId = $this->getTargetOrderId($getOrder->getIncrementId());
        $this->assertEquals(
            self::ORDST3500,
            $targetOrderId,
            "Issue came in saving target order id from mq to magento"
        );
        $this->assertEquals(
            $grandTotal,
            $getOrder->getGrandTotal(),
            "Issue came in saving order grandtotal from mq to magento"
        );
        $this->assertEquals(
            $discountAmount,
            $getOrder->getBaseDiscountAmount(),
            "Issue came in saving order discount amount from mq to magento"
        );
        $this->assertEquals(
            $shippingAmount,
            $getOrder->getBaseShippingAmount(),
            "Issue came in saving order shipping amount from mq to magento"
        );
        $this->assertEquals(
            $shippingMethod,
            $getOrder->getShippingMethod(),
            "Issue came in saving order shipping method from mq to magento"
        );
        $this->assertEquals(
            $paymentMethod,
            $getOrder->getPayment()->getMethod(),
            "Issue came in saving order payment method from mq to magento"
        );
        if ($paymentMethod == self::CHECKMO) {
            $checkNumber = $this->getCheckNumber($getOrder->getIncrementId());
            $this->assertEquals(
                $chkNum,
                $checkNumber,
                "Issue came in saving order check number from mq to magento"
            );
        }
    }

    /**
     * Compare order item values
     *
     * @param  object $eachItem
     * @param  float  $originalPrice
     * @param  float  $price
     * @param  int    $qty
     * @param  float  $discountAmount
     * @author Divya Koona
     */
    public function compareOrderItems(
        $eachItem,
        $originalPrice,
        $price,
        $qty,
        $discountAmount
    ) {
        $this->assertEquals(
            $originalPrice,
            $eachItem->getBaseOriginalPrice(),
            "Issue came in saving order item original price from mq to magento"
        );
        $this->assertEquals(
            $price,
            $eachItem->getBasePrice(),
            "Issue came in saving order item price from mq to magento"
        );
        $this->assertEquals(
            $qty,
            $eachItem->getQtyOrdered(),
            "Issue came in saving order item quantity from mq to magento"
        );
        $this->assertEquals(
            $discountAmount,
            $eachItem->getBaseDiscountAmount(),
            "Issue came in saving order item discount amount from mq to magento"
        );
    }

    /**
     * test reverse syn order with origin
     *
     * @magentoDbIsolation enabled
     * @author             kavya koona
     */
    public function testReverseOrderWithOrigin()
    {
        $this->dummyData->createCustomer();
        $this->dummyData->createSingleSimpleProduct(self::FLEXDECK);
        $file = "/Json/OrderReverse.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $this->orderTestGenericHelper->processData($data, self::ORDST3500, 'C00011');
        $origin = $this->dummyData->customer->getData('origin');
        $this->assertEquals('website', $origin, "Origin is not syincing properly");
    }

    /**
     * test order sync with inactive payment method
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_store payment/checkmo/active 0
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @author               Divya Koona
     */
    public function testReverseOrderWithInactivePaymentMethod()
    {
        $this->dummyData->createCustomer();
        $this->dummyData->createSingleSimpleProduct(self::FLEXDECK);
        $file = "/Json/OrderWithInactivePaymentMethod.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, "C00011");
        $errorData = $this->dummyData->getErrorData($collection[0]["error_id"]);
        $this->assertSame("i95dev_order_022", $errorData);
    }
}
