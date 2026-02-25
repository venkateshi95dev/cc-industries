<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Silk\ProductImage\Observer;

use Magento\Catalog\Model\Category;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductSaveMerchandiserData implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Silk\ProductImage\Model\Position\Cache
     */
    protected $_cache;

    /**
     * @param \Silk\ProductImage\Model\Position\Cache $cache
     */
    public function __construct(
        \Silk\ProductImage\Model\Position\Cache $cache
    ) {
        $this->_cache = $cache;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Category $document */
        $document = $observer->getEvent()->getDocument();
        // Assign cached positions
        $cacheKey = $observer->getEvent()->getRequest()->getPostValue(
            \Silk\ProductImage\Model\Position\Cache::POSITION_CACHE_KEY
        );
        $positions = $this->_cache->getPositions($cacheKey);
        if (is_array($positions)) {
            $document->setPostedProducts(
                $positions
            );
        }
        try {
            $document->save();
        } catch (Exception $e) {

        }
    }
}
