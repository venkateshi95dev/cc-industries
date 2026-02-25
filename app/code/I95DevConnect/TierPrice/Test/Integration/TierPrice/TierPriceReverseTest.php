<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_TierPrice
 */

namespace I95DevConnect\TierPrice\Test\Integration\TierPrice;

/**
 * Test case for Tier Price reverse flow
 */
class TierPriceReverseTest extends \PHPUnit\Framework\TestCase
{
    public const JSON_PATH_STR = 'jsonPath';
    public const STATUS_STR = 'status';
    public const SAVING_STR = 'Issue came in saving tierprice from mq to magento';
    public const IS_CUSTOMER_GRP_STR = 'isNewCustomerGroup';
    public const TESTPRO2_STR = 'TESTPRO2';

    /**
     * @author Debashis S. Gopal
     */
    protected function setUp()
    {
        $this->i95devServerRepo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->erpMessageQueue = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository::class
        );
        $this->productFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\Data\ProductInterfaceFactory::class
        );
        $this->productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepositoryFactory::class
        );
        $this->i95DevCustomerGroupFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \I95DevConnect\MessageQueue\Model\CustomerGroupFactory::class
        );
        $this->groupFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\Data\GroupInterfaceFactory::class
        );
        $this->groupRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\GroupRepositoryInterface::class
        );
    }

    /**
     * Create a simple product in magento.
     *
     * @param string $sku
     *
     * @return void
     * @author Debashis S. Gopal
     */
    public function createProduct($sku)
    {
        $product = $this->productFactory->create();
        $product->setSku($sku)
            ->setStatus(1)
            ->setAttributeSetId(4)
            ->setVisibility(1)
            ->setName($sku)
            ->setCustomAttribute("description", "Test Item for Integration Testing")
            ->setCustomAttribute("short_description", 'Test Item')
            ->setPrice(500)
            ->setTypeId("simple")
            ->setWeight(1.54)
            ->setCustomAttribute("update_by", "Magento")
            ->setCustomAttribute("targetproductstatus", 'Sync in process');
        $this->productRepository->create()->save($product);
    }

    /**
     * Create a customer group in magneto.
     *
     * @author Debashis S. Gopal
     * @return boolean
     */
    public function createNewCustomerGroup()
    {
        $group = $this->groupFactory->create();
        $group->setCode('Test')
            ->setTaxClassId(3);
        $customerGrp = $this->groupRepository->save($group);
        $grpId = $customerGrp->getId();
        if (!$grpId) {
            return false;
        }
        $i95DevCustomGroup = $this->i95DevCustomerGroupFactory->create();
        $i95DevCustomGroup->setTargetGroupId('Test');
        $i95DevCustomGroup->setCustomerGroupId($grpId);
        $i95DevCustomGroup->setUpdateBy(__('ERP'));
        $i95DevCustomGroup->save();
        return true;
    }

    /**
     * Test case for Tier price reverse sync with already existing customer group.
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testCreateTierPriceForAlreasdyExistCustomerGroup()
    {
        $path = realpath(dirname(__FILE__)) . "/Json/TierpriceErpData.json";
        $this->completeSync(['isCustomerGroupExists' => true, self::JSON_PATH_STR => $path]);
        $mqCollection = $this->getInboundMqData('TESTPRO');
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $mqCollection[0][self::STATUS_STR],
            self::SAVING_STR
        );
    }

    /**
     * Test case for tier price  reverse sync with new customer groups
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testCreateTierPriceForNewCustomerGroup()
    {
        $path = realpath(dirname(__FILE__)) . "/Json/TierpriceErpDataForNewCustomerGroup.json";
        $this->completeSync([self::IS_CUSTOMER_GRP_STR => true, self::JSON_PATH_STR => $path]);
        $mqCollection = $this->getInboundMqData(self::TESTPRO2_STR);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $mqCollection[0][self::STATUS_STR],
            self::SAVING_STR
        );
    }

    /**
     * Test case for tier price  reverse sync with unavailable product
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testCreateTierPriceWithInvalidProduct()
    {
        $path =  realpath(dirname(__FILE__)) . "/Json/TierpriceErpDataForNewCustomerGroup.json";
        $this->completeSync(
            [
                self::IS_CUSTOMER_GRP_STR => true,
                'isInvalidProduct' => true,
                self::JSON_PATH_STR => $path
            ]
        );
        $mqCollection = $this->getInboundMqData(self::TESTPRO2_STR);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $mqCollection[0][self::STATUS_STR],
            self::SAVING_STR
        );
    }

    /**
     * Test case for tier price  reverse sync with Empty Tier Price Data
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testCreateTierPriceWithEmptyTierPriceData()
    {
        $path = realpath(dirname(__FILE__)) . "/Json/TierpriceErpDataWithEmptyTierPriceData.json";
        $this->completeSync(['isEmptytier' => true, self::JSON_PATH_STR => $path]);
        $mqCollection = $this->getInboundMqData('TESTPRO3');
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $mqCollection[0][self::STATUS_STR],
            self::SAVING_STR
        );
    }

    /**
     * Test case for tier price  reverse sync with Empty Tier Price Data
     *
     * @magentoDbIsolation enabled
     * @author             Debashis S. Gopal
     */
    public function testCreateTierPriceWithEmptyPriceLevelKey()
    {
        $path = realpath(dirname(__FILE__)) . "/Json/TierpriceErpDataWithEmptyPriceLevelKey.json";
        $this->completeSync(['isEmptyPriceLevelKey' => true, self::JSON_PATH_STR => $path]);
        $mqCollection = $this->getInboundMqData('TESTPRO4');
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $mqCollection[0][self::STATUS_STR],
            self::SAVING_STR
        );
    }

    /**
     * Call updateTierPriceList service method
     *
     * @param  $inputs
     * @return mixed
     * @author Debashis S. Gopal
     */
    public function callCreateTierPriceServiceMethod($inputs)
    {
        $jsonPath = $inputs[self::JSON_PATH_STR];
        if (isset($inputs['isCustomerGroupExists'])) {
            $this->createProduct("TESTPRO");
            $this->createNewCustomerGroup();
        } elseif (isset($inputs['isEmptytier'])) {
            $this->createProduct("TESTPRO3");
        } elseif (isset($inputs['isEmptyPriceLevelKey'])) {
            $this->createProduct("TESTPRO4");
        } elseif (isset($inputs[self::IS_CUSTOMER_GRP_STR])) {
            if (!isset($inputs['isInvalidProduct'])) {
                $this->createProduct("TESTPRO2");
            }
        }
        $tierPriceJsonData = file_get_contents($jsonPath);
        return $this->i95devServerRepo->serviceMethod("updateTierPriceList", $tierPriceJsonData);
    }

    /**
     * Generic function for all test cases which will execute both updateTierPriceList and
     * sync from MQ to Magento
     *
     * @param array $inputs
     */
    public function completeSync($inputs)
    {
        $response = $this->callCreateTierPriceServiceMethod($inputs);
        $this->assertEquals(true, $response->result, $response->message);
        $this->i95devServerRepo->syncMQtoMagento();
    }

    /**
     * get inbound message queue collection by target_id
     *
     * @author Debashis S. Gopal
     * @param  $targetId
     * @return Object
     */
    public function getInboundMqData($targetId)
    {
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('target_id', $targetId)
            ->getData();
    }
}
