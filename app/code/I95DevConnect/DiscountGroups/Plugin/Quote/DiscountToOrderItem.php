<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Plugin\Quote;

use Closure;
use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Store\Model\ScopeInterface;

class DiscountToOrderItem
{
    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var Data
     */
    public $dataHelper;

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;
    
    /**
     * DiscountToOrderItem constructor
     *
     * @param Data $dataHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     */
    public function __construct(
        Data $dataHelper,
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->dataHelper = $dataHelper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Aroung plugin for convert function
     *
     * @param ToOrderItem $subject
     * @param Closure $proceed
     * @param AbstractItem $item
     * @param array $additional
     * @return Item|mixed
     */
    public function aroundConvert(
        ToOrderItem $subject, //NOSONAR
        Closure $proceed,
        AbstractItem $item,
        $additional = []
    ) {
        $isEnabled = $this->scopeConfig->getValue(
            'i95devconnect_discountgroups/discountgroups_enabled_settings/enable_dg',
            ScopeInterface::SCOPE_STORE
        );
        if (
            $this->dataHelper->getGlobalValue('i95_observer_skip') ||
            $this->request->getParam('isI95DevRestReq') == 'true' || !$isEnabled
        ) {
            return $proceed($item, $additional);
        }

        /** @var $orderItem Item */
        $orderItem = $proceed($item, $additional);
        $orderItem->setDiscountGroupAmount($item->getDiscountGroupAmount());
        $orderItem->setBaseDiscountGroupAmount($item->getBaseDiscountGroupAmount());
        return $orderItem;
    }
}
