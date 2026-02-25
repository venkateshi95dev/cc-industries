<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class DeleteAWFaqArticlesAttribute implements DataPatchInterface
{

    CONST ATTR_NAME = 'aw_faq_articles';

    public function __construct(
        private readonly EavSetupFactory $eavSetupFactory
    ) {}

    /**
     * @throws CouldNotSaveException
     */
    public function apply(): void
    {
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,
            self::ATTR_NAME);
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
