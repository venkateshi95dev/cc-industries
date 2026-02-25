<?php
/**
 * @namespace   Crimson
 * @module      MachShipping
 * @date        06/07/2022 12:00 am
 * @brief
 */

namespace Crimson\MachShipping\Api;

interface UpsDeliverySaturdaysRepositoryInterface
{
    /**
     * @param \Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface $upsDeliverySaturdays
     *
     * @return \Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface $upsDeliverySaturdays);

    /**
     * @param int $id
     *
     * @return \Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

        /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param \Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface $upsDeliverySaturdays
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Crimson\MachShipping\Api\Data\UpsDeliverySaturdaysInterface $upsDeliverySaturdays);

    /**
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);
}
