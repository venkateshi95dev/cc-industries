<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Controller\Adminhtml\Position;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class Get
 *
 * @package Silk\ProductImage\Controller\Adminhtml\Position
 */
class Get extends \Silk\ProductImage\Controller\Adminhtml\Position implements HttpPostActionInterface
{
    /**
     * Get products positions from cache
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $cacheKey = $this->getRequest()->getParam(
            \Silk\ProductImage\Model\Position\Cache::POSITION_CACHE_KEY
        );

        $positions = \Zend_Json::encode($this->cache->getPositions($cacheKey));

        $resultJson->setData($positions);

        return $resultJson;
    }
}
