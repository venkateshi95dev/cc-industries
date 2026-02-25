<?php

namespace Crimson\ZipCokerWvConsolidation\Preference\Silk\Coker\Model\ResourceModel\Order\Handler;

use Crimson\CorvetteCentral\Api\CorvetteCentralStoreInterface;
use Crimson\ZipCokerWvConsolidation\Model\Config;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Model\Order;
/**
 * Class State
 */
class State extends \Magento\Sales\Model\ResourceModel\Order\Handler\State
{
    const APPROVED ="approved";

    public function __construct(
        private readonly Http $request
    ) {}

    public function check(Order $order)
    {
        if (in_array($order->getStore()->getWebsite()->getCode(), [Config::ZIP_WEBSITE_CODE, CorvetteCentralStoreInterface::CORVETTE_CENTRAL_WEBSITE_CODE])) {
            return parent::check($order);
        }

        $route = $this->request->getRouteName()."/".$this->request->getControllerName()."/".$this->request->getActionName();
        if (!$order->isCanceled() && !$order->canUnhold() && !$order->canInvoice() && !$order->canShip()) {
            if (0 == $order->getBaseGrandTotal() || $order->canCreditmemo()) {
                if ($order->getState() !== Order::STATE_COMPLETE) {
                    $order->setState(Order::STATE_COMPLETE)
                        ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
                }
            } elseif (floatval($order->getTotalRefunded())
                || !$order->getTotalRefunded() && $order->hasForcedCanCreditmemo()
            ) {
                if ($order->getState() !== Order::STATE_CLOSED) {
                    $order->setState(Order::STATE_CLOSED)
                        ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
                }
            }
        }
        if ($order->getState() == Order::STATE_NEW && $order->getIsInProcess()) {
            if($route =="sales/order_invoice/save"){
                $order->setState(Order::STATE_PROCESSING)
                    ->setStatus(self::APPROVED);
            }else{
                $order->setState(Order::STATE_PROCESSING)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
            }

        }
        if ($order->getState() == Order::STATE_PROCESSING && $order->getIsInProcess()) {
            if($route =="sales/order_invoice/save"){
                $order->setState(Order::STATE_PROCESSING)
                    ->setStatus(self::APPROVED);
            }
        }
        if ($order->getState() == Order::STATE_COMPLETE && $order->getIsInProcess()) {
            if($route =="sales/order_invoice/save"){
                $order->setState(Order::STATE_PROCESSING)
                    ->setStatus(self::APPROVED);
            }
        }
        return $this;
    }
}
