<?php
declare(strict_types=1);

namespace Crimson\Checkout\Plugin\Quote;

use Crimson\Customer\Model\Config;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Psr\Log\LoggerInterface;
use Exception;

class CompanyBillingQuotePlugin
{
    public function __construct(
        private CustomerSession $customerSession,
        private CompanyRepositoryInterface $companyRepository,
        private AddressInterfaceFactory $addressFactory,
        private CompanyManagementInterface $companyManagement,
        private Config $config,
        private LoggerInterface $logger
    ) {}

    /**
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return CartInterface[]
     */
    public function beforeSave(CartRepositoryInterface $subject, CartInterface $quote) : array
    {
        try {
            $websiteId = (int) $quote->getStore()->getWebsiteId();
        } catch (Exception $e) {
            $this->logger->error("Wasn't able to get the website ID");
            $this->logger->error($e->getMessage());
            return [$quote];
        }

        if (!$this->config->useCompanyAddressAsBillingAddress($websiteId)) {
            return [$quote];
        }


        $customer = $this->customerSession->getCustomer();
        $companyId = $this->companyManagement->getByCustomerId($customer->getId())->getId() ?? null;

        if ($companyId) {
            try {
                $company = $this->companyRepository->get($companyId);
            } catch (Exception $e) {
                $this->logger->error("Wasn't able to get the company with ID $companyId");
                $this->logger->error($e->getMessage());
                return [$quote];
            }

            $billingAddress = $quote->getBillingAddress();

            if (!$billingAddress) {
                $billingAddress = $this->addressFactory->create();
            }

            $billingAddress->setAddressType(AbstractAddress::TYPE_BILLING);
            $billingAddress->setFirstname($company->getCompanyName());
            $billingAddress->setLastname($company->getLegalName());
            $billingAddress->setEmail($company->getCompanyEmail());
            $billingAddress->setStreet($company->getStreet());
            $billingAddress->setCity($company->getCity());
            $billingAddress->setCountryId($company->getCountryId());
            $billingAddress->setRegion($company->getRegion());
            $billingAddress->setRegionId($company->getRegionId());
            $billingAddress->setPostcode($company->getPostcode());
            $billingAddress->setTelephone($company->getTelephone());
            $billingAddress->setCompany($company->getCompanyName());
            $billingAddress->setCustomerId($customer->getId());
            $billingAddress->setCustomerAddressId(null);
            $billingAddress->setQuote($quote);
            $billingAddress->setQuoteId((int) $quote->getId());

            $quote->setBillingAddress($billingAddress);
        }

        return [$quote];
    }

    /**
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @param $cartId
     * @return CartInterface
     */
    public function afterGet(
        CartRepositoryInterface $subject,
        CartInterface $quote,
        $cartId
    ): CartInterface {
        if (!$this->customerSession->isLoggedIn()) {
            return $quote;
        }

        try {
            $websiteId = (int) $quote->getStore()->getWebsiteId();
        } catch (Exception $e) {
            $this->logger->error("Wasn't able to get the website ID");
            $this->logger->error($e->getMessage());
            return $quote;
        }


        if (!$this->config->useCompanyAddressAsBillingAddress($websiteId)) {
            return $quote;
        }

        $customerId = (int)$this->customerSession->getCustomerId();
        $company = $this->companyManagement->getByCustomerId($customerId);

        if (!$company) {
            return $quote; // not a company user
        }

        // Build an AddressInterface from company data
        $billingAddress = $this->addressFactory->create();
        $billingAddress->setAddressType(AbstractAddress::TYPE_BILLING);
        $billingAddress->setFirstname($company->getCompanyName());
        $billingAddress->setLastname($company->getLegalName());
        $billingAddress->setEmail($company->getCompanyEmail());
        $billingAddress->setStreet($company->getStreet());
        $billingAddress->setCity($company->getCity());
        $billingAddress->setCountryId($company->getCountryId());
        $billingAddress->setRegion($company->getRegion());
        $billingAddress->setRegionId($company->getRegionId());
        $billingAddress->setPostcode($company->getPostcode());
        $billingAddress->setTelephone($company->getTelephone());
        $billingAddress->setCompany($company->getCompanyName());
        $billingAddress->setCustomerId($customerId);
        $billingAddress->setCustomerAddressId(null);
        $billingAddress->setQuote($quote);
        $billingAddress->setQuoteId((int) $quote->getId());

        $quote->setBillingAddress($billingAddress);

        return $quote;
    }
}
