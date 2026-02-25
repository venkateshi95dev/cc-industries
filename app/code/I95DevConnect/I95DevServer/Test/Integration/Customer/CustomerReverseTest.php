<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Customer;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * customer test case for reverse flows
 */
class CustomerReverseTest extends \PHPUnit\Framework\TestCase
{
    public const ERP_CUST = 'ERPCUST001';
    public const MGT_ID = "magento_id";
    public const ERP_CUST6 = "ERPCUST006";
    public const ERP_CUST4 = 'ERPCUST0004';
    public const STATUS = 'status';
    public const ERROR_ID = "error_id";
    public const ISSUE001 = "Issue came in saving customer from mq to magento";
    public const ISSUE002 = "Issue came in saving customer group from mq to magento";

    /**
     * @var $customer
     */
    public $customer;
    /**
     * @var $customerModel
     */
    public $customerModel;
    /**
     * @var $customerId
     */
    public $customerId;
    /**
     * @var $i95devServerRepo
     */
    public $i95devServerRepo;
    /**
     * @var $erpMessageQueue
     */
    public $erpMessageQueue;
    /**
     * @var $groupRepository
     */
    public $groupRepository;
    /**
     * @var $resourceConfig
     */
    public $resourceConfig;
    /**
     * @var $errorUpdateData
     */
    public $errorUpdateData;
    /**
     * @var $dataHelper
     */
    public $dataHelper;

    /**
     * Test case for Customer creation from ERP to Magento without address.
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testCustomerWithoutAddress()
    {
        $file = "/Json/Customer.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::ERP_CUST;
        $collection = $this->processData($data, $customerErpId);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0][self::STATUS],
            self::ISSUE001
        );
        $getCustomer = $this->customer->get("christopherjose.yien@bullbeer.org", 1);
        $getCustomerId = $getCustomer->getId();
        $this->assertEquals(
            $getCustomerId,
            $collection[0][self::MGT_ID],
            self::ISSUE001
        );
        // Re sync Data if customer already exist in Magento
        $this->reSyncData($data);
    }

    /**
     * Read data from json file
     *
     * @createdBy Hrusieksh Manna
     * @param     $fileName
     * @return    false|string
     */
    public function readJsonFile($fileName)
    {
        $path = realpath(dirname(__FILE__)) . $fileName;
        return file_get_contents($path);
    }

    /**
     * Create data in message que and sync to magento
     *
     * @createdBy Hrusieksh Manna
     * @param     $customerData
     * @param     $erpId
     * @return    array
     */
    public function processData($customerData, $erpId)
    {
        $response = $this->createCustomerInInboundMQ($customerData, $erpId);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0][self::STATUS],
            "Issue came in saving customer to messagequeue"
        );
        $this->i95devServerRepo->syncMQtoMagento();
        return $this->getInboundMqData($erpId);
    }

    /**
     * Create customer in inbound messagequeue
     *
     * @param  $customerJsonData
     * @param  $targetCustomerId
     * @return array
     * @author Divya Koona
     */
    public function createCustomerInInboundMQ($customerJsonData, $targetCustomerId)
    {
        $this->i95devServerRepo->serviceMethod("createCustomersList", $customerJsonData);
        return $this->getInboundMqData($targetCustomerId);
    }

    /**
     * Get inbound message queue collection by ref name
     *
     * @param $targetCustomerId
     *
     * @return array
     * @author Debashis S. Gopal. getCollection by 'ref_name' chnaged to
     * getCollection by 'target_id', as we are passing target id to this function.
     */
    public function getInboundMqData($targetCustomerId)
    {
        return $this->erpMessageQueue->getCollection()
            ->addFieldToFilter('target_id', $targetCustomerId)
            ->getData();
    }

    /**
     * Check if the customer already exist
     *
     * @createdBy Hrusieksh Manna
     * @param     $customerJsonData
     */
    public function reSyncData($customerJsonData)
    {
        $this->createCustomerInInboundMQ($customerJsonData, self::ERP_CUST);
        $this->i95devServerRepo->syncMQtoMagento();
        $collection = $this->getInboundMqData(self::ERP_CUST);
        $getCustomer = $this->customer->get("christopherjose.yien@bullbeer.org", 1);
        $getCustomerId = $getCustomer->getId();
        $this->assertEquals(
            $getCustomerId,
            $collection[0][self::MGT_ID],
            self::ISSUE001
        );
    }

    /**
     * Customer Address reverse sync with destination id
     *
     * @magentoDbIsolation enabled
     * @author             Hrusikesh Manna
     */
    public function testReverseCustomerAddressWithDestinationId()
    {
        $file = "/Json/customerAddressWithDestinationId.json";
        $data = $this->readJsonFile($file);
        $customerErpId = "ERPCUST004";
        $collection = $this->processData($data, $customerErpId);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0][self::STATUS],
            self::ISSUE001
        );
    }

    /**
     * Test customer sync with new customer group creation
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseCustomerWithNewCustomerGroup()
    {
        $file = "/Json/CustomerWithNewCustomerGroup.json";
        $data = $this->readJsonFile($file);
        $customerErpId = "ERPCUST002";
        $collection = $this->processData($data, $customerErpId);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0][self::STATUS],
            self::ISSUE001
        );

        $getCustomer = $this->customer->get("christopherjose.yien1@bullbeer.org", 1);
        $getCustomerId = $getCustomer->getId();

        $groupId = $getCustomer->getGroupId();
        $group = $this->groupRepository->getById($groupId);
        $groupCode = $group->getCode();
        $this->assertEquals(
            $getCustomerId,
            $collection[0][self::MGT_ID],
            self::ISSUE001
        );
        $this->assertEquals('New Customer Group', $groupCode, self::ISSUE002);
    }

    /**
     * Test customer sync with null customer group
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseCustomerWithNullCustomerGroup()
    {
        $file = "/Json/CustomerWithNullCustomerGroup.json";
        $data = $this->readJsonFile($file);
        $customerErpId = "ERPCUST003";
        $collection = $this->processData($data, $customerErpId);
        $getCustomer = $this->customer->get("christopherjose.yien2@bullbeer.org", 1);
        $getCustomerId = $getCustomer->getId();
        $groupId = $getCustomer->getGroupId();
        $this->assertEquals(
            $getCustomerId,
            $collection[0][self::MGT_ID],
            self::ISSUE001
        );
        $this->assertEquals(1, $groupId, self::ISSUE002);
    }

    /**
     * Test customer sync with same email with different target customer ids
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseCustomerWithSameEmailError()
    {
        $path = realpath(dirname(__FILE__)) . "/Json/CustomerWithSameEmailError.json";
        $customerJsonData = file_get_contents($path);

        $this->createCustomer();
        $response = $this->createCustomerInInboundMQ($customerJsonData, 'ERPCUST005');
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::PENDING,
            $response[0][self::STATUS],
            "Issue came in saving customer to messagequeue"
        );

        $this->i95devServerRepo->syncMQtoMagento();

        $collection = $this->getInboundMqData('ERPCUST005');
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::ERROR,
            $collection[0][self::STATUS],
            self::ISSUE001
        );

        $errorData = $this->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_cust_009", $errorData);
    }

    /**
     * Create dummy customer for test cases
     *
     * @author Divya Koona
     */
    public function createCustomer()
    {
        $fName = "christopher";
        $lName = "jose";
        $customerEmail = 'christopherjose.yien3@bullbeer.org';
        $this->customerModel
            ->setWebsiteId(1)
            ->setEntityTypeId(1)
            ->setAttributeSetId(1)
            ->setEmail($customerEmail)
            ->setPassword('password')
            ->setGroupId(1)
            ->setStoreId(1)
            ->setIsActive(1)
            ->setFirstname($fName)
            ->setLastname($lName)
            ->setTargetCustomerId(self::ERP_CUST4);
        $this->customerModel->isObjectNew(true);
        $this->customerModel->save();
        $this->customerId = $this->customerModel->getId();
    }

    /**
     * Get error message
     *
     * @param  int $errorId
     * @return string
     * @author Divya Koona
     */
    public function getErrorData($errorId)
    {
        $errorData = $this->errorUpdateData->getCollection()
            ->addFieldToFilter('id', $errorId)
            ->getData();

        return $errorData[0]['msg'];
    }

    /**
     * Test customer sync with without firstname
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseCustomerWithoutFirstname()
    {
        $file = "/Json/CustomerWithoutFirstname.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::ERP_CUST6;
        $collection = $this->processData($data, $customerErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_cust_005", $errorData);
    }

    /**
     * Test customer sync with without lastname
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseCustomerWithoutLastname()
    {
        $file = "/Json/CustomerWithoutLastname.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::ERP_CUST6;
        $collection = $this->processData($data, $customerErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_cust_006", $errorData);
    }

    /**
     * Test customer sync with without email
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseCustomerWithoutEmail()
    {
        $file = "/Json/CustomerWithoutEmail.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::ERP_CUST6;
        $collection = $this->processData($data, $customerErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_cust_004", $errorData);
    }

    /**
     * Test customer sync with without target id
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseCustomerWithoutTargetId()
    {
        $file = "/Json/CustomerWithoutTargetId.json";
        $data = $this->readJsonFile($file);
        $collection = $this->processData($data, '');
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID]);
        $this->assertSame("i95dev_cust_003", $errorData);
    }

    /**
     * Test customer sync with without firstname
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testCustomerFirstNameAndEmailUpdate()
    {
        $this->createCustomer();
        $file = "/Json/CustomerNameAndEmailUpdate.json";
        $data = $this->readJsonFile($file);
        $collection = $this->processData($data, self::ERP_CUST4);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0][self::STATUS],
            self::ISSUE001
        );
        $getCustomer = $this->customer->get("christopherjose_update.yien1@bullbeer.org", 1);
        $this->assertNotEmpty($getCustomer, "Customer updation not happened");

        $customer_id = $getCustomer->getId();
        $name = $getCustomer->getFirstname();
        $this->assertEquals('christopherNew', $name, 'Name not updated');
        $this->assertEquals(
            $customer_id,
            $collection[0][self::MGT_ID],
            self::ISSUE001
        );
    }

    /**
     * Test customer sync with without firstname
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testCustomerGroupUpdate()
    {
        $this->createCustomer();
        $file = "/Json/CustomerGroupUpdate.json";
        $data = $this->readJsonFile($file);
        $collection = $this->processData($data, self::ERP_CUST4);
        $this->assertEquals(
            \I95DevConnect\MessageQueue\Helper\Data::SUCCESS,
            $collection[0][self::STATUS],
            self::ISSUE001
        );

        $getCustomer = $this->customer->get("christopherjose.yien3@bullbeer.org", 1);
        $this->assertNotEmpty($getCustomer, "Customer updation not happened");

        $groupId = $getCustomer->getGroupId();
        $group = $this->groupRepository->getById($groupId);
        $groupCode = $group->getCode();
        $this->assertEquals('New Customer Group', $groupCode, self::ISSUE002);
    }

    /**
     * Create customer group store configuration
     *
     * @author Divya Koona
     */
    public function createCustomerGroupStoreConfiguration()
    {
        $this->resourceConfig->saveConfig(
            'i95dev_messagequeue/I95DevConnect_settings/customer_group',
            1,
            'default',
            0
        );

        return $this->dataHelper->getscopeConfig(
            'i95dev_messagequeue/I95DevConnect_settings/customer_group',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * @author Divya Koona
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->customer = $objectManager->create(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->groupRepository = $objectManager->create(
            \Magento\Customer\Api\GroupRepositoryInterface::class
        );
        $this->customerModel = $objectManager->create(
            \Magento\Customer\Model\Customer::class
        );
        $this->resourceConfig = $objectManager->create(
            \Magento\Config\Model\ResourceModel\Config::class
        );
        $this->i95devServerRepo = $objectManager->create(
            \I95DevConnect\I95DevServer\Model\I95DevServerRepository::class
        );
        $this->erpMessageQueue = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\I95DevErpMQRepository::class
        );
        $this->dataHelper = $objectManager->create(
            \I95DevConnect\MessageQueue\Helper\Data::class
        );
        $this->errorUpdateData = $objectManager->create(
            \I95DevConnect\MessageQueue\Model\ErrorUpdateData::class
        );
    }
}
