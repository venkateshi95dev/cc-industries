<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 5:08 PM
 * @brief
 */

namespace Crimson\MachCatalog\Setup\Patch\Data;

use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class UpdateBackorderConfiguration implements
    DataPatchInterface,
    PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * @param ModuleDataSetupInterface                   $moduleDataSetup
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resourceConfig  = $resourceConfig;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {

        $this->resourceConfig->saveConfig(
            Configuration::XML_PATH_BACKORDERS,
            \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY,
            'default',
            0
        );
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
        return '2.3.0';
    }
}
