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

namespace Ebizcharge\Ebizcharge\Model\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\State\AuthorizeCommand;
use Magento\Sales\Model\Order\StatusResolver;

/**
 * Process order state and status after authorize operation.
 */
class AuthorizedCommandOrderComments extends AuthorizeCommand
{
    /**
     * @var StatusResolver|mixed
     */
    protected $statusResolver;

    /**
     * @param StatusResolver|null $statusResolver
     */
    public function __construct(
        StatusResolver $statusResolver = null
    )
    {
        parent::__construct($statusResolver);
        $this->statusResolver = $statusResolver
            ?: ObjectManager::getInstance()->get(StatusResolver::class);
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param $amount
     * @param OrderInterface $order
     * @return Phrase
     */
    public function execute(OrderPaymentInterface $payment, $amount, OrderInterface $order): Phrase
    {
        $state = Order::STATE_PROCESSING;
        $status = null;
        $baseAuthorizedAmount = (float)$payment->getBaseAmountAuthorized();
        $grandTotal = (float)$order->getGrandTotal();

        if ($grandTotal !== $baseAuthorizedAmount) {
            $amount = $baseAuthorizedAmount;
        }
        return parent::execute($payment, $amount, $order);

    }

}
