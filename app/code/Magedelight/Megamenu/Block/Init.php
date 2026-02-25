<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Block;

use Magento\Backend\Block\AbstractBlock;
use Magento\Backend\Block\Context;
use Magento\Framework\View\Page\Config;

class Init extends AbstractBlock
{
    /**
     * @var Config
     */
    protected $pageConfig;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $pageConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $pageConfig,
        array $data = []
    ) {
        $this->pageConfig = $pageConfig;
        parent::__construct($context, $data);
    }

    /**
     * Add Page Asset
     *
     * @return void
     */
    protected function _construct()
    {
        $page = $this->pageConfig;
        $page->addPageAsset('Magedelight_Megamenu::css/font-awesome/css/font-awesome.min.css');
        $page->addPageAsset('Magedelight_Megamenu::js/megamenu/megamenu.js');
        $page->addPageAsset('Magedelight_Megamenu::js/megamenu/burgermenu.js');
    }
}
