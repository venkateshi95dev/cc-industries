<?php

declare(strict_types=1);

namespace Crimson\Testimonial\Api;

use Crimson\Testimonial\Api\Data\TestimonialInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface TestimonialRepositoryInterface
{

    /**
     * Save Testimonial
     * @param TestimonialInterface $post
     * @return TestimonialInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        TestimonialInterface $post
    );

    /**
     * Retrieve post
     * @param string $id
     * @return TestimonialInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Retrieve Testimonial matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Crimson\Testimonial\Api\Data\TestimonialSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Retrieve Testimonial matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Crimson\Testimonial\Api\Data\TestimonialSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPublishList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Testimonial
     * @param TestimonialInterface $testimonial
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        TestimonialInterface $testimonial
    );

    /**
     * Delete Testimonial by ID
     * @param string $testimonialId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($testimonialId);
}

