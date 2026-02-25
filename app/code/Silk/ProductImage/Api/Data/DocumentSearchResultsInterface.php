<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for cms page search results.
 * @api
 * @since 100.0.2
 */
interface DocumentSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get pages list.
     *
     * @return \Silk\ProductImage\Api\Data\DocumentInterface[]
     */
    public function getItems();

    /**
     * Set pages list.
     *
     * @param \Silk\ProductImage\Api\Data\DocumentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
