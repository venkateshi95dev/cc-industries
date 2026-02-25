<?php
namespace Silk\CKDocument\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for cms page search results.
 * @api
 * @since 100.0.2
 */
interface CKDocumentSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get pages list.
     *
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface[]
     */
    public function getItems();

    /**
     * Set pages list.
     *
     * @param \Silk\CKDocument\Api\Data\CKDocumentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
