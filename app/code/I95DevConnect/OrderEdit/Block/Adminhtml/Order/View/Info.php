<?php
/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_OrderEdit
 */
namespace I95DevConnect\OrderEdit\Block\Adminhtml\Order\View;

/**
 * Address edit link block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Info
{
    /**
     * Edit link after get address
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\View\Info $subject
     * @param string $result
     * @return string
     */
    public function afterGetAddressEditLink(\Magento\Sales\Block\Adminhtml\Order\View\Info $subject, $result) // NOSONAR
    {
        return '';
    }
}
