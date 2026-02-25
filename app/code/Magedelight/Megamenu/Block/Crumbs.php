<?php
/**
 * @package Magedelight_Megamenu for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

declare(strict_types=1);

namespace Magedelight\Megamenu\Block;

use Magento\Catalog\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

class Crumbs extends Template
{
    /**
     * Catalog data obj
     * @var Data
     */
    private $catalogData = null;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * Crumbs constructor.
     *
     * @param Context $context
     * @param Data $catalogData
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $catalogData,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->catalogData = $catalogData;
        $this->registry = $registry;
    }

    /**
     * Get Crumbs
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCrumbs()
    {
        $evercrumbs = [];
        $evercrumbs[] = [
            'label' => __('Home'),
            'title' => __('Go to Home Page'),
            'link' => $this->_storeManager->getStore()->getBaseUrl()
        ];
        $product = $this->registry->registry('current_product');
        $path = $this->catalogData->getBreadcrumbPath();
        if ($path) {
            $path = $this->catalogData->getBreadcrumbPath();
            foreach ($path as $k => $p) {
                $evercrumbs[] = [
                    'label' => $p['label'],
                    'title' => $p['label'],
                    'link' => isset($p['link']) ? $p['link'] : ''
                ];
            }
        } else {
             $evercrumbs[] = [
                'label' => $product->getName(),
                'title' => $product->getName(),
                'link' => ''
             ];
        }
        return $evercrumbs;
    }
}
