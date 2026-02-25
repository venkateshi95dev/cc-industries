<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 * @updatedBy Divya Koona. Removed getCustomerById function and added in Generic Helper.
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Customer\Customer;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Helper\Generic;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\Address;
use I95DevConnect\MessageQueue\Model\DataPersistence\Customer\CustomerGroup;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for preparing customer result data to be sent to ERP
 */
class Info
{
    public const EMAIL = "email";

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var Manager
     */
    public $eventManager;

    /**
     * @var Address
     */
    public $address;

    /**
     * @var CustomerGroup
     */
    public $customerGroup;

    /**
     * @var array
     */
    public $customer;

    /**
     * @var string
     */
    public $customerId;

    /**
     * @var string[]
     */
    public $fieldMapInfo = [
        'sourceId' => 'id',
        self::EMAIL => self::EMAIL,
        'firstName' => 'firstname',
        'lastName' => 'lastname',
        'reference' => self::EMAIL,
        'prefix' => 'prefix',
        'suffix' => 'suffix',
        'middleName' => 'middlename',
        'websiteId' => 'website_id'
    ];

    /**
     * @var array
     */
    public $InfoData = [];

    /**
     * @var Generic
     */
    public $genericHelper;

    /**
     *
     * @param Manager $eventManager
     * @param Address $address
     * @param Data $dataHelper
     * @param CustomerGroup $customerGroup
     * @param Generic $genericHelper
     */
    public function __construct(
        Manager $eventManager,
        Address $address,
        Data $dataHelper,
        CustomerGroup $customerGroup,
        Generic $genericHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->address = $address;
        $this->eventManager = $eventManager;
        $this->customerGroup = $customerGroup;
        $this->genericHelper = $genericHelper;
    }

    /**
     * Prepare customer information to be sent as customer result
     *
     * @param int $customerId
     * @param string $entityCode
     * @param string $erpCode
     * @param int $messageId
     * @return array
     * @throws LocalizedException
     */
    public function getInfo($customerId, $entityCode, $erpCode = null, $messageId = null) //NOSONAR
    {
        $this->customerId = $customerId;
        $this->customer = $this->genericHelper->getCustomerById($customerId);
        if (isset($this->customer)) {
            $this->InfoData = $this->dataHelper->prepareInfoArray($this->fieldMapInfo, $this->customer);

            if (isset($this->customer['custom_attributes'])) {
                foreach ($this->customer['custom_attributes'] as $value) {
                    if ($value['attribute_code'] == 'target_customer_id') {
                        $this->InfoData['targetCustomerId'] = $value['value'];
                        $this->InfoData['targetId'] = $value['value'];
                    }
                }
            }

            if (isset($this->customer['group_id'])) {
                $this->InfoData['customerGroup'] = $this->customerGroup
                        ->getCustomerGroupEntityByGroupId($this->customer['group_id']);
            } else {
                $this->InfoData['customerGroup'] = null;
            }
            $addressData = $this->address->getInfo($this->customer);
            $this->InfoData['addresses'] = $addressData;
        }

        $customerInfoEvent = "erpconnect_forward_customerinfo";
        $this->eventManager->dispatch($customerInfoEvent, ['customer' => $this]);
        return $this->InfoData;
    }
}
