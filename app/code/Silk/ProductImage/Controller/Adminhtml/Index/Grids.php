<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Silk\ProductImage\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Grids extends AbstractGrid implements HttpPostActionInterface
{
    /**
     * @var string
     */
    protected $blockClass = \Silk\ProductImage\Block\Adminhtml\Product\Merchandiser\Productgrid::class;

    /**
     * @var string
     */
    protected $blockName = 'grid';

}
