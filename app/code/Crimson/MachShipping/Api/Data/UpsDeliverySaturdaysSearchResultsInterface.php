<?php
/**
 * @namespace   Crimson
 * @module      MachShipping
 * @date        06/07/2022 12:00 am
 * @brief
 */

namespace Crimson\MachShipping\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface UpsDeliverySaturdaysSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface[]
     */
    public function getItems();
    
    /**
     * @param \Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
