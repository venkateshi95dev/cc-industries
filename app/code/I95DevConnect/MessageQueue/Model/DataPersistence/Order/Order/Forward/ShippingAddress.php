<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\DataPersistence\Order\Order\Forward;

use I95DevConnect\MessageQueue\Helper\Generic;
use Magento\Customer\Api\AddressRepositoryInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class responsible for preparing shipping address data which will be added in order result to ERP
 */
class ShippingAddress
{
    /**
     * @var array
     */
    public $shippingAddress = [];

    /**
     * @var Generic
     */
    public $genericHelper;

    /**
     * @var AddressFactory
     */
    public $addressCollectionFactory;

    /**
     * @var OrderInterface
     */
    public $order;

    /**
     * @var AddressRepositoryInterfaceFactory
     */
    public $addressRepo;

    /**
     * @var QuoteFactory
     */
    public $quoteFactory;

    /**
     * Constructor for DI
     *
     * @param AddressRepositoryInterfaceFactory $addressRepo
     * @param AddressFactory $addressCollectionFactory
     * @param OrderInterface $order
     * @param Generic $genericHelper
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        AddressRepositoryInterfaceFactory $addressRepo,
        AddressFactory $addressCollectionFactory,
        OrderInterface $order,
        Generic $genericHelper,
        QuoteFactory $quoteFactory
    ) {
        $this->addressRepo = $addressRepo;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->order = $order;
        $this->genericHelper = $genericHelper;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Method to prepare Order's Shipping Address array
     *
     * @param OrderInterface $order
     * @return array
     * @throws LocalizedException
     * @createdBy Sravani Polu
     */
    public function getShippingAddress($order)
    {
        $orderShippingAddress = null;
        try {
            $extensionAttributes = $order->getExtensionAttributes();
            if ($order->getIsNotVirtual() && $extensionAttributes && $extensionAttributes->getShippingAssignments()) {
                $shippingAssignments = $extensionAttributes->getShippingAssignments();
                if (!empty($shippingAssignments)) {
                    $shipping = array_shift($shippingAssignments)->getShipping();
                    $shippingAddr = $shipping->getAddress();

                    $orderShippingAddress['shippingMethod'] = $this->getShippingMethod($shipping);
                }

                if (!empty($shippingAddr)) {
                    $orderShippingAddress['city'] = $shippingAddr->getCity();
                    $orderShippingAddress['countryId'] = $shippingAddr->getCountryId();
                    $orderShippingAddress['firstName'] = $shippingAddr->getFirstname();
                    $orderShippingAddress['middleName'] = $shippingAddr->getMiddlename();
                    $orderShippingAddress['lastName'] = $shippingAddr->getLastname();
                    $orderShippingAddress['postcode'] = $shippingAddr->getPostcode();
                    $orderShippingAddress['regionId'] = $shippingAddr->getRegionCode();
                    $orderShippingAddress['region'] = $shippingAddr->getRegion();
                    $orderShippingAddress['street'] = $this->getStreet($shippingAddr);
                    $orderShippingAddress['street2'] = $this->getStreet2($shippingAddr);
                    $orderShippingAddress['telephone'] = $shippingAddr->getTelephone();
                    $sourceAddressId = $shippingAddr->getCustomerAddressId();
                    $orderShippingAddress['sourceId'] = $this->getSourceAddressId($sourceAddressId);
                    $orderShippingAddress['targetId'] = null;
                    if ($sourceAddressId) {
                        try {
                        $customerAddress = $this->addressRepo->create()->getById($sourceAddressId);

                            $targetAttr = $customerAddress->getCustomAttribute('target_address_id');
                            $orderShippingAddress['targetId'] = $targetAttr ? $targetAttr->getValue() : null;

                        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                            // Address deleted / invalid → fallback to order address
                            $orderShippingAddress['sourceId'] = null;
                        $orderShippingAddress['targetId'] = null;
                    }
                }

                }
            }
            return $orderShippingAddress;
        } catch (LocalizedException $ex) {
            throw new LocalizedException(
                __($ex->getMessage()),
                null,
                $ex->getCode()
            );
        }
    }

    /**
     * Get Street
     *
     * @param object $shippingAddr
     * @return bool
     */
    public function getStreet($shippingAddr)
    {
        return is_array($shippingAddr->getStreet()) ?
            $shippingAddr->getStreet()[0] : null;
    }

    /**
     * Get Street 2
     *
     * @param object $shippingAddr
     * @return bool
     */
    public function getStreet2($shippingAddr)
    {
        return (array_key_exists(1, $shippingAddr->getStreet())) ?
            $shippingAddr->getStreet()[1] : null;
    }

    /**
     * Get Source Address Id
     *
     * @param object $sourceAddressId
     * @return null
     */
    public function getSourceAddressId($sourceAddressId)
    {
        return isset($sourceAddressId) ? $sourceAddressId : null;
    }

    /**
     * Fetch multiple shipping address of an order
     *
     * @param int $quoteId
     * @param int $customerAddressId
     * @return array
     * @createdBy Sravani Polu
     */
    public function getMultiAddressShipping($quoteId, $customerAddressId)
    {
        return $this->addressCollectionFactory->create()->getCollection()
                ->addFieldToFilter('quote_id', $quoteId)
                ->addFieldToFilter('address_type', 'shipping')
                ->addFieldToFilter('customer_address_id', $customerAddressId)
                ->getFirstItem()
                ->getShippingMethod();
    }

    /**
     * Checking quote is having multiple shipping addresses or not
     *
     * @param int $quoteId
     * @return int
     * @createdBy Sravani Polu
     */
    public function checkMultiAddressQuote($quoteId)
    {
        $quoteColl = $this->quoteFactory->create()->getCollection()
            ->addFieldToSelect('is_multi_shipping')
            ->addFieldToFilter('entity_id', $quoteId);
        $quoteColl->getSelect()->limit(1);
        $quoteColl = $quoteColl->getData();
        if ($quoteColl->getSize() > 0) {
            $quoteColl = $quoteColl[0]['is_multi_shipping'];
        } else {
            $quoteColl = null;
        }

        return $quoteColl;
    }

    /**
     * Get shipping method of an order
     *
     * @param Object $shipping
     * @return string
     * @createdBy Arushi Bansal
     */
    public function getShippingMethod($shipping)
    {

        return $shipping->getMethod();
    }
}
