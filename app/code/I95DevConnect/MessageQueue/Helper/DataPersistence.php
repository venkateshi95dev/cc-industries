<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class for entity list
 */
class DataPersistence extends AbstractHelper
{
    /**
     * @var string|null
     */
    public $entityList;

    /**
     * @param Context $context
     * @param string $entityList
     */
    public function __construct(
        Context $context,
        $entityList = null
    ) {
        $this->entityList = $entityList;
        parent::__construct($context);
    }
}
