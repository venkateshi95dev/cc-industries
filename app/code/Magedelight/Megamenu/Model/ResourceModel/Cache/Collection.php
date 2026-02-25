<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Model\ResourceModel\Cache;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magedelight\Megamenu\Model\Cache as CacheModel;
use Magedelight\Megamenu\Model\ResourceModel\Cache as CacheResourceModel;

class Collection extends AbstractCollection
{

    /**
     * Cache Id field name
     *
     * @var string
     */
    protected $_idFieldName = 'cache_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            CacheModel::class,
            CacheResourceModel::class
        );
    }
}
