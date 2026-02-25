<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Helper;

use Magedelight\Megamenu\Api\Data\CacheInterfaceFactory;
use Magedelight\Megamenu\Model\CacheRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Variable\Model\VariableFactory;

class Cache extends AbstractHelper
{
    public const STORE_MENU = 'store_menu';
    public const SHORTCODE_MENU = 'shortcode_menu';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var VariableFactory
     */
    protected $varFactory;

    /**
     * @var CacheInterfaceFactory
     */
    protected $cacheFactory;

    /**
     * @var CacheRepository
     */
    protected $cacheRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param VariableFactory $varFactory
     * @param CacheInterfaceFactory $cacheFactory
     * @param CacheRepository $cacheRepository
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        VariableFactory $varFactory,
        CacheInterfaceFactory $cacheFactory,
        CacheRepository $cacheRepository
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->varFactory = $varFactory;
        $this->cacheFactory = $cacheFactory;
        $this->cacheRepository = $cacheRepository;
    }

    /**
     * Get Custom Variable
     *
     * @param string $variableCode
     * @return string
     */
    public function getCustomVariable($variableCode)
    {
        $cacheData = $this->cacheRepository->loadByName($variableCode);
        return $cacheData->getHtmlValue();
    }

    /**
     * Update Variable By Code
     *
     * @param string $variableCode
     * @return void
     * @throws \Exception
     */
    public function updateVariableByCode($variableCode)
    {
        $allStoreIds = array_keys($this->storeManager->getStores(true));
        foreach ($allStoreIds as $storeId) {
            $readVariableCode = $variableCode.'_'.$storeId;
            $cacheData = $this->cacheRepository->loadByName($readVariableCode);
            $cacheDataCount = count($cacheData->getData());
            if ($cacheDataCount > 0) {
                $cacheModel = $this->cacheRepository->get($cacheData->getCacheId());
                $cacheModel->setHtmlValue('');
                $this->cacheRepository->save($cacheModel);
            }
        }
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Enable Custom Menu
     *
     * @return boolean
     */
    public function enableCustomMenu()
    {
        return $this->scopeConfig->getValue(
            'magedelight/general/menu_custom_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Store menu Id
     *
     * @return mixed
     */
    public function getStoreMenuId()
    {
        return $this->scopeConfig->getValue(
            'magedelight/general/primary_menu',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Store Menu Key
     *
     * @return string
     */
    public function getStoreMenuKey()
    {
        return self::STORE_MENU;
    }
}
