<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Observer\ReverseSyc;

use Exception;
use I95DevConnect\PriceLevel\Helper\Data;
use I95DevConnect\PriceLevel\Model\PriceLevelDataFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use I95DevConnect\PriceLevel\Helper\PriceLevel as PriceLevelHelper;

/**
 * Observer to assign Price Level to the Customer from ERP
 */
class CustomerPriceLevel implements ObserverInterface
{
    /**
     *
     * @var PriceLevelDataFactory
     */
    public $magentoPriceLevelFactory;

    /**
     *
     * @var Data
     */
    public $helper;

    /**
     *
     * @var PriceLevelHelper
     */
    public $priceLevelHelper;

    /**
     * @param PriceLevelDataFactory $magentoPriceLevelFactory
     * @param Data $helper
     * @param PriceLevelHelper $priceLevelHelper
     */
    public function __construct(
        PriceLevelDataFactory $magentoPriceLevelFactory,
        Data $helper,
        PriceLevelHelper $priceLevelHelper
    ) {
        $this->magentoPriceLevelFactory = $magentoPriceLevelFactory;
        $this->helper = $helper;
        $this->priceLevelHelper = $priceLevelHelper;
    }

    /**
     * Assign Price Level to the Customer
     *
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $currentObject = $observer->getEvent()->getData("currentObject");
        $erpPriceLevel = $currentObject->dataHelper
                ->getValueFromArray("priceLevel", $currentObject->stringData);
        if (isset($erpPriceLevel) && $erpPriceLevel != '' && $this->helper->isEnabled()) {
            $this->priceLevelHelper->validatePricelevel($erpPriceLevel);
            $currentObject->customerInterface->setCustomAttribute('pricelevel', $erpPriceLevel);
        } else {
            $currentObject->customerInterface->setCustomAttribute('pricelevel', null);
        }
    }
}
