<?php
declare(strict_types=1);

namespace Silk\Coker\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AddCategoryTopDesc
 * @package Silk\Coker\Setup\Patch\Data
 */
class AddCategoryTopDesc implements DataPatchInterface
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
            $eavSetup = $this->eavSetupFactory->create(['setup' =>  $this->setup]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                    'top_description',
                    [
                        'type' => 'text',
                        'label' => 'Top Description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 3,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'wysiwyg_enabled' => true,
                        'is_html_allowed_on_front' => true,
                        'group' => 'General Information',
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
