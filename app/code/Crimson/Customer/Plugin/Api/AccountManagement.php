<?php

namespace Crimson\Customer\Plugin\Api;

use Crimson\Customer\Service\CatalogRequestRedirect;
use Crimson\Customer\Model\Config;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Company\Api\Data\CompanyInterface;
use Crimson\CokerWV\Api\CokerStoreInterface;
use Psr\Log\LoggerInterface;
use Exception;

class AccountManagement
{
    public function __construct(
        protected CatalogRequestRedirect $catalogRequestRedirect,
        private StoreManagerInterface $storeManager,
        private CompanyManagementInterface $companyManagement,
        private AddressInterfaceFactory $addressFactory,
        private Config $config,
        private LoggerInterface $logger
    ) {}

    public function afterAuthenticate(AccountManagementInterface $subject, CustomerInterface $customer)
    {
        try {
            $this->catalogRequestRedirect->unsetComingFromCatalogRequest();
        } catch (\Exception $e) {

        } finally {
            return $customer;
        }
    }

    public function afterCreateAccount(AccountManagementInterface $subject, CustomerInterface $customer)
    {
        try {
            $this->catalogRequestRedirect->unsetComingFromCatalogRequest();
        } catch (\Exception $e) {

        } finally {
            return $customer;
        }
    }

    /**
     * @param AccountManagementInterface $subject
     * @param AddressInterface|null $result
     * @param int $customerId
     * @return AddressInterface|null
     */
    public function afterGetDefaultBillingAddress(
        AccountManagementInterface $subject,
        ?AddressInterface $result,
        int $customerId
    ) : ?AddressInterface
    {
        try {
            $website = $this->storeManager->getWebsite();

            if (!$this->config->useCompanyAddressAsBillingAddress((int)$website->getId())) {
                return $result;
            }

            $company = $this->companyManagement->getByCustomerId($customerId);

            if (!$company) {
                return $result;
            }

            return $this->getCompanyAddress($company);

        } catch (Exception $e) {
            $this->logger->error("Wasn't able to get the company billing address for the customer $customerId");
            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    /**
     * @param CompanyInterface $company
     * @return AddressInterface
     */
    protected function getCompanyAddress(CompanyInterface $company) : AddressInterface
    {
        $street = $company->getStreet();
        $city = $company->getCity();
        $region = $company->getRegion();
        $regionId = $company->getRegionId();
        $postcode = $company->getPostcode();
        $countryId = $company->getCountryId();
        $telephone = $company->getTelephone();
        $companyName = $company->getCompanyName();
        $legalName = $company->getLegalName();

        $address = $this->addressFactory->create();
        $address->setFirstname($companyName)
            ->setLastname($legalName)
            ->setStreet($street)
            ->setCity($city)
            ->setRegion($region)
            ->setRegionId($regionId)
            ->setPostcode($postcode)
            ->setCountryId($countryId)
            ->setTelephone($telephone);

        return $address;
    }
}
