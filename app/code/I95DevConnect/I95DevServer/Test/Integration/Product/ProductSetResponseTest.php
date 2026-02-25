<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Product;

/**
 * Test case for Product SetResponse
 */
class ProductSetResponseTest extends \PHPUnit\Framework\TestCase
{
    public const ITSTEST1 = 'ITSTEST1';

    /**
     * @var $productId
     */
    public $productId;

    /**
     * @author Debashis S. Gopal
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
        $this->productFactory = $objectManager->create(
            \Magento\Catalog\Api\Data\ProductInterfaceFactory::class
        );
        $this->productRepository = $objectManager->create(
            \Magento\Catalog\Model\ProductRepositoryFactory::class
        );
    }

    /**
     * Create a simple product in magento.
     *
     * @author Debashis S. Gopal
     * @return int
     */
    public function createProduct()
    {
        $product = $this->productFactory->create();
        $product->setSku(self::ITSTEST1)
            ->setStatus(1)
            ->setAttributeSetId(4)
            ->setVisibility(1)
            ->setName(self::ITSTEST1)
            ->setCustomAttribute("description", "Test Item for Integration Testing for set response")
            ->setCustomAttribute("short_description", 'Test Item')
            ->setPrice(500)
            ->setTypeId("simple")
            ->setWeight(1.54)
            ->setCustomAttribute("update_by", "Magento")
            ->setCustomAttribute("targetproductstatus", 'Sync in process');
        $result = $this->productRepository->create()->save($product);
        return $result->getId();
    }

    /**
     * Get outbound message queue collection by magento_id
     *
     * @author Debashis S. Gopal
     * @return array
     */
    public function getOutbountMqData()
    {
        return $this->magentoMessageQueue->getCollection()
            ->addFieldToFilter('magento_id', $this->productId)->getData();
    }
    /**
     * Test case for ProductSetResponse with invalid data.
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testProductWithInvalidData()
    {
        $response = $this->callGetProductsInfo();
        $this->assertEquals(true, $response->result, "Unable to fectch product from outbound MQ");
        $responseAfterSync = $this->callSetResponseService(true);
        $this->assertNotNull($responseAfterSync);
        $mqDataAfterSync = $this->getOutbountMqData();
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $mqDataAfterSync[0]['status'],
            "Status should be ERROR in outbound message queue"
        );
    }

    /**
     * Test case for ProductSetResponse.
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testProductSetResponse()
    {
        $response = $this->callGetProductsInfo();
        $this->assertEquals(true, $response->result, "Unable to fectch product from outbound MQ");
        $responseAfterSync = $this->callSetResponseService(false);
        $this->assertNotNull($responseAfterSync);
        $mqDataAfterSync = $this->getOutbountMqData();
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::COMPLETE,
            $mqDataAfterSync[0]['status'],
            "Status should be ERROR in outbound message queue"
        );
    }

    /**
     * Calls getProductsInfo service.
     *
     * @author Debashis S. Gopal
     * @return I95DevConnect\MessageQueue\Model\I95DevReverseResponse
     */
    public function callGetProductsInfo()
    {
        $this->productId = $this->createProduct();
        return $this->i95devServerRepo->serviceMethod(
            "getProductsInfo",
            '{"requestData":[],"packetSize":50,"erp_name":"ERP"}'
        );
    }

    /**
     * Calls setProductsResponse service.
     *
     * @param $invalid
     *
     * @return I95DevConnect\MessageQueue\Model\I95DevReverseResponse
     * @author Debashis S. Gopal
     */
    public function callSetResponseService($invalid)
    {
        $mqData = $this->getOutbountMqData();
        $msgId = $mqData[0]['msg_id'];
        $request = [];
        if ($invalid) {
            $request['requestData'][] = [
                'reference' => self::ITSTEST1,
                'messageId' => $msgId,
                'message' => '',
                'result' => true,
                'targetId' => 'WRONGSKU',
                'sourceId' => 4545454
            ];
        } else {
            $request['requestData'][] = [
                'reference' => self::ITSTEST1,
                'messageId' => $msgId,
                'message' => '',
                'result' => true,
                'targetId' => self::ITSTEST1,
                'sourceId' => $this->productId
            ];
        }
        return $this->i95devServerRepo->serviceMethod("setProductsResponse", json_encode($request));
    }
}
