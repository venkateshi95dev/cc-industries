<?php

namespace Crimson\CorvetteCentral\Setup\Patch\Data;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateCorvetteCentralCategory implements DataPatchInterface
{

    CONST CORVETTE_CENTRAL_ROOT_CATEGORY_NAME = 'Corvette Central';

    public function __construct(
        private readonly CategoryFactory $categoryFactory,
        private readonly CategoryRepository $categoryRepository
    ) {}

    /**
     * @throws CouldNotSaveException
     */
    public function apply(): void
    {
        $category = $this->categoryFactory->create();
        $category->setName(self::CORVETTE_CENTRAL_ROOT_CATEGORY_NAME)
            ->setIsActive(true)
            ->setUrlKey('corvette-central')
            ->setParentId(1);

        $this->categoryRepository->save($category);
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
