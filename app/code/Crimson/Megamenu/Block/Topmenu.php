<?php

declare(strict_types=1);

namespace Crimson\Megamenu\Block;

use Magedelight\Megamenu\Block\Topmenu as BaseTopmenu;
use Magento\Framework\Exception\NoSuchEntityException;

class Topmenu extends BaseTopmenu
{
    /**
     * Override setChildCategoryColumn to add $style parameter
     */
    public function setChildCategoryColumn(
        $subcats,
        $item,
        $columnCount = 0,
        $childs = false,
        $level = 1,
        $style = null
    ) {
        if (!$childs) {
            $categoryArray = $this->prepareCategoryItems($subcats, $item);
        } else {
            $categoryArray = $subcats;
        }

        if (!$categoryArray && !$item->getProductDisplay()) {
            return '';
        }

        $html = '';
        $ulClass = '';

        if ($columnCount !== 0) {
            $ulClass .= 'column' . $columnCount . ' child-level-1';
        } else {
            $ulClass .= 'child-level-' . $level;
        }

        $openInNewTabText = '';
        if ($item->getOpenInNewTab()) {
            $openInNewTabText = 'target="_blank"';
        }

        if (!$categoryArray && $item->getProductDisplay()) {
            $html .= $this->getSingleCategoryProducts($html, $item, $ulClass, $openInNewTabText, $level);
            return $html;
        }

        $html .= '<ul style="' . ($style ?? '') . '" class="' . $ulClass . '">';

        foreach ($categoryArray as $cat) {
            $verticalclass = $cat['id'] == $this->getCurrentCat() ? 'active' : '';
            $uniqueClass = 'category-item nav-' . $item->getItemId() . '-' . $cat['id'];

            if ($item->getCategoryDisplay()) {
                $countChild = $this->getCategoryCount($cat['id']);
            }

            if ($item->getProductDisplay()) {
                $countChild = $this->getProductCount($cat['id']);
            }

            $liClass = $uniqueClass . ' ' . $verticalclass;
            $html .= '<li class="' . $liClass . '">';
            $html .= '<a href="' . $cat['url'] . '" ' . $openInNewTabText . '>' . __($cat['label']) . $countChild . $this->getCategoryMenuLabelHtml($cat['id']) . '</a>';

            if ($item->getProductDisplay()) {
                $html .= $this->getCategoryProducts($cat, $item, $level + 1);
            } else {
                if ((int) $item->getCategoryVerticalMenu() !== 1) {
                    $html .= $this->setChildCategoryColumn($cat['childrens'], $item, 0, true, $level + 1, $style);
                } else {
                    $html .= $this->setVerticalChildCategoryColumn($cat['childrens'], $level + 1);
                }
            }

            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }
}
