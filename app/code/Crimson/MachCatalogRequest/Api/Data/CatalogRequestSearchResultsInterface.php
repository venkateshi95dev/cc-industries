<?php
/**
 * @namespace   Crimson
 * @module      MachCatalogRequest
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/14/2019 4:09 PM
 * @brief
 */

namespace Crimson\MachCatalogRequest\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface CatalogRequestSearchResultsInterface
 * @package Crimson\MachCatalogRequest\Api\Data
 */
interface CatalogRequestSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get CatalogRequest list.
     * @return CatalogRequestInterface[]
     */
    public function getItems();

    /**
     * Set request_id list.
     * @param CatalogRequestInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
