<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward;

use Exception;
use Magento\Customer\Api\AddressRepositoryInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class responsible for preparing order's billing address data which will be added in order result to ERP
 * @createdBy Sravani Polu
 */
class BillingAddress
{
    /**
     * @var AddressRepositoryInterfaceFactory
     */
    public $addressRepo;

    /**
     *
     * @param AddressRepositoryInterfaceFactory $addressRepo
     */
    public function __construct(
        AddressRepositoryInterfaceFactory $addressRepo
    ) {
        $this->addressRepo = $addressRepo;
    }

    /**
     * Method to prepare Order's Billing Address array
     *
     * @param OrderInterface $order
     * @return array
     * @throws Exception
     * @createdBy Sravani Polu
     * @noinspection DuplicatedCode
     */
    public function getBillingAddress($order)
    {
        try {
            $billingAddress = $order->getBillingAddress();
            if ($billingAddress !== null) {
                $orderBillingAddress['firstName'] = $billingAddress->getFirstname();
                $orderBillingAddress['middleName'] = $billingAddress->getMiddlename();
                $orderBillingAddress['lastName'] = $billingAddress->getLastname();
                $orderBillingAddress['postcode'] = $billingAddress->getPostcode();
                $orderBillingAddress['regionId'] = $billingAddress->getRegionCode();
                $orderBillingAddress['region'] = $billingAddress->getRegion();
                $orderBillingAddress['city'] = $billingAddress->getCity();
                $orderBillingAddress['countryId'] = $billingAddress->getCountryId();
                $orderBillingAddress['street'] = is_array($billingAddress->getStreet()) ?
                    $billingAddress->getStreet()[0] : null;
                $orderBillingAddress['street2'] = (array_key_exists(1, $billingAddress->getStreet())) ?
                    $billingAddress->getStreet()[1] : null;
                $orderBillingAddress['telephone'] = $billingAddress->getTelephone();
                $sourceAddressId = $billingAddress->getCustomerAddressId();
                $orderBillingAddress['sourceId'] = isset($sourceAddressId) ? $sourceAddressId : null;
                $orderBillingAddress['targetId'] = null;
                if ($sourceAddressId) {
                    try {
                    $customerAddress = $this->addressRepo->create()->getById($sourceAddressId);

                        $targetAttr = $customerAddress->getCustomAttribute('target_address_id');
                        $orderBillingAddress['targetId'] = $targetAttr ? $targetAttr->getValue() : null;

                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                        // Address deleted or invalid → fallback to order address
                        $orderBillingAddress['sourceId'] = null;
                    $orderBillingAddress['targetId'] = null;
                }
                }

                return $orderBillingAddress;
            } else {
                return null;
            }
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }
}

