<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Order;

/**
 * Class responsible for error test cases in reverse order work flow
 */
class OrderReverseErrorCaseTest extends \PHPUnit\Framework\TestCase
{
    public const ORDST3500 = "ORDST3500";
    public const C00011 = "C00011";
    public const FLEXDECK = "FLEXDECK";
    public const ERROR_ID = "error_id";

    /**
     * Test order sync with not existing target order id in Magento
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseOrderWithExistingTargetId()
    {
        $file = "/Json/OrderWithExistingTargetId.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $this->orderTestGenericHelper->orderPrerequistiesData();
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, 'GPCUST128');

        $this->assertEquals(4, $collection[0]["status"]);
    }

    /**
     * test order sync with not existing customer in Magento
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseOrderWithNotExistingCustomer()
    {
        $file = "/Json/OrderWithNotExistingCustomer.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, 'GPCUST128');
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_order_030", $errorData);
    }

    /**
     * test order sync without providing billing address
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseOrderWithEmptyBillingAddress()
    {
        $this->dummyData->createCustomer("GPCUST128", "PRIMARY");
        $file = "/Json/OrderWithEmptyBillingAddress.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_order_033", $errorData);
    }

    /**
     * test order sync without providing required fields in billing address
     *
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @magentoDbIsolation   enabled
     * @author               Divya Koona
     */
    public function testReverseOrderBillingAddressWithoutRequiredFields()
    {
        $this->dummyData->createCustomer();
        $file = "/Json/OrderBillingAddressWithoutRequiredFields.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $expectedErrorMsg = "i95dev_addr_002, i95dev_addr_003, i95dev_addr_004, i95dev_addr_006" .
            ", i95dev_addr_007, i95dev_addr_008, i95dev_addr_009";

        $this->assertSame($expectedErrorMsg, $errorData, "Wrong error message");
    }

    /**
     * test order sync with empty target address id of billing address
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component GP
     * @author               Divya Koona
     */
    public function testReverseOrderBillingAddressWithEmptyBiilingData()
    {
        $this->dummyData->createCustomer();
        $file = "/Json/OrderBillingAddressWithEmptyTargetAddressId.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);

        $this->assertSame(
            "i95dev_addr_002, i95dev_addr_003, i95dev_addr_004, i95dev_addr_006, i95dev_addr_007, 
            i95dev_addr_008, i95dev_addr_009",
            $errorData
        );
    }

    /**
     * test order sync with empty target address id of billing address
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component AX
     * @author               Divya Koona
     */
    public function testReverseOrderBillingAddressWithEmptyTargetAddressIdForAX()
    {
        $this->dummyData->createCustomer();
        $file = "/Json/OrderBillingAddressWithEmptyTargetAddressId.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);

        $this->assertSame("i95dev_addr_001", $errorData);
    }

    /**
     * test order sync without providing required fields in shipping address
     *
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @magentoDbIsolation   enabled
     * @author               Divya Koona
     */
    public function testReverseOrderShippingAddressWithoutRequiredFields()
    {
        $this->dummyData->createCustomer();
        $file = "/Json/OrderShippingAddressWithoutRequiredFields.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $expectedError = "i95dev_addr_002, i95dev_addr_003, i95dev_addr_004, " .
            "i95dev_addr_006, i95dev_addr_007, i95dev_addr_008, i95dev_addr_009";
        $this->assertSame($expectedError, $errorData, "Wrong error message");
    }

    /**
     * test order sync with invalid region of shipping address
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @author               Divya Koona
     */
    public function testReverseOrderWithInvalidRegionShippingAddress()
    {
        $this->dummyData->createCustomer();
        $file = "/Json/OrderWithInvalidRegionShippingAddress.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_addr_014", $errorData);
    }

    /**
     * test order sync with invalid region of billing address
     *
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @magentoDbIsolation   enabled
     * @author               Divya Koona
     */
    public function testReverseOrderWithInvalidRegionBillingAddress()
    {
        $this->dummyData->createCustomer();
        $file = "/Json/OrderWithInvalidRegionBillingAddress.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_addr_014", $errorData);
    }

    /**
     * test order sync with empty shipping method
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseOrderWithEmptyShippingMethod()
    {
        $this->dummyData->createCustomer();
        $file = "/Json/OrderWithEmptyShippingMethod.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_order_004", $errorData);
    }

    /**
     * test order sync with Inactive shipping method
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @magentoConfigFixture current_store carriers/flatrate/active 0
     * @magentoConfigFixture current_store carriers/freeshipping/active 1
     * @author               Divya Koona
     */
    public function testReverseOrderWithInactiveShippingMethod()
    {
        $this->dummyData->createCustomer();
        $this->dummyData->createSingleSimpleProduct(self::FLEXDECK);
        $file = "/Json/OrderWithInactiveShippingMethod.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_order_021", $errorData);
    }

    /**
     * test order sync with all inactive shipping methods
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @magentoConfigFixture current_store carriers/flatrate/active 0
     * @author               Divya Koona
     */
    public function testReverseOrderWithAllInactiveShippingMethods()
    {
        $this->dummyData->createCustomer();
        $file = "/Json/OrderWithInactiveAllShippingMethods.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_quote_all_shippingMethod_active", $errorData);
    }

    /**
     * test order sync without item SKU
     *
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @magentoDbIsolation   enabled
     * @author               Divya Koona
     */
    public function testReverseOrderWithoutItemSku()
    {
        $this->dummyData->createCustomer();
        $file = "/Json/OrderWithoutItemSku.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_order_032", $errorData);
    }

    /**
     * test order sync with empty item SKU
     *
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @magentoDbIsolation   enabled
     * @author               Divya Koona
     */
    public function testReverseOrderWithEmptyItemSku()
    {
        $this->dummyData->createCustomer();
        $file = "/Json/OrderWithEmptyItemSku.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_order_032", $errorData);
    }

    /**
     * test order sync with not existing item SKU
     *
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @magentoDbIsolation   enabled
     * @author               Divya Koona
     */
    public function testReverseOrderWithNotExistingItemSku()
    {
        $this->dummyData->createCustomer();
        $file = "/Json/OrderWithNotExistingItemSku.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_order_028", $errorData);
    }

    /**
     * test order sync with mismatch of item type in Magento and ERP
     *
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @magentoDbIsolation   enabled
     * @author               Divya Koona
     */
    public function testReverseOrderWithMismatchItemType()
    {
        $this->dummyData->createCustomer();
        $this->dummyData->createSingleSimpleProduct(self::FLEXDECK);
        $file = "/Json/OrderWithMismatchItemType.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_order_029", $errorData);
    }

    /**
     * test order sync with disabled product in Magento and ERP
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @author               Divya Koona
     */
    public function testReverseOrderWithDisabledProduct()
    {
        $this->dummyData->createCustomer();
        $this->dummyData->createSingleSimpleProduct(self::FLEXDECK);
        $product = $this->productFactory->create()->load($this->dummyData->productId);
        $product->setStatus(2);
        $product->save();
        $file = "/Json/OrderWithDisabledProduct.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_order_027", $errorData);
    }

    /**
     * test order sync with different line items with same SKU  with different prices
     *
     * @magentoDbIsolation   enabled
     * @magentoConfigFixture current_website i95dev_messagequeue/I95DevConnect_settings/component NAV
     * @author               Divya Koona
     */
    public function testReverseOrderMultiLineItemsWithSameSkuDiffPrices()
    {
        $this->dummyData->createCustomer();
        $this->dummyData->createSingleSimpleProduct(self::FLEXDECK);
        $file = "/Json/OrderMultiLineItemsWithSameSkuDiffPrices.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("SKU ::FLEXDECK Having Different Price (100,50)", $errorData);
    }

    /**
     * test order sync without providing payment entity
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseOrderWithoutPaymentEntity()
    {
        $this->dummyData->createCustomer();
        $this->dummyData->createSingleSimpleProduct(self::FLEXDECK);
        $file = "/Json/OrderWithoutPaymentEntity.json";
        $data = $this->orderTestGenericHelper->readJsonFile($file);
        $collection = $this->orderTestGenericHelper->processData($data, self::ORDST3500, self::C00011);
        $errorData = $this->dummyData->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_order_035", $errorData);
    }

    /**
     * @author Debashis S. Gopal
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->errorUpdateData = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\ErrorUpdateData::class
        );
        $this->dummyData = $objectManager->create(
            \I95DevConnect\I95DevServer\Test\Integration\DummyData::class
        );
        $this->productFactory = $objectManager->create(
            \Magento\Catalog\Model\ProductFactory::class
        );
        $this->orderTestGenericHelper = $objectManager->create(
            OrderGenericHelperTest::class
        );
    }
}
