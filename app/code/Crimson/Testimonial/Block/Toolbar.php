<?php

namespace Crimson\Testimonial\Block;

use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{


    /**
     * Set collection to pager
     *
     * @param  \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int) $this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }

        return $this;
    }


    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChildBlock('Crimson_testimonial_toolbar_pager');

        if ($pagerBlock instanceof DataObject) {
            // @var $pagerBlock \Magento\Theme\Block\Html\Pager
            $pagerBlock->setAvailableLimit($this->getAvailableLimit());

            $pagerBlock->setUseContainer(
                false
            )->setShowPerPage(
                false
            )->setShowAmounts(
                false
            )->setFrameLength(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame',
                    ScopeInterface::SCOPE_STORE
                )
            )->setJump(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame_skip',
                    ScopeInterface::SCOPE_STORE
                )
            )->setLimit(
                $this->getLimit()
            )->setCollection(
                $this->getCollection()
            );
            return $pagerBlock->toHtml();
        }//end if

        return '';
    }
}
