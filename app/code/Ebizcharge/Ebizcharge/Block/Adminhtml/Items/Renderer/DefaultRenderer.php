<?php
/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\Items\Renderer;

use Magento\Backend\Block\Template\Context;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer as CoreDefaultRenderer;

/**
 * Items Default Renderer block class
 *
 * Class DefaultRenderer
 */
class DefaultRenderer extends CoreDefaultRenderer
{
    /**
     * @var Item
     */
    protected Item $_orderItem;

    /**
     * DefaultRenderer constructor.
     *
     * @param Context $context
     * @param StockRegistryInterface $stockRegistry
     * @param StockConfigurationInterface $stockConfiguration
     * @param Registry $registry
     * @param Item $orderItem
     * @param array $data
     */
    public function __construct(
        Context $context,
        StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration,
        Registry $registry,
        Item $orderItem,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $stockRegistry,
            $stockConfiguration,
            $registry,
            $data
        );

        /** @var  _orderItem */
        $this->_orderItem = $orderItem;
    }

    /**
     * Get Recurred Ordered Item
     *
     * @param string $itemId
     * @return array
     */
    public function getRecurredOrderedItem(string $itemId = "")
    {
        $orderedItem = $this->_orderItem->load($itemId);
        $productOptions = [];
        if ($orderedItem) {
            $productOptions = $orderedItem->getProductOptions();
        }
        return $productOptions;
    }

    /**
     * Get order item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->_getData('item');//->getOrderItem();
    }
}
