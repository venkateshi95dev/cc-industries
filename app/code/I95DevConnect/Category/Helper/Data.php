<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2022 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_Category
 */

namespace I95DevConnect\Category\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\CategoryFactory;

/**
 * Helper Class for Module
 */
class Data extends AbstractHelper
{
    /**
     * @var CategoryFactory
     */
    public $categoryFactory;

    /**
     * Class constructor to include all the dependencies
     *
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        CategoryFactory $categoryFactory
    ) {
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Get category id from target category code
     *
     * @param string $targetCategoryCode
     * @return int|null
     */
    public function getCatIdByTargetCatCode($targetCategoryCode)
    {
        return $this->categoryFactory->create()
            ->getCollection()
            ->addAttributeToFilter('target_category_code', $targetCategoryCode)
            ->getFirstItem()
            ->getId();
    }
}
