<?php

/**
 * @author    i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package   I95DevConnect_CancelOrder
 */

namespace I95DevConnect\CancelOrder\Plugin\Block\Order\Widget\Button;

use I95DevConnect\CancelOrder\Helper\Data;
use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Sales\Block\Adminhtml\Order\View;

/**
 * Plugin file added to remove cancel button on sales order edit page
 */
class Toolbar
{
    /**
     * @var Data
     */
    public $data;

    /**
     * Toolbar constructor.
     *
     * @param Data $data
     */
    public function __construct(Data $data)
    {
        $this->data = $data;
    }

    /**
     * Removes cancel order button from sales order edit page
     *
     * @param  ToolbarContext $toolbar
     * @param  AbstractBlock  $context
     * @param  ButtonList     $buttonList
     * @return array
     */
    public function beforePushButtons(
        ToolbarContext $toolbar, //NOSONAR
        AbstractBlock $context,
        ButtonList $buttonList
    ) {
        if (!$context instanceof View) {
            return [$context, $buttonList];
        }
        $order = $context->getOrder();
        $getWebsiteId = $order->getStore()->getWebsiteId();
        $isEnabled = $this->data->isEnabled($getWebsiteId);
        $storeId = $order ? $order->getStoreId() : null;
        // Remove cancel button if storeId > 1
        if ($storeId > 1 && $isEnabled) {
            $buttonList->remove('order_cancel');
        }

        return [$context, $buttonList];
    }
}
