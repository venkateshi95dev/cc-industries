<?php
/**
 * @namespace   Crimson
 * @module      MachCatalog
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        5/13/2019 5:30 PM
 * @brief
 */

namespace Crimson\MachCatalog\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class UpdateBackorderStaticBlocks20190513 implements
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
            <p>By checking this box we will wait to ship your order until all items are in stock and ready to ship.</p><br/>
            <p>Do not check this box if you want us to ship the items that are in stock and then ship the backorders once they come in.</p>
HTML;

        $cmsBlock = $this->blockFactory->create();
        $cmsBlock = $cmsBlock->load('backorder_shipments_checkout', 'identifier');
        $cmsBlock->setTitle('Backorder Shipments Checkout')
            ->setIdentifier('backorder_shipments_checkout')
            ->setContent($content)
            ->setIsActive(1)
            ->setStores([0]);

        $cmsBlock->save();

        $cmsBlock = $this->blockFactory->create();
        $cmsBlock = $cmsBlock->load('backorder_shipments', 'identifier');
        $cmsBlock->setTitle('Backorder Shipments Cart')
            ->setIdentifier('backorder_shipments')
            ->setContent($content)
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
