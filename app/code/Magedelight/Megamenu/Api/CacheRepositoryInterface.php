<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Api;

interface CacheRepositoryInterface
{
    /**
     * Save Cache
     *
     * @param \Magedelight\Megamenu\Api\Data\CacheInterface $cache
     * @return \Magedelight\Megamenu\Api\Data\CacheInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Magedelight\Megamenu\Api\Data\CacheInterface $cache
    );

    /**
     * Retrieve Cache
     *
     * @param string $cacheId
     * @return \Magedelight\Megamenu\Api\Data\CacheInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($cacheId);

    /**
     * Retrieve Cache matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Megamenu\Api\Data\CacheSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Cache
     *
     * @param \Magedelight\Megamenu\Api\Data\CacheInterface $cache
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Magedelight\Megamenu\Api\Data\CacheInterface $cache
    );

    /**
     * Delete Cache by ID
     *
     * @param string $cacheId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($cacheId);
}
