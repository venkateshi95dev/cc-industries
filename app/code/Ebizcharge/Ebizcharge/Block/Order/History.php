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

namespace Ebizcharge\Ebizcharge\Block\Order;

use Ebizcharge\Ebizcharge\Model\Order;
use Ebizcharge\Ebizcharge\Model\OrderFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Order History Block class
 *
 * Class History
 */
class History extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::order/history.phtml';

    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;

    /**
     * History constructor.
     *
     * @param Context $context
     * @param CollectionFactory $orderCollectionFactory
     * @param Session $customerSession
     * @param OrderFactory $orderFactory
     * @param Config $orderConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $orderCollectionFactory,
        Session $customerSession,
        OrderFactory $orderFactory,
        Config $orderConfig,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $orderCollectionFactory,
            $customerSession,
            $orderConfig,
            $data
        );

        /** @var  _orderFactory */
        $this->_orderFactory = $orderFactory;
    }

    /**
     * Is Subscribed
     *
     * @param null|Order $order
     * @return Phrase
     */
    public function isSubscribed($order = null)
    {
        $isSubscribed = __("No");
        /** @var  $orderId */
        $orderId = $order->getEntityId();
        $order = $this->_orderFactory->create()->load($orderId);

        if (count($order->getAllVisibleItems()) > 0) {
            foreach ($order->getAllVisibleItems() as $orderItem) {
                /** @var $orderItemOptions */
                $orderItemOptions = $orderItem->getProductOptions();

                if (isset($orderItemOptions["info_buyRequest"])) {
                    /** @var  $infoBuyRequest */
                    $infoBuyRequest = $orderItemOptions["info_buyRequest"];

                    if (isset($infoBuyRequest["recurring"]) && count($infoBuyRequest["recurring"]) > 0) {
                        $recurringItem = $infoBuyRequest["recurring"];
                        if (isset($recurringItem['rec_frequency']) && $recurringItem['rec_frequency'] !== '') {

                            $isSubscribed = __("Yes");
                        }
                    }
                }
            }
        }

        return $isSubscribed;
    }
}
