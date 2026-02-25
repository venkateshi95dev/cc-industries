<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Api\Data;

interface CacheInterface
{

    public const STORE_ID = 'store_id';
    public const NAME = 'name';
    public const CACHE_ID = 'cache_id';
    public const HTML_VALUE = 'html_value';
    public const CACHE_TABLE = 'megamenu_cache';

    /**
     * Get cache_id
     *
     * @return string|null
     */
    public function getCacheId();

    /**
     * Set cache_id
     *
     * @param string $cacheId
     * @return \Magedelight\Megamenu\Cache\Api\Data\CacheInterface
     */
    public function setCacheId($cacheId);

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return \Magedelight\Megamenu\Cache\Api\Data\CacheInterface
     */
    public function setName($name);

    /**
     * Get store_id
     *
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set store_id
     *
     * @param string $storeId
     * @return \Magedelight\Megamenu\Cache\Api\Data\CacheInterface
     */
    public function setStoreId($storeId);

    /**
     * Get html_value
     *
     * @return string|null
     */
    public function getHtmlValue();

    /**
     * Set html_value
     *
     * @param string $htmlValue
     * @return \Magedelight\Megamenu\Cache\Api\Data\CacheInterface
     */
    public function setHtmlValue($htmlValue);
}
