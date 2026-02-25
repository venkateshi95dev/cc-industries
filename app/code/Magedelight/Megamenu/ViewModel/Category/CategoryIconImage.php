<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\ViewModel\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Image as CategoryImage;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Category image view model
 */
class CategoryIconImage implements ArgumentInterface
{
    private const ATTRIBUTE_NAME = 'category_icon_image';

    /**
     * @var CategoryImage
     */
    private $image;

    /**
     * Initialize dependencies.
     *
     * @param CategoryImage $image
     */
    public function __construct(CategoryImage $image)
    {
        $this->image = $image;
    }

    /**
     * Resolve category image URL
     *
     * @param Category $category
     * @param string $attributeCode
     * @return string
     * @throws LocalizedException
     */
    public function getUrl(Category $category, $attributeCode = self::ATTRIBUTE_NAME): string
    {
        return $this->image->getUrl($category, $attributeCode);
    }
}
