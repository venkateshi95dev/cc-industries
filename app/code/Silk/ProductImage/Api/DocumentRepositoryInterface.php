<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Api;

/**
 * CMS document CRUD interface.
 * @api
 * @since 100.0.2
 */
interface DocumentRepositoryInterface
{
    /**
     * Save document.
     *
     * @param \Silk\ProductImage\Api\Data\DocumentInterface $document
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\DocumentInterface $document);

    /**
     * Retrieve document.
     *
     * @param int $documentId
     * @return \Silk\ProductImage\Api\Data\DocumentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($documentId);

    /**
     * Retrieve documents matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Silk\ProductImage\Api\Data\DocumentSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete document.
     *
     * @param \Silk\ProductImage\Api\Data\DocumentInterface $document
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\DocumentInterface $document);

    /**
     * Delete document by ID.
     *
     * @param int $documentId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($documentId);
}
