<?php
namespace Silk\CKDocument\Api;

/**
 * CMS document CRUD interface.
 * @api
 * @since 100.0.2
 */
interface CKDocumentRepositoryInterface
{
    /**
     * Save document.
     *
     * @param \Silk\CKDocument\Api\Data\CKDocumentInterface $document
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\CKDocumentInterface $document);

    /**
     * Retrieve document.
     *
     * @param int $documentId
     * @return \Silk\CKDocument\Api\Data\CKDocumentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($documentId);

    /**
     * Retrieve documents matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Silk\CKDocument\Api\Data\CKDocumentSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete document.
     *
     * @param \Silk\CKDocument\Api\Data\CKDocumentInterface $document
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\CKDocumentInterface $document);

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
