<?php
namespace Crimson\CorvetteCentral\Plugin\Customer\Address;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Store\Model\StoreManagerInterface;

class AbstractAddressPlugin
{
    public function __construct(
        private StoreManagerInterface $storeManager,
        private DirectoryHelper $directoryHelper
    )
    {
    }

    /**
     * After plugin for getRegionId
     *
     * @param \Magento\Customer\Model\Address\AbstractAddress $subject
     * @param int|string|null $result
     * @return int|string|null
     */
    public function afterGetRegionId(
        \Magento\Customer\Model\Address\AbstractAddress $subject,
                                                        $result
    ) {
        $CCStoreId = $this->storeManager->getStore(CorvetteCentralStoreInterface::CORVETTE_CENTRAL_STORE_CODE)->getId();
        if ($this->storeManager->getStore()->getId()!==$CCStoreId) {
            return $result;
        }
        $countryId = $subject->getCountryId();

        // If country does not require region, force null
        if ($countryId && !$this->directoryHelper->isRegionRequired($countryId)) {
            return null;
        }

        return $result;
    }
}
