<?php
 /**
 * @namespace   Crimson
 * @module      MachGiftCard
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        3/8/2019 12:27 PM
 * @brief
 */

namespace Crimson\MachGiftCard\Observer;

use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachBase\Model\MachConfig;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GiftCardAccount\Helper\Data;
use Magento\GiftCardAccount\Model\Giftcardaccount;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class MachAddOrderBeforeRequest
 * @package Crimson\MachGiftCard\Observer
 */
class MachAddOrderBeforeRequest extends AbstractApi
    implements ObserverInterface
{

    public function __construct(
        ApiContext $apiContext,
        MachConfig $machConfig,
        protected Data $giftcardHelper,
        Subscriber $subscriberResource,
        protected StoreManagerInterface $storeManager
    ) {
        parent::__construct($apiContext, $machConfig, $subscriberResource);
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): void
    {
        $zipWebsiteId = $this->storeManager->getWebsite(MachConfig::ZIP_WEBSITE_CODE)->getId();
        if (!$this->machConfig->isEnabled($zipWebsiteId)) {
            return;
        }

        /** @var DataObject $argumentDataObject */
        /** @noinspection PhpUndefinedMethodInspection */
        $argumentDataObject = $observer->getArguments();
        /** @noinspection PhpUndefinedMethodInspection */
        $arguments = $argumentDataObject->getArguments();

        /** @var Order $order */
        /** @noinspection PhpUndefinedMethodInspection */
        $order = $observer->getOrder();

        foreach ($arguments as $key => $soapVar) {
            /** @var \SoapVar $soapVar */
            if (!($soapVar instanceof \SoapVar)) {
                continue;
            }

            /** @noinspection PhpUndefinedFieldInspection */
            if ($soapVar->enc_name != 'ADD_ORDER_IN' || !is_array($soapVar->enc_value)) {
                continue;
            }

            foreach ($soapVar->enc_value as $orderKey => $orderSoapVar) {
                if (!($soapVar instanceof \SoapVar)) {
                    continue;
                }

                /** @var \SoapVar $orderSoapVar */
                /** @noinspection PhpUndefinedFieldInspection */
                if (strpos($orderSoapVar->enc_name, 'GCNum') === 0
                    || strpos($orderSoapVar->enc_name, 'GCAmt') === 0
                ) {
                    unset($soapVar->enc_value[$orderKey]);
                }
            }

            $_giftCards = $this->_getOrderGiftCards($order);
            foreach ($_giftCards as $giftcardKey => $value) {
                $soapVar->enc_value[] = $this->_soapVar($value, $giftcardKey);
            }

            break;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $argumentDataObject->setArguments($arguments);
    }

    /**
     * Returns gift card data associated with an order.
     *
     * As per MACH's documentation only two gift cards can be used. The
     * result is returned as an assoc with indices:
     *  'GCNum1'
     *  'GCAmt1'
     *  'GCNum2'
     *  'GCAmt2'
     *
     * @see     ZIP-533
     *
     * @param   OrderInterface|Order $order
     *
     * @return  array
     */
    protected function _getOrderGiftCards(OrderInterface $order): array
    {
        $_cards = $this->giftcardHelper->getCards($order);
        $_data = [
            'GCNum1' => null,
            'GCAmt1' => null,
            'GCNum2' => null,
            'GCAmt2' => null,
            'GCNum3' => null,
            'GCAmt3' => null,
            'GCNum4' => null,
            'GCAmt4' => null,
            'GCNum5' => null,
            'GCAmt5' => null,
        ];

        $_numCards = count($_cards);
        if (!is_array($_cards) || $_numCards == 0) {
            return $_data;
        }

        $i = 1;
        foreach ($_cards as $_card) {
            if (!array_key_exists(Giftcardaccount::CODE, $_card)
                || !array_key_exists(Giftcardaccount::AMOUNT, $_card)
            ) {
                continue;
            }

            $_data["GCNum{$i}"] = (string)$_card[Giftcardaccount::CODE];
            $_data["GCAmt{$i}"] = round($_card[Giftcardaccount::AMOUNT], 2);
            $i++;

            if ($i > 5) {
                break;
            }
        }

        return $_data;
    }
}
