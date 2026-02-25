<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;

class AbstractGrid extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::categories';
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;
    /**
     * @var string
     */
    protected $blockClass;

    /**
     * @var string
     */
    protected $blockName;
    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get product reviews grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        $this->storeManager->setCurrentStore($this->storeManager->getStore($storeId)->getCode());

        if (!$this->blockClass || !$this->blockName) {
            throw new NotFoundException(__('Page not found.'));
        }
        $block = $this->layoutFactory->create()->createBlock(
            $this->blockClass,
            $this->blockName
        );
        $block->setPositionCacheKey(
            $this->getRequest()->getParam(\Silk\ProductImage\Model\Position\Cache::POSITION_CACHE_KEY, false)
        );
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $block->toHtml()
        );
    }
}
