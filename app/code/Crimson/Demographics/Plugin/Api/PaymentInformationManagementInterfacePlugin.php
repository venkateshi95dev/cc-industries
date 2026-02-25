<?php

/**
 * @namespace   Crimson
 * @module      Demographics
 * @author      Chad Carlson
 * @email       ccarlson@crimsonagility.com
 * @brief
 */

namespace Crimson\Demographics\Plugin\Api;

use Crimson\Demographics\Helper\Data;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class PaymentInformationManagementInterfacePlugin
 * @package Crimson\Demographics\Plugin\Api
 */
class PaymentInformationManagementInterfacePlugin
{
    protected $_customerRepository;

    protected $_logger;

    protected $_quoteRepository;

    protected $_helper;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        CartRepositoryInterface $quoteRepository,
        Data $helper
    ) {
        $this->_customerRepository = $customerRepository;
        $this->_logger = $logger;
        $this->_quoteRepository = $quoteRepository;
        $this->_helper = $helper;
    }

    /**
     * @param PaymentInformationManagementInterface $subject
     * @param $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return array
     * @throws \Exception
     */
    public function beforeSavePaymentInformation(PaymentInformationManagementInterface $subject, $cartId, PaymentInterface $paymentMethod, AddressInterface $billingAddress = null): array
    {
        $demographics = null;
        $additionalData = $paymentMethod->getAdditionalData();

        if ($this->_helper->isEnabled() && ($this->_helper->isRequired() && empty($additionalData['demographics_code']))) {
            throw new CouldNotSaveException(__('Please select your Corvette generation to continue'));
        }

        if (!empty($additionalData['demographics_code'])) {

            $demographics = $additionalData['demographics_code'];
            try {
                $quote = $this->_quoteRepository->get($cartId);
                $quote->setData('car_demos', $demographics);
                $this->_quoteRepository->save($quote);

                $customerId = $quote->getCustomer()->getId();
                $customer = $this->_customerRepository->getById($customerId);
                $customer->setCustomAttribute('car_demos', $demographics);
                $this->_customerRepository->save($customer);
            } catch (\Exception $e) {
                $this->_logger->critical('error saving demographics: '.$e->getMessage());
            }
        }

        return [$cartId, $paymentMethod, $billingAddress];
    }
}
