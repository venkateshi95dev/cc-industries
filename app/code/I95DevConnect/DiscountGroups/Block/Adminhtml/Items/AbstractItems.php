<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */
// @codingStandardsIgnoreFile

namespace I95DevConnect\DiscountGroups\Block\Adminhtml\Items;

class AbstractItems
{
    public function afterGetItemRenderer(\Magento\Sales\Block\Adminhtml\Items\AbstractItems $subject, $result) //NOSONAR
    {
        if (get_class($result) == 'Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer\Interceptor') {
            $result->setTemplate('I95DevConnect_DiscountGroups::order/invoice/view/items/renderer/default.phtml');
        } elseif (get_class($result) ==
            'Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer\Interceptor') {
            $result->setTemplate('I95DevConnect_DiscountGroups::order/view/default.phtml');
        }
        return $result;
    }
}
