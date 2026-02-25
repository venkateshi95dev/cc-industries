<?php

namespace Crimson\Testimonial\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface TestimonialSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get Testimonial list.
     * @return \Crimson\Testimonial\Api\Data\TestimonialInterface[]
     */
    public function getItems();

    /**
     * Set Testimonial list.
     * @param \Crimson\Testimonial\Api\Data\TestimonialInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
