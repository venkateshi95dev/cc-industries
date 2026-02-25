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
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Recent Orders Block class
 *
 * Class Recent
 */
class Recent extends \Magento\Sales\Block\Order\Recent
{
    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;

    /**
     * Recent constructor.
     *
     * @param Context $context
     * @param CollectionFactoryInterface $orderCollectionFactory
     * @param Session $customerSession
     * @param Config $orderConfig
     * @param OrderFactory $orderFactory
     * @param array $data
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        Context $context,
        CollectionFactoryInterface $orderCollectionFactory,
        Session $customerSession,
        Config $orderConfig,
        OrderFactory $orderFactory,
        array $data = [],
        StoreManagerInterface $storeManager = null
    ) {
        parent::__construct(
            $context,
            $orderCollectionFactory,
            $customerSession,
            $orderConfig,
            $data,
            $storeManager
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
