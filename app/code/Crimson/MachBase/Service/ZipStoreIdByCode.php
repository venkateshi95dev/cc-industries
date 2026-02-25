<?php

namespace Crimson\MachBase\Service;

use Crimson\MachBase\Model\MachConfig;
use Magento\Store\Api\StoreRepositoryInterface;

class ZipStoreIdByCode
{

    public function __construct(
        protected StoreRepositoryInterface $storeRepository
    ) {}

    /**
     * @return int|null
     */
    public function get(): ?int
    {
        try {
            return $this->storeRepository->get(MachConfig::ZIP_STORE_CODE)->getId();
        } catch (\Exception $e) {
            return null;
        }
    }
}
