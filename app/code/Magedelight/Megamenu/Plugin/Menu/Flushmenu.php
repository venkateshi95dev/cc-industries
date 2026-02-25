<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Megamenu\Plugin\Menu;

use Magedelight\Megamenu\Controller\Adminhtml\Menu\Save;
use Magedelight\Megamenu\Helper\Cache;
use Magento\Framework\Controller\ResultInterface;

class Flushmenu
{
    /**
     * @var Cache
     */
    protected $helperData;

    /**
     * Flushmenu constructor.
     * @param Cache $helperData
     */
    public function __construct(
        Cache $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Update Variable By Code
     *
     * @param Save $subject
     * @param ResultInterface $result
     * @return ResultInterface
     * @throws \Exception
     */
    public function afterExecute(Save $subject, $result)
    {
        if ($this->helperData->enableCustomMenu()) {
            $this->helperData->updateVariableByCode($this->helperData->getStoreMenuKey());
        }
        return $result;
    }
}
