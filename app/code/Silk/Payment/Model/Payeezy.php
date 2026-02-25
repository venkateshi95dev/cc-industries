<?php
namespace Silk\Payment\Model;

use Magento\Framework\App\ObjectManager;
use Silk\Payment\Helper\Transaction as TransactionHelper;

class Payeezy extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'silk_payment';
    protected $_code = self::CODE;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_transactionHelper = null;

    /**
     * Authorize a payment.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /* @var $order Order */
        $order = $payment->getOrder();

        $this->getTransactionHelper()->doAuthorizationTransaction($order, $amount);

        return $this;
    }

    /**
     * Set the payment action to authorize_and_capture
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return self::ACTION_AUTHORIZE;
    }


    private function getTransactionHelper()
    {
        if ($this->_transactionHelper === null) {
            $this->_transactionHelper = ObjectManager::getInstance()->get(TransactionHelper::class);
        }
        return $this->_transactionHelper;
    }
}
