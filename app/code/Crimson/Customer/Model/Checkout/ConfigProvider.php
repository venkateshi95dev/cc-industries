<?php
declare(strict_types=1);

namespace Crimson\Customer\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;

class ConfigProvider implements ConfigProviderInterface
{
    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected CompanyManagementInterface $companyManagement,
        protected CurrentCustomer $currentCustomer
    ) {
    }

    /**
     * @return array
     */
    public function getConfig() : array
    {
        $config = [];
        try {
            $customerId = $this->currentCustomer->getCustomerId();
            if (!$customerId) {
                return $config;
            }

            $company = $this->companyManagement->getByCustomerId($customerId);

            if ($company) {
                $config['company'] = [
                    'company_id'    => $company->getId(),
                    'company_name'  => $company->getCompanyName(),
                    'street'        => $company->getStreet(),
                    'city'          => $company->getCity(),
                    'region'        => $company->getRegion(),
                    'region_id'     => $company->getRegionId(),
                    'postcode'      => $company->getPostcode(),
                    'country_id'    => $company->getCountryId(),
                    'telephone'     => $company->getTelephone(),
                    'vat_id'        => $company->getVatTaxId()
                ];
            }
        } catch (\Exception $e) {}

        return $config;
    }
}
