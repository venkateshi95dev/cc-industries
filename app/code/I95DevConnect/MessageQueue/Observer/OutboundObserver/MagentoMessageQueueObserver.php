<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Observer\OutboundObserver;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 * Observer class to set the records in magento message queue
 */
class MagentoMessageQueueObserver extends BaseObserver implements ObserverInterface
{
    public const XML_PATH_GENERIC_CONNECT_ERP_CRM = 'i95dev_messagequeue/I95DevConnect_settings/component';
    public const ORDER = 'order';

    /**
     * To save data in outbound messageQueue for any entity
     *
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/order_creation_venki.log');
$logger = new \Zend_Log();
$logger->addWriter($writer);
        try {
            if ($this->canEnterObserver()) {
                $logger->info('Test the log');
                $dataObject = $observer->getEvent()->getData("data_object");
                $savingSource = $this->dataHelper->coreRegistry->registry("savingSource");
               

                $observerConfig = $this->getValueFromArray($this->observerRouting, $observer->getEvent()->getName());

                if (is_array($observerConfig)) {
                    $methodName = $observerConfig['sourceKeyMethod'];
                    $entityCode = $observerConfig['entityCode'];

                    /* @author Arushi Bansal - prevent new entry to magento outbound messagequeue,
                     * if that entity is disabled
                     */
                    $storeScope = ScopeInterface::SCOPE_WEBSITE;
                    $component = $this->scopeConfig->getValue(
                        self::XML_PATH_GENERIC_CONNECT_ERP_CRM,
                        $storeScope,
                        $this->storeManager->getDefaultStoreView()->getWebsiteId()
                    );
$logger->info('before skipOutboundObserver');
                    if ($this->skipOutboundObserver($entityCode, $component, $dataObject)) {
                        return;
                    }
$logger->info('skipOutboundObserver');

                    $this->setDataObject($dataObject);
                    $magentoId = $dataObject->$methodName();

                    if ($this->skipProduct($observer, $component, $magentoId, $entityCode)) {
                        return;
                    }

                    if ($entityCode == 'address') {
                        $entityCode = 'Customer';
                        $magentoId = $dataObject->getData('customer_id');
                    }
                    if($entityCode == 'Customer' && $magentoId>0){
 
                        $customer = $this->generic->getCustomerById($magentoId);
 
                        $store_id = $customer["store_id"];
 
                    } elseif ($entityCode == "accountreceivable" ||
                        $entityCode == "cashreceipt" ) {
 
                        $customer_id = ($dataObject->getData('customer_id')) ?? 0;
 
                        if($customer_id){
 
                            $customer = $this->generic->getCustomerById($customer_id);
 
                            $store_id = $customer["store_id"];
					    }
					}elseif($entityCode == "returns"){
                        $store_id = $dataObject->getData('store_id');
                    } else {
					    $store_id = $dataObject->getData('store_id');
					}
$logger->info('store_id - '.$store_id);
                $isWebsiteEnabled = $this->dataHelper->isWebsiteEnabled($this->storeManager->getStore($store_id)->getWebsite()->getWebsiteId());
                    if(!$isWebsiteEnabled){
                        return;
                    }

                    $this->setSourceData($savingSource);
                    $this->setEntitycode($entityCode);
                    $this->setMagentoId($magentoId);
                    $eventName = 'erp_connect_magento_message_queue';
                    $this->eventManager->dispatch($eventName, ['currentObject' => $this]);

                    $this->saveRecord();
                }
            }
        } catch (LocalizedException $ex) {
            $message = $ex->getMessage();
            throw new LocalizedException(__($message));
        }
    }

    /**
     * Skip Outbound Observer
     *
     * @param string $entitiyCode
     * @param string $component
     * @param object $dataObject
     * @return bool
     */
    protected function skipOutboundObserver($entitiyCode, $component, $dataObject)
    {
        $entityStatus = $this->entityTypeModel->create()->load($entitiyCode);
        $skip = false;
        if ($entityStatus->getSupportForOutbound() == 0) {
            $skip = true;
        }

        /** @updatedBy Debashis. Stopping Customer group sync Magento to ERP, if ERP is  BC or NAV **/
        if (($component == 'NAV' || $component == 'BC') && $entitiyCode == 'CustomerGroup') {
            $skip = true;
        }

        /** @updatedBy Ranjith. Stopping Salesperson sync from Magento to ERP, if ERP is  BC or D365FO **/
        if (($component == 'D365FO' || $component == 'BC') && $entitiyCode == 'salesperson') {
            $skip = true;
        }
        $customerSync = $this->dataHelper->scopeConfig->getValue(
            'i95dev_messagequeue/I95DevConnect_settings/customersync',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
        if ($entitiyCode == 'Customer' && $customerSync) {
            $customerId = $dataObject->getId();
            $orderData = $this->getSalesOrderByCustomerId($customerId);
            if ($orderData->getSize() == 0) {
                $skip = true;
            }
        }
        if ($entitiyCode == self::ORDER) {
            $orderSyncDate = $this->dataHelper->scopeConfig->getValue(
                'i95dev_messagequeue/I95DevConnect_settings/ordersync_fromdate',
                ScopeInterface::SCOPE_WEBSITE,
                $this->storeManager->getDefaultStoreView()->getWebsiteId()
            );
            $orderCreatedDate = date('Y-m-d', strtotime(str_replace('.', '/', $dataObject->getData('created_at'))));
            $orderSyncDate = (!empty($orderSyncDate)) ? strtotime($orderSyncDate) : 0;
        }

        if ($entitiyCode == self::ORDER &&
            (
                $dataObject->getData('status') == "closed" ||
                $dataObject->getData('status') == "canceled" ||
                strtotime($orderCreatedDate) < $orderSyncDate)
        ) {
            $skip = true;
        }

        return $skip;
    }

    /**
     * Can enter observer
     *
     * @return bool
     */
    protected function canEnterObserver()
    {
        $is_enabled = $this->dataHelper->isEnabled();
        return $is_enabled && (!($this->dataHelper->getGlobalValue('i95_observer_skip') ||
            !empty($this->request->getParam('isI95DevRestReq'))));
    }

    /**
     * Skip product
     *
     * @param object $observer
     * @param string $component
     * @param int $magentoId
     * @param string $entityCode
     * @return bool
     */
    protected function skipProduct($observer, $component, $magentoId, $entityCode)
    {
        if ($entityCode == 'product' && ($component != 'GP' && $component != 'Sage')) {
            $product = $observer->getEvent()->getProduct();
            $supportedArray = $this->generic->getSupportedTypesForProduct();
            $productType = $product->getTypeId();
            if ($product->getTargetproductstatus() == "synced" || !in_array($productType, $supportedArray)) {
                return true;
            }
        } elseif ($entityCode == self::ORDER) {
            //fix added for multiple entry issue of order
            $mqOrder = $this->I95DevMagMQRepo->create()->getCollection();
            $mqOrder->addFieldToFilter("entity_code", self::ORDER)
                ->addFieldToFilter("magento_id", $magentoId);
            $mqOrder->getSelect()->limit(1);

            if (!empty($mqOrder->getData())) {
                return true;
            }

            //fix to stop the order inserting into OBMQ if it has target order id
            $targetOrderId = $this->generic->getTargetOrderId($magentoId);
            if ($targetOrderId != '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Sales order by customer id
     *
     * @param int $customerId
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getSalesOrderByCustomerId($customerId)
    {
        $salesOrder = $this->salesOrderFactory->create();
        $salesOrder->addFieldToFilter('customer_id', $customerId);
        return $salesOrder;
    }
}
