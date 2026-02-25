<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Block\Adminhtml\Sampleimport;

use Magento\Framework\View\Element\Template;

class Index extends Template
{
    /**
     * Generate form url
     *
     * @return  string
     */
    public function getFormAction()
    {
        return $this->getUrl('*/*/import');
    }
}
