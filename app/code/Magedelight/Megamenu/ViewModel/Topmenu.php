<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\ViewModel;

use Magedelight\Megamenu\Api\Data\CacheInterfaceFactory;
use Magedelight\Megamenu\Helper\Cache;
use Magedelight\Megamenu\Model\CacheRepository;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Topmenu implements ArgumentInterface
{
    /**
     * @var Cache
     */
    protected $cacheHelper;

    /**
     * @var CacheInterfaceFactory
     */
    protected $cacheFactory;

    /**
     * @var CacheRepository
     */
    protected $cacheRepository;

    /**
     * Topmenu constructor.
     * @param Cache $cacheHelper
     * @param CacheInterfaceFactory $cacheFactory
     * @param CacheRepository $cacheRepository
     */
    public function __construct(
        Cache $cacheHelper,
        CacheInterfaceFactory $cacheFactory,
        CacheRepository $cacheRepository
    ) {
        $this->cacheHelper = $cacheHelper;
        $this->cacheFactory = $cacheFactory;
        $this->cacheRepository = $cacheRepository;
    }

    /**
     * Get Store Id
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return $this->cacheHelper->getStoreId();
    }

    /**
     * Enable Cache Menu
     *
     * @return mixed
     */
    public function enableCacheMenu()
    {
        return $this->cacheHelper->enableCustomMenu();
    }

    /**
     * Get Store Menu Key
     *
     * @return string
     */
    public function getStoreMenuKey(): string
    {
        return $this->cacheHelper->getStoreMenuKey().'_'.$this->getStoreId();
    }

    /**
     * Get Menu Data
     *
     * @return mixed
     */
    public function getMenuData()
    {
        return $this->cacheHelper->getCustomVariable($this->getStoreMenuKey());
    }

    /**
     * Save variable by Code
     *
     * @param string $variableCode
     * @param string $fileContent
     * @return void
     */
    public function saveVariableByCode($variableCode, $fileContent)
    {
        $cacheData = $this->cacheRepository->loadByName($variableCode);
        $cacheDataCount = count($cacheData->getData());
        if ($cacheDataCount > 0) {
            $cacheModel = $this->cacheRepository->get($cacheData->getCacheId());
            $cacheModel->setHtmlValue($fileContent);
        } else {
            $cacheModel = $this->cacheFactory->create();
            $data = [
                'name' => $variableCode,
                'html_value' => $fileContent,
                'store_id' => $this->getStoreId()
            ];

            $cacheModel->setData($data);
        }

        $this->cacheRepository->save($cacheModel);
    }
}
