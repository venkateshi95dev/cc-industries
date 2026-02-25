<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Model;

use Magedelight\Megamenu\Api\Data\CacheInterface;
use Magento\Framework\Model\AbstractModel;

class Cache extends AbstractModel implements CacheInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Magedelight\Megamenu\Model\ResourceModel\Cache::class);
    }

    /**
     * @inheritDoc
     */
    public function getCacheId()
    {
        return $this->getData(self::CACHE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCacheId($cacheId)
    {
        return $this->setData(self::CACHE_ID, $cacheId);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
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

    /**
     * @inheritDoc
     */
    public function getHtmlValue()
    {
        return $this->getData(self::HTML_VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setHtmlValue($htmlValue)
    {
        return $this->setData(self::HTML_VALUE, $htmlValue);
    }
}
