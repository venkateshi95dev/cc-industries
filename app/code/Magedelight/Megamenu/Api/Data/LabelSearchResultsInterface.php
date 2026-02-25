<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */
declare(strict_types=1);

namespace Magedelight\Megamenu\Api\Data;

interface LabelSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Label list.
     * @return \Magedelight\Megamenu\Api\Data\LabelInterface[]
     */
    public function getItems();

    /**
     * Set shape list.
     * @param \Magedelight\Megamenu\Api\Data\LabelInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

