<?php
namespace Crimson\CorvetteCentral\Plugin\Directory\Helper;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\Json\Helper\Data as JsonData;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class DataPlugin
{

    public function __construct(
        private StoreManagerInterface $storeManager,
        private JsonData $jsonHelper
    )
    {
    }

    /**
     * Force isShowNonRequiredState() to return false for specific website(s)
     *
     * @param Data $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsShowNonRequiredState(
        Data $subject,
             $result
    ) {

        $CCStoreId = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();
        if ($this->storeManager->getStore()->getId()!==$CCStoreId) {
            return $result;
        }
        return false;
    }

    public function afterIsRegionRequired(
        Data $subject, $result, $countryId
    ) {
        $CCStoreId = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();
        if ($this->storeManager->getStore()->getId()!==$CCStoreId) {
            return $result;
        }
        return in_array($countryId,['US','CA']);
    }

    public function afterGetCountriesWithStatesRequired(
        Data $subject, $result, $asJson = false
    )
    {
        $CCStoreId = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();
        if ($this->storeManager->getStore()->getId()!==$CCStoreId) {
            return $result;
        }
        $value = 'US,CA';
        $countryList = preg_split('/\,/', $value, 0, PREG_SPLIT_NO_EMPTY);
        if ($asJson) {
            return $this->jsonHelper->jsonEncode($countryList);
        }
        return $countryList;
    }
}
