<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Megamenu\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface LabelRepositoryInterface
{

    /**
     * Save Label
     * @param \Magedelight\Megamenu\Api\Data\LabelInterface $label
     * @return \Magedelight\Megamenu\Api\Data\LabelInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Magedelight\Megamenu\Api\Data\LabelInterface $label
    );

    /**
     * Retrieve Label
     * @param string $labelId
     * @return \Magedelight\Megamenu\Api\Data\LabelInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($labelId);

    /**
     * Retrieve Label matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Megamenu\Api\Data\LabelSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Label
     * @param \Magedelight\Megamenu\Api\Data\LabelInterface $label
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Magedelight\Megamenu\Api\Data\LabelInterface $label
    );

    /**
     * Delete Label by ID
     * @param string $labelId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($labelId);
}

