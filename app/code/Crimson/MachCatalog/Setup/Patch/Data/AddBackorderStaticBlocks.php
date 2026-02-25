<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/13/2019 9:52 PM
 * @brief
 */

namespace Crimson\MachCatalog\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class AddBackorderStaticBlocks implements
    DataPatchInterface,
    PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @param ModuleDataSetupInterface        $moduleDataSetup
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Cms\Model\BlockFactory $blockFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        $content =<<<'HTML'
            <p>It looks like there are backorders in your cart. If you would like us to hold your order and ship every item at the same time to cut down on the number of shipments, please check this box. If you would like us to ship the items that are in stock as soon as possible and ship the backorders once they are available, leave this box unchecked.</p>
HTML;

        $cmsBlock = $this->blockFactory->create();
        $cmsBlock = $cmsBlock->load('backorder_shipments_checkout', 'identifier');
        $cmsBlock->setTitle('Backorder Shipments')
            ->setIdentifier('backorder_shipments_checkout')
            ->setContent($content)
            ->setIsActive(1)
            ->setStores([0]);

        $cmsBlock->save();

        $cmsBlock = $this->blockFactory->create();
        $cmsBlock = $cmsBlock->load('backorder_shipments', 'identifier');
        $cmsBlock->setTitle('Backorder Shipments')
            ->setIdentifier('backorder_shipments')
            ->setContent('...')
            ->setIsActive(1)
            ->setStores([0]);

        $cmsBlock->save();
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
