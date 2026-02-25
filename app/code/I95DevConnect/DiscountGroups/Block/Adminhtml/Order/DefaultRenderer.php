<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Block\Adminhtml\Order;

use Magento\Sales\Model\Order\Item;

/**
 * Adminhtml sales order item renderer
 */
class DefaultRenderer extends \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer
{
    /**
     * @var string
     */
    protected $_template = 'I95DevConnect_DiscountGroups::order/view/default.phtml';// phpcs:ignore
}
