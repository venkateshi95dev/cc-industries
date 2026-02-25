<?php
/**
 * @namespace   Crimson
 * @module      Demographics
 * @author      Chad Carlson
 * @email       ccarlson@crimsonagility.com
 * @date        7/16/2020 12:27 PM
 * @brief
 */

namespace Crimson\Demographics\Observer;

use Crimson\Demographics\Model\Service\DemographicsMapper;
use Crimson\MachBase\Model\Api\AbstractApi;
use Crimson\MachBase\Model\Api\ApiContext;
use Crimson\MachBase\Model\MachConfig;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class MachAddOrderBeforeRequestDemographics
 * @package Crimson\Demographics\Observer
 */
class MachAddOrderBeforeRequestDemographics extends AbstractApi implements ObserverInterface
{

    public function __construct(
        ApiContext $apiContext,
        MachConfig $machConfig,
        protected DemographicsMapper $demographicsMapper,
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
                if ($orderSoapVar->enc_name === 'BillOpen1') {
                    unset($soapVar->enc_value[$orderKey]);
                    $soapVar->enc_value[] = $this->_soapVar($this->demographicsMapper->getDemographicsMappedByIds($order->getData('car_demos'), $order->getStoreId()), 'BillOpen1');
                }
            }

            break;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $argumentDataObject->setArguments($arguments);
    }
}
