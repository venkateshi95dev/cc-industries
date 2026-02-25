<?php

namespace Crimson\Testimonial\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface CategorySearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get category list.
     * @return \Crimson\Testimonial\Api\Data\CategoryInterface[]
     */
    public function getItems(): array;

    /**
     * Set category list.
     * @param \Crimson\Testimonial\Api\Data\CategoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
