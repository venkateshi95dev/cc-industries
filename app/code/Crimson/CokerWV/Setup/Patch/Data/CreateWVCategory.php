<?php

namespace Crimson\CokerWV\Setup\Patch\Data;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;

class CreateWVCategory implements DataPatchInterface
{

    CONST WV_ROOT_CATEGORY_NAME = 'Wheel Vintiques Category';

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
        $category->setName(self::WV_ROOT_CATEGORY_NAME)
                 ->setIsActive(true)
                 ->setUrlKey('wheel-vintiques-category')
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
