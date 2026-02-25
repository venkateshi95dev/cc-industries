<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/15/2019 1:37 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Block;

use Crimson\MachCatalogRequest\Model\Config;
use Crimson\MachCatalogRequest\Model\Source\AvailableCatalogs;
use Magento\Cms\Block\BlockByIdentifier;
use Magento\Customer\Model\Session;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

class Form extends \Magento\Directory\Block\Data
{

    CONST GUEST_COSTUMER_CMS_BLOCK_IDENTIFIER = 'guest_catalog_request_account_creation';

    public function __construct(
        Template\Context $context,
        protected Config $catalogRequestConfig,
        protected AvailableCatalogs $availableCatalogs,
        protected Session $customerSession,
        Data $directoryHelper,
        EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        protected RegionFactory $regionFactory,
        protected CountryInformationAcquirerInterface $countryInformationAcquirer,
        protected BlockByIdentifier $blockByIdentifier,
        protected Context $httpContext,
        array $data = []
    ) {
        parent::__construct(
            $context, $directoryHelper, $jsonEncoder, $configCacheType, $regionCollectionFactory,
            $countryCollectionFactory, $data
        );
    }

    public function getRequestPageUrl(): string
    {
        return $this->getUrl('catalog/request/form');
    }

    public function getCatalogRequestFooterImageUrl(): ?string
    {
        return $this->catalogRequestConfig->getFooterRequestCatalogImageUrl();
    }

    public function getActionUrl(): string
    {
        return $this->getUrl('catalog/request/formPost');
    }

    public function getAvailableCatalogs(): array
    {
        return $this->availableCatalogs->toOptionArray();
    }

    public function getHtmlId($key)
    {
        return preg_replace('/[^0-9a-z_\-]/i', '', $key);
    }

    public function getCatalogValue($availableCatalog): string
    {
        return $availableCatalog[AvailableCatalogs::CATALOG_VALUE] ?? '';
    }

    public function getCatalogLabel($availableCatalog): string
    {
        return $availableCatalog[AvailableCatalogs::CATALOG_LABEL] ?? '';
    }

    public function getCatalogImageUrl($availableCatalog): string
    {
        return $availableCatalog[AvailableCatalogs::CATALOG_IMAGE_URL] ?? '';
    }

    public function getFormData()
    {
        $data = $this->getData('form_data');
        if ($data === null) {
            $formData = $this->customerSession->getFormData();
            $data = new \Magento\Framework\DataObject();
            if ($formData) {
                $data->addData($formData);
            }
            if (isset($data['region_id'])) {
                $data['region_id'] = (int)$data['region_id'];
            }
            $this->setData('form_data', $data);
        }

        return $data;
    }

    public function isCatalogSelected($value): bool
    {
        $catalogData = $this->getFormData()->getCatalog();
        if (!$catalogData) {
            return false;
        }

        return in_array($value, $catalogData, true);
    }

    public function getRegion()
    {
        if ($region = $this->getFormData()->getRegion()) {
            return $region;
        } elseif ($region = $this->getFormData()->getRegionId()) {
            return $region;
        }

        return null;
    }

    public function getCountryId(): int
    {
        $countryId = $this->getFormData()->getCountryId();
        if ($countryId) {
            return $countryId;
        }

        return parent::getCountryId();
    }

    public function getConfig(string $path): ?string
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    public function getAllStates(): ?array
    {
        return $this->countryInformationAcquirer->getCountryInfo("US")->getAvailableRegions();
    }

    public function getAllCountries()
    {
        return $this->countryInformationAcquirer->getCountriesInfo();
    }

    public function isCustomerLoggedIn(): bool
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    public function getCMSBlockByIdentifier(string $identifier): string
    {
        return $this->blockByIdentifier->setData('identifier', $identifier)->toHtml();
    }
}
