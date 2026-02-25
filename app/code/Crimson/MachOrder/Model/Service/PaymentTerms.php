<?php
/**
 * @namespace   Crimson
 * @module      MachOrder
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/6/2019 9:57 AM
 * @brief
 */

namespace Crimson\MachOrder\Model\Service;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class PaymentTerms
 * @package Crimson\MachOrder\Model\Service
 */
class PaymentTerms
{

    const PAYMENT_TERMS_CREDIT_CARD = 'CC';
    const PAYMENT_TERMS_PAYWARE = 'PW';
    const PAYMENT_TERMS_SKIPJACK = 'SJ';
    const PAYMENT_TERMS_PAYPAL = 'PP';
    const PAYMENT_TERMS_CHECK = 'CK';
    const PAYMENT_TERMS_PAYMENTTERMS = 'NET';
    const PAYMENT_TERMS_PURCHASEORDER = 'PO';
    const PAYMENT_TERMS_GIFTCARD = '14';
    const PAYMENT_TERMS_NMI = 'NMI';

    /**
     * Instance of serializer.
     *
     * @var Json
     */
    private $serializer;

    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @param OrderInterface $order
     * @return string|null
     */
    public function get(OrderInterface $order): ?string
    {
        /**
         * PaymentTerms
         * INPUT - LABEL
         * CC – Credit Card
         * PW – Payware
         * SJ – SkipJack
         * PP – PayPal
         * CK – Check
         * NET – Open on Account
         * 14 - if Gift Card covered the full Order
         */
        $method = $order->getPayment() ? $order->getPayment()->getMethod() : null;

        switch ($method) {

            case 'free':

                $result = $this->_isOrderFullCoveredByGiftCard($order);
                if($result){
                    return self::PAYMENT_TERMS_GIFTCARD;
                }

            case 'checkmo':
                return self::PAYMENT_TERMS_CHECK;
            case 'paypal':
            case 'paypal_standard':
            case 'paypal_express':
                return self::PAYMENT_TERMS_PAYPAL;
            case 'payware':
                return self::PAYMENT_TERMS_PAYWARE;
            case 'skipjack':
                return self::PAYMENT_TERMS_SKIPJACK;
            case 'cc':
            case 'authorizenet':
            case 'chaseorbitalgateway':
            case 'ccsave':
                return self::PAYMENT_TERMS_CREDIT_CARD;
            case 'payment_terms':
                return self::PAYMENT_TERMS_PAYMENTTERMS;
            case 'aw_nmi':
                return self::PAYMENT_TERMS_NMI;
        }

        return null;
    }

    /**
     * @param OrderInterface $order
     *
     * @return bool
     */
    protected function _isOrderFullCoveredByGiftCard(OrderInterface $order): bool
    {
        $result = false;
        if (!$order->getGiftCards()) {
            return false;
        }

        $cards = $this->serializer->unserialize($order->getGiftCards());
        if (is_array($cards)
            && count($cards) > 0
            && $order->getGiftCardsAmount() > 0
            && $order->getTotalDue() == 0
            && $order->getTotalPaid() == 0
            && $order->getGrandTotal() == 0
        ) {
            $result = true;
        }

        return $result;
    }
}
