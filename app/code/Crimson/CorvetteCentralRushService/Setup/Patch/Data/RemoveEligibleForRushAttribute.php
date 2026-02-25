<?php

declare(strict_types=1);

namespace Crimson\CorvetteCentralRushService\Setup\Patch\Data;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean as BooleanSource;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class RemoveEligibleForRushAttribute implements DataPatchInterface
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
        $attributeCode = "cc_eligible_for_rush";
        try {
            $eavSetup->removeAttribute(Product::ENTITY, $attributeCode);
        } catch (Exception $e) {
            $this->logger->error("Wasn't able to remove the attribute $attributeCode");
            $this->logger->error($e->getMessage());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array|string[]
     */
    public function getAliases() : array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies() : array
    {
        return [];
    }
}
