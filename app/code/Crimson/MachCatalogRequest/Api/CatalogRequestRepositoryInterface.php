<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/14/2019 4:08 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Api;

use Crimson\MachCatalogRequest\Api\Data\CatalogRequestInterface;
use Crimson\MachCatalogRequest\Api\Data\CatalogRequestSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface CatalogRequestRepositoryInterface
 * @package Crimson\MachCatalogRequest\Api
 */
interface CatalogRequestRepositoryInterface
{

    /**
     * Save CatalogRequest
     * @param CatalogRequestInterface $catalogRequest
     * @return CatalogRequestInterface
     * @throws LocalizedException
     */
    public function save(
        CatalogRequestInterface $catalogRequest
    );

    /**
     * Retrieve CatalogRequest
     *
     * @param string $requestId
     *
     * @return CatalogRequestInterface
     * @throws LocalizedException
     */
    public function getById($requestId);

    /**
     * Retrieve CatalogRequest matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return CatalogRequestSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete CatalogRequest
     * @param CatalogRequestInterface $catalogRequest
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        CatalogRequestInterface $catalogRequest
    );

    /**
     * Delete CatalogRequest by ID
     * @param string $requestId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($requestId);
}
