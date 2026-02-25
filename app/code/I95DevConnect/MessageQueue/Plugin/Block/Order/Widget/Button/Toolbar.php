<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Plugin\Block\Order\Widget\Button;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Tool bar Edit
 */
class Toolbar
{
    /**
     * @var Data
     */
    public $data;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * Toolbar constructor.
     *
     * @param Data $data
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $data,
        StoreManagerInterface $storeManager
    ) {
        $this->data = $data;
        $this->storeManager = $storeManager;
    }

    /**
     * To remove ship and invoice buttons in tool bar
     *
     * @param ToolbarContext $toolbar
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
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
        $storeId = $order ? $order->getStoreId() : null;
        $website_id = $this->storeManager->getStore($storeId)->getWebsiteId();
        $component = $this->data->scopeConfig->getValue(
            'i95dev_messagequeue/I95DevConnect_settings/component',
            ScopeInterface::SCOPE_WEBSITE,
           $website_id
        );
        if ($storeId>1 && $this->data->isWebsiteEnabled($website_id) && ($component == "AX" || $component == "NAV"
            || $component == "GP" || $component == "BC" || $component == "Sage")
        ) {
            $buttonList->remove('order_ship');
            $buttonList->remove('order_invoice');
        }

        return [$context, $buttonList];
    }
}
