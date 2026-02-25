<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Block;

class ShortcodeMenu extends Topmenu
{
    /**
     * Get top menu html
     *
     * @return string
     */
    public function isStickyEnable()
    {
        $this->primaryMenuId = $this->getMenuid();
        $this->primaryMenu = $this->megamenuManagement->loadMenuById($this->primaryMenuId);
        return $this->primaryMenu->getIsSticky();
    }

    /**
     * Get html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $html = '';
        $this->primaryMenuId = $this->getMenuid();
        $this->primaryMenu = $this->megamenuManagement->loadMenuById($this->primaryMenuId);

        if ($this->helper->isEnabled() && $this->primaryMenu->getIsActive() == 1) {
            $menuItems = $this->megamenuManagement->loadMenuItems(0, 'ASC', $this->primaryMenuId);
            foreach ($menuItems as $item) {
                $childrenWrapClass = "level0 nav-1 first parent main-parent";
                $html .= $this->setMegamenu($item, $childrenWrapClass);
            }
        }

        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $this->_eventManager->dispatch(
            'shortcode_block_html_topmenu_gethtml_after',
            ['menu' => $this->primaryMenuId, 'transportObject' => $transportObject]
        );
        $html = $transportObject->getHtml();
        return $html;
    }

    /**
     * Get Cache Lifetime
     *
     * @return int|null
     */
    public function getCacheLifetime()
    {
        return null;
    }

    /**
     * Get Menu Design
     *
     * @return string
     */
    public function getMenuDesign()
    {
        return $this->primaryMenu->getMenuDesignType();
    }
}
