<?php
/**
 * @namespace   Crimson
 * @module      Brand
 * @author      Dario Grau
 * @email       dgrau@crimsonagility.com
 * @date        01/17/2019
 */
namespace Crimson\Brand\Api;

interface BrandRepositoryInterface
{
    /**
     * Get brand by Brand ID.
     *
     * @param int $brandId
     * @return \Crimson\Brand\Api\Data\BrandInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If brand with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($brandId);

    /**
     * Create or update a brand.
     *
     * @param \Crimson\Brand\Api\Data\BrandInterface $brand
     * @return \Crimson\Brand\Api\Data\BrandInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Crimson\Brand\Api\Data\BrandInterface $brand);

    /**
     * Delete brand.
     *
     * @param \Crimson\Brand\Api\Data\BrandInterface $brand
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Crimson\Brand\Api\Data\BrandInterface $brand);

    /**
     * Retrieve brands which match a specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Crimson\Brand\Api\Data\BrandSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete brand by Brand ID.
     *
     * @param int $brandId
     * @return \Crimson\Brand\Api\Data\BrandInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If brand with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($brandId);
}