<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Observer;

use I95DevConnect\MessageQueue\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class ConvertToOrderObserver implements ObserverInterface
{
    /**
     * @var I95DevConnect\MessageQueue\Helper\Data
     */
    public $dataHelper;

    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $request;
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * ConvertToOrderObserver constructor.
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
     * Set discount group amount to quote shipping address
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $isEnabled = $this->scopeConfig->getValue(
            'i95devconnect_discountgroups/discountgroups_enabled_settings/enable_dg',
            ScopeInterface::SCOPE_STORE
        );
        if ($this->dataHelper->getGlobalValue('i95_observer_skip') || !$isEnabled) {
            return $this;
        }

        $observer->getEvent()->getOrder()
            ->setBaseDiscountGroupAmount(
                $observer->getEvent()->getQuote()->getShippingAddress()->getBaseDiscountGroupAmount()
            );
        $observer->getEvent()->getOrder()
            ->setDiscountGroupAmount(
                $observer->getEvent()->getQuote()->getShippingAddress()->getDiscountGroupAmount()
            );
        return $this;
    }
}
