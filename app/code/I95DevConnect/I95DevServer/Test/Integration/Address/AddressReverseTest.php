<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_I95DevServer
 * @author    Divya Koona
 */

namespace I95DevConnect\I95DevServer\Test\Integration\Address;

use I95DevConnect\I95DevServer\Test\Integration\AddressParent;

/**
 * address test case for reverse flows
 */
class AddressReverseTest extends AddressParent
{
    /**
     * test basic happy path for a address
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseNewAddressCreation()
    {
        $file = "/Json/NewAddressCreation.json";
        $data = $this->readJsonFile($file);
        $customerErpId = "CUST002";
        $customerEmail = 'MarkJohnson@gmail.com';
        $addressErpId = "01";
        $this->createCustomer($customerErpId, $customerEmail);

        $this->customAttrCode($data, $customerErpId, $addressErpId, $customerEmail);

        // Re sync Data if address already exist in Magento
        $this->reSyncData($data, $customerErpId, $addressErpId);
    }

    /**
     * test basic happy path for multiple addresses
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseMultipleNewAddressCreation()
    {
        $file = "/Json/MultipleNewAddressCreation.json";
        $data = $this->readJsonFile($file);
        $customerErpId = 'CUST003';
        $customerEmail = 'MarkJohnson1@gmail.com';
        $addressErpIds = ['01', '02', '03'];
        $this->createCustomer($customerErpId, $customerEmail);
        $this->processMultipleAddressData($data, $customerErpId, $customerEmail, $addressErpIds);
        // Re sync Data if customer already exist in Magento
        $this->reSyncMultipleAddressData($data, $customerErpId, $customerEmail, $addressErpIds);
    }

    /**
     * test address sync without firstname
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseAddressWithoutFirstname()
    {
        $file = "/Json/AddressWithoutFirstname.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::CUST001_STR;
        $addressErpId = "01";
        $collection = $this->processData($data, $customerErpId, $addressErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("i95dev_addr_002", $errorData);
    }

    /**
     * test address sync without lastname
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseAddressWithoutLastname()
    {
        $file = "/Json/AddressWithoutLastname.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::CUST001_STR;
        $addressErpId = "01";
        $collection = $this->processData($data, $customerErpId, $addressErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("i95dev_addr_003", $errorData);
    }

    /**
     * test address sync without country id
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseAddressWithoutCountryId()
    {
        $file = "/Json/AddressWithoutCountryId.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::CUST001_STR;
        $addressErpId = "01";
        $collection = $this->processData($data, $customerErpId, $addressErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("i95dev_addr_004", $errorData);
    }

    /**
     * test address sync without city
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseAddressWithoutCity()
    {
        $file = "/Json/AddressWithoutCity.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::CUST001_STR;
        $addressErpId = "01";
        $collection = $this->processData($data, $customerErpId, $addressErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("i95dev_addr_006", $errorData);
    }

    /**
     * test address sync without street
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseAddressWithoutStreet()
    {
        $file = "/Json/AddressWithoutStreet.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::CUST001_STR;
        $addressErpId = "01";
        $collection = $this->processData($data, $customerErpId, $addressErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("i95dev_addr_007", $errorData);
    }

    /**
     * test address sync without postcode
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseAddressWithoutPostcode()
    {
        $file = "/Json/AddressWithoutPostcode.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::CUST001_STR;
        $addressErpId = "01";
        $collection = $this->processData($data, $customerErpId, $addressErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("i95dev_addr_008", $errorData);
    }

    /**
     * test address sync without telephone
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseAddressWithoutTelephone()
    {
        $file = "/Json/AddressWithoutTelephone.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::CUST001_STR;
        $addressErpId = "01";
        $collection = $this->processData($data, $customerErpId, $addressErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("i95dev_addr_009", $errorData);
    }

    /**
     * test address sync without target id
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseAddressWithoutTargetId()
    {
        $file = "/Json/AddressWithoutTargetId.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::CUST001_STR;
        $addressErpId = "";
        $collection = $this->processData($data, $customerErpId, $addressErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("i95dev_addr_001", $errorData);
    }

    /**
     * test address sync without region for the country for which state is required
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseAddressWithoutRegion()
    {
        $file = "/Json/AddressWithoutRegion.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::CUST001_STR;
        $addressErpId = "01";
        $collection = $this->processData($data, $customerErpId, $addressErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("i95dev_addr_005", $errorData);
    }

    /**
     * test address sync with invalid region code for the country for which state is required
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseAddressWithInvalidRegionCode()
    {
        $file = "/Json/AddressWithInvalidRegionCode.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::CUST001_STR;
        $addressErpId = "01";
        $collection = $this->processData($data, $customerErpId, $addressErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("i95dev_addr_014", $errorData);
    }

    /**
     * test address sync with not existing customer in Magento
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testReverseAddressWithNotExistingCustomer()
    {
        $file = "/Json/AddressWithNotExistingCustomer.json";
        $data = $this->readJsonFile($file);
        $customerErpId = self::CUST001_STR;
        $addressErpId = "01";
        $collection = $this->processData($data, $customerErpId, $addressErpId);
        $errorData = $this->getErrorData($collection[0][self::ERROR_ID_STR]);
        $this->assertSame("i95dev_addr_019", $errorData);
    }

    /**
     * test address sync with not existing customer in Magento
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testAddressUpdate()
    {
        $customerErpId = "C00011";
        $customerEmail = 'hrusikesh.manna@jiva.com';
        $addressErpId = "2";
        $this->dummyData->createCustomer();
        $updatefile = "/Json/AddressUpdate.json";
        $updateData = $this->readJsonFile($updatefile);

        $address = $this->customAttrCode($updateData, $customerErpId, $addressErpId, $customerEmail);
        $streets = $address->getStreet();

        $this->assertEquals('One Church Street update', $streets[0], "Street not updated");
        $this->assertEquals('28954', $address->getPostcode(), "Postcode not updated");
        $this->assertEquals('9000002154', $address->getTelephone(), "Telephone not updated");
        $this->assertEquals('New York', $address->getCity(), "Telephone not updated");
        $this->assertEquals('NY', $address->getRegion()->getRegionCode(), "Region not updated");
    }

    /**
     * test address sync with not existing customer in Magento
     *
     * @magentoDbIsolation enabled
     * @author             Divya Koona
     */
    public function testAustreliaAddressCreation()
    {
        $customerErpId = "CUST017";
        $customerEmail = 'mana.bana@test.com';
        $addressErpId = "PRIMARY";
        $file = "/Json/AustreliaAddress.json";
        $data = $this->readJsonFile($file);
        $address = $this->customAttrCode($data, $customerErpId, $addressErpId, $customerEmail);
        $this->assertEquals('AU', $address->getCountryId(), "CountryId not updated");
    }

    /**
     * @param  $data
     * @param  $customerErpId
     * @param  $addressErpId
     * @param  $customerEmail
     * @return array
     */
    public function customAttrCode($data, $customerErpId, $addressErpId, $customerEmail)
    {
        $getAddressId = 0;
        $collection = $this->processData($data, $customerErpId, $addressErpId);

        $getCustomer = $this->customer->get($customerEmail, 1);
        $customerAddress = $getCustomer->getAddresses();
        $this->assertEquals(1, count($customerAddress), self::ADDR_SAVING_MSG_STR);
        $address = $customerAddress[0];
        if (!empty($address->getCustomAttributes())) {
            foreach ($address->getCustomAttributes() as $addressCusttomAttr) {
                if (($addressCusttomAttr->getAttributeCode() === self::TARGET_ADDRESS_ID_STR)
                    && ($addressCusttomAttr->getValue() === $addressErpId)
                ) {
                    $getAddressId = $address->getId();
                    break;
                }
            }
        }
        $this->assertEquals(
            $getAddressId,
            $collection[0][self::MAGENTO_ID_STR],
            self::ADDR_SAVING_MQ_STR
        );

        return $address;
    }
}
