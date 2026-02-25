<?php

namespace Crimson\Catalog\Model\Service\Sections\Purchased;

use Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection;

/**
 * Class BestsellersCollection
 * @package Crimson\Catalog\Model\Service\Sections\Purchased
 */
class BestsellersCollection extends Collection
{

    /**
     * Rating limit
     *
     * @var int
     */
    protected $_ratingLimit = null;
}
