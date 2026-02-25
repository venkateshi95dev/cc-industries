<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Api\Data;

interface CacheSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Cache list.
     *
     * @return \Magedelight\Megamenu\Api\Data\CacheInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     *
     * @param \Magedelight\Megamenu\Api\Data\CacheInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
