<?php

declare(strict_types=1);

namespace Silk\Coker\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Psr\Log\LoggerInterface;

/**
 * Class AddReturnTypePatch
 * @package Silk\Coker\Setup\Patch\Data
 */
class AddSoldAsSetToProduct implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $setup
     * @param LoggerInterface $logger
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        LoggerInterface $logger,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->setup = $setup;
        $this->logger = $logger;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $this->setup->startSetup();

        try {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create();
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    'sold_as_set',
                    [
                            'type' => 'int',
                            'backend' => '',
                            'frontend' => '',
                            'label' => 'Sold As Set',
                            'input' => 'text',
                            'class' => '',
                            'source' => '',
                            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_WEBSITE,
                            'group' => 'General',
                            'sort_order' => 10,
                            'visible' => 1,
                            'required' => 0,
                            'user_defined' => 1,
                            'searchable' => 0,
                            'filterable' => 0,
                            'comparable' => 0,
                            'visible_on_front' => 0,
                            'used_in_product_listing' => 0,
                            'unique' => 0,
                            'apply_to' => '',
                            'default_value' => 1
                    ]
            );

        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        $this->setup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
