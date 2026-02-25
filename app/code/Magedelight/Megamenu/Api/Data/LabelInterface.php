<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Megamenu\Api\Data;

interface LabelInterface
{

    public const BACKGROUND_COLOR = 'background_color';
    public const PRODUCT_ASSIGN = 'product_assign';
    public const TEXT_COLOR = 'text_color';
    public const LABEL_ID = 'label_id';
    public const SHAPE = 'shape';
    public const CATEGORY_ASSIGN = 'category_assign';
    public const TEXT = 'text';
    public const STORE_ID = 'store_id';

    /**
     * Get label_id
     * @return string|null
     */
    public function getLabelId();

    /**
     * Set label_id
     * @param string $labelId
     * @return \Magedelight\Megamenu\Label\Api\Data\LabelInterface
     */
    public function setLabelId($labelId);

    /**
     * Get shape
     * @return string|null
     */
    public function getShape();

    /**
     * Set shape
     * @param string $shape
     * @return \Magedelight\Megamenu\Label\Api\Data\LabelInterface
     */
    public function setShape($shape);

    /**
     * Get text
     * @return string|null
     */
    public function getText();

    /**
     * Set text
     * @param string $text
     * @return \Magedelight\Megamenu\Label\Api\Data\LabelInterface
     */
    public function setText($text);

    /**
     * Get text_color
     * @return string|null
     */
    public function getTextColor();

    /**
     * Set text_color
     * @param string $textColor
     * @return \Magedelight\Megamenu\Label\Api\Data\LabelInterface
     */
    public function setTextColor($textColor);

    /**
     * Get background_color
     * @return string|null
     */
    public function getBackgroundColor();

    /**
     * Set background_color
     * @param string $backgroundColor
     * @return \Magedelight\Megamenu\Label\Api\Data\LabelInterface
     */
    public function setBackgroundColor($backgroundColor);

    /**
     * Get product_assign
     * @return string|null
     */
    public function getProductAssign();

    /**
     * Set product_assign
     * @param string $productAssign
     * @return \Magedelight\Megamenu\Label\Api\Data\LabelInterface
     */
    public function setProductAssign($productAssign);

    /**
     * Get category_assign
     * @return string|null
     */
    public function getCategoryAssign();

    /**
     * Set category_assign
     * @param string $categoryAssign
     * @return \Magedelight\Megamenu\Label\Api\Data\LabelInterface
     */
    public function setCategoryAssign($categoryAssign);

    /**
     * Get store_id
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param int $storeId
     * @return \Magedelight\Megamenu\Label\Api\Data\LabelInterface
     */
    public function setStoreId($storeId);
}

