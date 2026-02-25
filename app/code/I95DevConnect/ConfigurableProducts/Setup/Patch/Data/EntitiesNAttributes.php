<?php

/**
 * @author Subhan
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_ConfigurableProducts
 */

namespace I95DevConnect\ConfigurableProducts\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use I95DevConnect\MessageQueue\Api\LoggerInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class EntitiesNAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Magento\Eav\Model\Entity\Attribute\Set $attributeSet
     */
    private $attributeSet;

    /**
     * @var Magento\Framework\App\State $appState
     */
    private $appState;

    /**
     * @var LoggerInterface $logger
     */
    public $logger;

    /**
     * EntitiesNAttributes constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Set $attributeSet
     * @param State $appState
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Set $attributeSet,
        State $appState,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->appState = $appState;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSet = $attributeSet;
        $this->logger = $logger;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        try {
            $this->appState->setAreaCode('global');
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                'critical'
            );
        }
        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('i95dev_entity'),
            [
                [
                    'entity_name' => 'Configurable Product',
                    'entity_code' => 'configurableproduct',
                    'sort_order' => 5,
                    'support_for_inbound' => true,
                    'support_for_outbound' => false
                ]
            ]
        );
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(Product::ENTITY, 'parentsku', [
            'group' => 'General',
            'backend' => ArrayBackend::class,
            'frontend' => '',
            'label' => 'ParentSku',
            'required' => false,
            'input' => 'hidden',
            'global' => Attribute::SCOPE_WEBSITE,
            'visible' => false,
            'user_defined' => true,
            'apply_to' => '',
            'visible_on_front' => false,
            'used_in_product_listing' => false,
        ]);
    }

    /**
     * Get Aliases
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get Dependencies
     *
     * @return array
     */
    public static function getDependencies() // NOSONAR
    {
        return [

        ];
    }

    /**
     * Get patch version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '2.0.6';
    }
}
