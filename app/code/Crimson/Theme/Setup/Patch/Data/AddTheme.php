<?php
/**
 * @namespace   Crimson
 * @module      ${MODULE}
 * @author      Peter Talavera
 * @email       ptalavera@crimsonagility.com
 * @date        3/1/2019 10:51 AM
 * @brief
 */
namespace Crimson\Theme\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Theme\Model\DesignConfigRepository;
use Magento\Store\Model\ScopeInterface;
use Magento\Theme\Model\Theme\Registration as ThemeRegistration;

class AddTheme implements
    DataPatchInterface,
    PatchVersionInterface
{

    const THEME_NAME = 'ZipCorvette/default';

    private $_config;
    private $_collectionFactory;
    private $_storeRepository;
    private $_resourceConfig;

    /**
     * @var \Magento\Theme\Model\Theme\Registration
     */
    private $themeRegistration;

    /**
     * @var DesignConfigRepository
     */
    protected $_designConfigRepository;


    public function __construct(
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionFactory,
        \Magento\Theme\Model\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeRepository,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface  $resourceConfig,
        DesignConfigRepository $designConfigRepository,
        ThemeRegistration $themeRegistration
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->_config = $config;
        $this->_storeRepository = $storeRepository;
        $this->_resourceConfig = $resourceConfig;
        $this->_designConfigRepository = $designConfigRepository;
        $this->themeRegistration = $themeRegistration;
    }

    public function apply(

    )
    {
        $stores = $this->_storeRepository->getStores();
        $themes = $this->_collectionFactory->create()->loadRegisteredThemes();
        $websiteid = 1;

        foreach ($themes as $theme) {
            if ($theme->getCode() == self::THEME_NAME) {
                foreach ($stores as $store) {
                    if ($store->getWebsiteId() == $websiteid) {
                        $this->_config->assignToStore(
                            $theme,
                            [$store->getId()],
                            ScopeInterface::SCOPE_STORES
                        );
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.1';
    }
}