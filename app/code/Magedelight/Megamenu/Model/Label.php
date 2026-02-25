<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Megamenu\Model;

use Magedelight\Megamenu\Api\Data\LabelInterface;
use Magento\Framework\Model\AbstractModel;

class Label extends AbstractModel implements LabelInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Magedelight\Megamenu\Model\ResourceModel\Label::class);
    }

    /**
     * @inheritDoc
     */
    public function getLabelId()
    {
        return $this->getData(self::LABEL_ID);
    }

    /**
     * @inheritDoc
     */
    public function setLabelId($labelId)
    {
        return $this->setData(self::LABEL_ID, $labelId);
    }

    /**
     * @inheritDoc
     */
    public function getShape()
    {
        return $this->getData(self::SHAPE);
    }

    /**
     * @inheritDoc
     */
    public function setShape($shape)
    {
        return $this->setData(self::SHAPE, $shape);
    }

    /**
     * @inheritDoc
     */
    public function getText()
    {
        return $this->getData(self::TEXT);
    }

    /**
     * @inheritDoc
     */
    public function setText($text)
    {
        return $this->setData(self::TEXT, $text);
    }

    /**
     * @inheritDoc
     */
    public function getTextColor()
    {
        return $this->getData(self::TEXT_COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setTextColor($textColor)
    {
        return $this->setData(self::TEXT_COLOR, $textColor);
    }

    /**
     * @inheritDoc
     */
    public function getBackgroundColor()
    {
        return $this->getData(self::BACKGROUND_COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setBackgroundColor($backgroundColor)
    {
        return $this->setData(self::BACKGROUND_COLOR, $backgroundColor);
    }

    /**
     * @inheritDoc
     */
    public function getProductAssign()
    {
        return $this->getData(self::PRODUCT_ASSIGN);
    }

    /**
     * @inheritDoc
     */
    public function setProductAssign($productAssign)
    {
        return $this->setData(self::PRODUCT_ASSIGN, $productAssign);
    }

    /**
     * @inheritDoc
     */
    public function getCategoryAssign()
    {
        return $this->getData(self::CATEGORY_ASSIGN);
    }

    /**
     * @inheritDoc
     */
    public function setCategoryAssign($categoryAssign)
    {
        return $this->setData(self::CATEGORY_ASSIGN, $categoryAssign);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }
}

