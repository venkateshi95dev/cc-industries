<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_OrderEdit
 */
namespace I95DevConnect\OrderEdit\Plugin\Block\Order\Widget\Button;

use I95DevConnect\OrderEdit\Helper\Data;
use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;

/**
 * Class for remove order edit button from sales order edit tool bar
 */
class Toolbar
{
    /**
     * @var Data
     */
    public $data;

    /**
     *
     * @param \I95DevConnect\OrderEdit\Helper\Data $data
     */
    public function __construct(\I95DevConnect\OrderEdit\Helper\Data $data)
    {
        $this->data=$data;
    }

    /**
     * Remove order edit button from sales order tool bar
     *
     * @param ToolbarContext $toolbar
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @return array
     */
    public function beforePushButtons(
        ToolbarContext $toolbar, // NOSONAR
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        if (!$context instanceof \Magento\Sales\Block\Adminhtml\Order\View) {
            return [$context, $buttonList];
        }

        if ($this->data->isEnabled()) {
            $buttonList->remove('order_edit');
        }
        return [$context, $buttonList];
    }
}
