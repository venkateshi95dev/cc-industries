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
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestPaymentInformationManagementInterfacePlugin
 * @package Crimson\Demographics\Plugin\Api
 */
class GuestPaymentInformationManagementInterfacePlugin
{
    protected $_logger;

    protected $_quoteRepository;

    protected $_quoteIdMaskFactory;

    protected $_helper;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        LoggerInterface $logger,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        Data $helper
    ) {
        $this->_logger = $logger;
        $this->_quoteRepository = $quoteRepository;
        $this->_quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->_helper = $helper;
    }

    /**
     * @param GuestPaymentInformationManagementInterface $subject
     * @param $cartId
     * @param $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return array
     * @throws CouldNotSaveException
     */
    public function beforeSavePaymentInformation(GuestPaymentInformationManagementInterface $subject, $cartId, $email, PaymentInterface $paymentMethod, AddressInterface $billingAddress = null): array
    {
        $demographics = null;
        $additionalData = $paymentMethod->getAdditionalData();

        if ($this->_helper->isEnabled() && ($this->_helper->isRequired() && empty($additionalData['demographics_code']))) {
            throw new CouldNotSaveException(__('Please select your Corvette generation to continue'));
        }

        if (!empty($additionalData['demographics_code'])) {
            $demographics = $additionalData['demographics_code'];

            try {

                $quoteIdMask = $this->_quoteIdMaskFactory->create()->load($cartId, 'masked_id');

                $quote = $this->_quoteRepository->getActive($quoteIdMask->getQuoteId());
                $quote->setData('car_demos', $demographics);

            } catch (\Exception $e) {
                $this->_logger->critical('error saving demographics: '.$e->getMessage());
            }
        }

        return [$cartId, $email, $paymentMethod, $billingAddress];
    }
}
