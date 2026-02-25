<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentralFlatRatePrepaid\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use Exception;

class CreateAirShipmentOkProductAttr implements DataPatchInterface
{

    public function __construct(
        protected ModuleDataSetupInterface $moduleDataSetup,
        protected EavSetupFactory $eavSetupFactory,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     */
    public function apply() : void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        try {
            $eavSetup->addAttribute(
                Product::ENTITY,
                'cc_airshipmentok',
                [
                    'label' => "Air Shipment Ok",
                    'type' => 'int',
                    'input' => 'boolean',
                    'source' => Boolean::class,
                    'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                    'required' => false,
                    'user_defined' => true,
                    'default' => 1,
                    'system' => false,
                    'group' => 'CorvetteCentral',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error("Wasn't able to create the attribute cc_airshipmentok");
            $this->logger->error($e->getMessage());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function getAliases() : array
    {
        return [];
    }

    public static function getDependencies() : array
    {
        return [];
    }
}
