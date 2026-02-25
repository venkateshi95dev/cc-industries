<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Tile extends AbstractGrid implements HttpPostActionInterface
{
    /**
     * @var string
     */
    protected $blockClass = \Silk\ProductImage\Block\Adminhtml\Product\Merchandiser\Tile::class;

    /**
     * @var string
     */
    protected $blockName = 'tile';

}
