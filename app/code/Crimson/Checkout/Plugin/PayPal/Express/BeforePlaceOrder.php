<?php

namespace Crimson\Checkout\Plugin\PayPal\Express;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Paypal\Controller\Express\AbstractExpress\PlaceOrder;
use Magento\Checkout\Model\Session;

class BeforePlaceOrder
{

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * BeforePlaceOrder constructor.
     * @param Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param PlaceOrder $subject
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeExecute(PlaceOrder $subject)
    {
        $hsc = $subject->getRequest()->getParam('mach_hsc',false);
        if ($hsc) {
            $quoteAddress = $this->_checkoutSession->getQuote()->getShippingAddress();
            $quoteAddress->getExtensionAttributes()->setMachHsc((int)$hsc);
        }
    }
}
