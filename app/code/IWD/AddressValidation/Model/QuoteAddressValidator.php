<?php

namespace IWD\AddressValidation\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;

class QuoteAddressValidator extends \Magento\Quote\Model\QuoteAddressValidator
{
    private $customerFactory;

    public function __construct(
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        parent::__construct($addressRepository, $customerRepository,$customerSession);
        $this->customerFactory = $customerFactory;
    }

    public function validateForCart(CartInterface $cart, AddressInterface $address): void
    {
        if(!$cart->getCustomerIsGuest()){
            $customerId = $cart->getCustomer()->getId();
            if(!$customerId){
                try{
                    $customer = $this->customerFactory->create();
                    $customer->setWebsiteId($cart->getCustomer()->getWebsiteId());
                    $customer->loadByEmail($cart->getCustomer()->getEmail());
                    $customerId = $customer->getId();
                }catch (\Exception $e){

                }
            }
        }
        $this->doValidate($address, $cart->getCustomerIsGuest() ? null : $customerId);
    }

    private function doValidate(AddressInterface $address, ?int $customerId): void
    {
        //temporary fix
        if(!$customerId){
            if($address->getCustomerId()){
                $customerId = $address->getCustomerId();
            }
        }

        //validate customer id
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            if (!$customer->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Invalid customer id %1', $customerId)
                );
            }
        }

        if ($address->getCustomerAddressId()) {
            //Existing address cannot belong to a guest
            if (!$customerId) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Invalid customer address id %1', $address->getCustomerAddressId())
                );
            }
            //Validating address ID
            try {
                $this->addressRepository->getById($address->getCustomerAddressId());
            } catch (NoSuchEntityException $e) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Invalid address id %1', $address->getId())
                );
            }
            //Finding available customer's addresses
            $applicableAddressIds = array_map(function ($address) {
                /** @var \Magento\Customer\Api\Data\AddressInterface $address */
                return $address->getId();
            }, $this->customerRepository->getById($customerId)->getAddresses());
            if (!in_array($address->getCustomerAddressId(), $applicableAddressIds)) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Invalid customer address id %1', $address->getCustomerAddressId())
                );
            }
        }
    }
}
