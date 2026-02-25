<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_PriceLevel
 */

namespace I95DevConnect\PriceLevel\Observer\ReverseSyc;

use Exception;
use I95DevConnect\MessageQueue\Model\CustomerGroupFactory;
use I95DevConnect\PriceLevel\Helper\Data;
use I95DevConnect\PriceLevel\Model\PriceLevelDataFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use I95DevConnect\PriceLevel\Helper\PriceLevel as PriceLevelHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Observer to assign Price Level to the Customer Group from ERP
 */
class CustomerGroupPriceLevel implements ObserverInterface
{
    /**
     * @var object
     */
    public $currentObject;

    /**
     *
     * @var PriceLevelDataFactory
     */
    public $magentoPriceLevelFactory;

    /**
     *
     * @var CustomerGroupFactory
     */
    public $modelCustomerGroup;

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
     * @param CustomerGroupFactory $modelCustomerGroup
     * @param Data $helper
     * @param PriceLevelHelper $priceLevelHelper
     */
    public function __construct(
        PriceLevelDataFactory $magentoPriceLevelFactory,
        CustomerGroupFactory $modelCustomerGroup,
        Data $helper,
        PriceLevelHelper $priceLevelHelper
    ) {
        $this->magentoPriceLevelFactory = $magentoPriceLevelFactory;
        $this->modelCustomerGroup = $modelCustomerGroup;
        $this->helper = $helper;
        $this->priceLevelHelper = $priceLevelHelper;
    }

    /**
     * Assign Price Level to the Customer Group
     *
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $this->currentObject = $observer->getEvent()->getData("currentObject");
        $component = $this->currentObject->dataHelper->getscopeConfig(
            'i95dev_messagequeue/I95DevConnect_settings/component',
            ScopeInterface::SCOPE_WEBSITE
        );
        if ($component != "NAV") {
            $erpPriceLevel = $this->currentObject->dataHelper
                    ->getValueFromArray("priceLevel", $this->currentObject->stringData);
            $targetCustomerGroupId = $this->currentObject->dataHelper
                    ->getValueFromArray("customerGroup", $this->currentObject->stringData);
            /** @updatedBy Debashis S. Gopal. Fetching $customerGroupId from object as it is changed
             * to object type in customer group.
             */
            $customerGroupId = $this->currentObject->resultData->getId();
            if (isset($erpPriceLevel) && $erpPriceLevel != '' && $this->helper->isEnabled()) {
                $pricelevelData = $this->priceLevelHelper->validatePricelevel($erpPriceLevel);
                $priceLevelId = $pricelevelData[0]['pricelevel_id'];
                if ($customerGroupId == "") {
                    $customGroup = $this->modelCustomerGroup->create();
                } else {
                    $modelCustomerGroupData = $this->modelCustomerGroup->create()
                            ->getCollection()
                            ->addFieldToFilter('customer_group_id', $customerGroupId)
                            ->getData();
                    $id = $modelCustomerGroupData[0]['id'];
                    $customGroup = $this->modelCustomerGroup->create()->load($id);
                }
                $customGroup->setcustomerGroupId($customerGroupId);
                $customGroup->settargetGroupId($targetCustomerGroupId);
                $customGroup->setpricelevelId($priceLevelId);
                $customGroup->setcreatedAt($this->currentObject->date->gmtDate());
                $customGroup->setupdatedAt($this->currentObject->date->gmtDate());
                $customGroup->setupdateBy('ERP');
                $customGroup->save();
            }
        }
    }
}
