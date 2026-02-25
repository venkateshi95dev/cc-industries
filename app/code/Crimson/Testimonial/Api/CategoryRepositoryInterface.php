<?php

declare(strict_types=1);

namespace Crimson\Testimonial\Api;

use Crimson\Testimonial\Api\Data\CategoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface CategoryRepositoryInterface
{

    /**
     * Save Category
     * @param CategoryInterface $category
     * @return CategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        CategoryInterface $category
    );

    /**
     * Retrieve category
     * @param string $categoryId
     * @return CategoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($categoryId);

    /**
     * Retrieve Category matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Crimson\Testimonial\Api\Data\CategorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Retrieve Category matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Crimson\Testimonial\Api\Data\CategorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPublishList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Category
     * @param CategoryInterface $category
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        CategoryInterface $category
    );

    /**
     * Delete Category by ID
     * @param $categoryId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($categoryId);
}

