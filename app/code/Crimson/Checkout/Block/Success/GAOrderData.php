<?php

namespace Crimson\Checkout\Block\Success;

use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order;

class GAOrderData extends Template
{
    protected $_template = 'Crimson_Checkout::success/ga-order-data.phtml';

    /** @var \Magento\Checkout\Model\Session */
    private $checkoutSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->checkoutSession = $checkoutSession;
    }

    public function getOrder(): ?Order
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    public function _toHtml()
    {
        if (!$this->getOrder()) {
            return '';
        }


        return parent::_toHtml();
    }
}