<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Helper;

use DateTime;
use Exception;
use I95DevConnect\MessageQueue\Api\LoggerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper Class for Message Queue module
 */
class Data extends AbstractData
{
    /**
     * To delete Inbound MQ data
     *
     * @param string $mqEntity
     *
     * @return boolean
     * @throws Exception
     */
    public function deleteMQData($mqEntity)
    {
        try {
            $toDeleteDate = $this->getMQCleanDate();
            $successRecord = $this->erpMessageQueue->create()->getCollection()
                ->addFieldToSelect([self::MSG_ID, self::ERROR_ID])
                ->addFieldtoFilter(self::ENTITY_CODE, $mqEntity)
                ->addFieldtoFilter('updated_dt', ['to' => $toDeleteDate])
                ->addFieldtoFilter('status', ['IN' => 2, 3, 4, 5]);
            $successRecord = $successRecord->getData();
            if ($successRecord) {
                foreach ($successRecord as $record) {
                    $dataRec = $this->erpMessageQueue->create()->get($record[self::MSG_ID]);
                    if ($dataRec['data_id'] > 0) {
                        $this->modelEntityUpdateDataFactory->create()->deleteMQData($dataRec['data_id']);
                    }
                    if ($record[self::ERROR_ID] > 0) {
                        $this->errorModel->create()->load($record[self::ERROR_ID])->delete();
                        $dataRec->setErrorId(null);
                    }
                    $dataRec->delete();
                }
            }
        } catch (LocalizedException $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), self::CLEAN, LoggerInterface::CRITICAL);
        }
        return true;
    }

    /**
     * To delete outbound MQ data
     *
     * @param string $mqEntity
     * @return boolean
     * @throws Exception
     */
    public function deleteMMQData($mqEntity)
    {
        try {
            $toDeleteDate = $this->getMQCleanDate();
            $successRecord = $this->magentoMessageQueue->create()->getCollection()
                ->addFieldtoFilter(self::ENTITY_CODE, $mqEntity)
                ->addFieldtoFilter('updated_dt', ['to' => $toDeleteDate])
                ->addFieldtoFilter('status', ['IN' => 2, 3, 4, 5, 6]);
            $successRecord = $successRecord->getData();
            if ($successRecord) {
                foreach ($successRecord as $record) {
                    $dataRec = $this->magentoMessageQueue->create()->get($record[self::MSG_ID]);
                    if ($record[self::ERROR_ID] > 0) {
                        $this->errorModel->create()->load($record[self::ERROR_ID])->delete();
                        $dataRec->setErrorId(null);
                    }
                    $dataRec->delete();
                }
            }
        } catch (LocalizedException $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), self::CLEAN, LoggerInterface::CRITICAL);
        }
        return true;
    }

    /**
     * To get MQ clean date
     *
     * @return string
     * @throws LocalizedException
     * @throws Exception
     * @author Ranjith R
     */
    public function getMQCleanDate()
    {
        try {
            $todayDate = $this->date->gmtDate();
            $mqDays = $this->scopeConfig->getValue(
                'i95dev_messagequeue/I95DevConnect_mqsettings/mqdata_clean_days',
                ScopeInterface::SCOPE_WEBSITE,
                $this->storeManager->getDefaultStoreView()->getWebsiteId()
            );

            if (!trim($mqDays)) {
                $mqDays = self::MQDATA_CLEAN_DAYS;
            }

            $dateObj = new DateTime($todayDate);
            $dateObj->modify('-' . $mqDays . ' day');
            return $dateObj->format('Y-m-d H:i:s');
        } catch (LocalizedException $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), self::CLEAN, LoggerInterface::CRITICAL);
            throw new LocalizedException(
                $ex->getMessage()
            );
        }
    }

    /**
     * Get value from array
     *
     * @param string $key
     * @param array $array
     * @param string $defaultValue
     * @param boolean $issetflag
     * @return string
     */
    public function getValueFromArray($key, $array, $defaultValue = null, $issetflag = true)
    {
        $value = "";
        if (isset($array[$key])) {
            $value = $array[$key];
        } else {
            if ($issetflag) {
                $value = $defaultValue;
            }
        }
        return $value;
    }

    /**
     * Get region details
     *
     * @param string $regionCode
     * @param string $countryCode
     * @return array
     */
    public function getRegionDetails($regionCode, $countryCode)
    {
        $regionDetails = $this->regionModel->create()->loadByCode($regionCode, $countryCode)->getData();
        return is_array($regionDetails) ? $regionDetails : [$regionDetails];
    }

    /**
     * Prepare entity data
     *
     * @param string $fieldmap
     * @param string $data
     * @return array
     */
    public function prepareInfoArray($fieldmap, $data)
    {
        $entityData = [];
        if (!empty($fieldmap)) {
            foreach ($fieldmap as $key => $value) {
                if (isset($data[$value])) {
                    $entityData[$key] = $data[$value];
                } else {
                    $entityData[$key] = null;
                }
            }
        }

        return $entityData;
    }

    /**
     * Set Custom Attributes for customer
     *
     * @param obj $customer
     */
    public function customCustomerAttributes($customer)
    {
        try {
            $targetId = $customer->getTargetCustomerId();
            $customer->setData('update_by', 'Magento')->getResource()->saveAttribute($customer, 'update_by');
            if ($customer->getOrigin() == "" || $customer->getOrigin() == "website") {
                $customer->setData(self::ORIGIN, 'website')->getResource()->saveAttribute($customer, self::ORIGIN);
            } else {
                // @updatedBy Arushi Bansal
                $component = $this->getComponent();
                $customer->setData(self::ORIGIN, $component)->getResource()->saveAttribute($customer, self::ORIGIN);
            }
            $customer->setData(self::TARGET_CUSTOMER_ID, $targetId)->getResource()
                ->saveAttribute($customer, self::TARGET_CUSTOMER_ID);
        } catch (LocalizedException $ex) {
            $this->logger->createLog(__METHOD__, $ex->getMessage(), self::I95EXC, LoggerInterface::CRITICAL);
        }
    }

    /**
     * Get region id
     *
     * @updatedBy Debashis S. Gopal. Added create() method in regionModel as it is a factory object.
     * @param string $regionCode
     * @param string $countryCode
     * @return string
     */
    public function getRegionId($regionCode, $countryCode)
    {
        $regionId = $this->regionModel->create()->loadByCode($regionCode, $countryCode)->getId();
        if ($regionId == "") {
            //@author kavya.koona Removed create() method to get the previous region collection
            $collection = $this->regionModel->getCollection()
                ->addFieldToFilter("country_id", $countryCode);
            $collection->getSelect()->limit(1);
            $itemsCount = count($collection->getItems());
            if ($itemsCount > 0) {
                $regionId = false;
            }
        }
        return $regionId;
    }

    /**
     * Get packet size
     *
     * @return int
     */
    public function getPacketSize()
    {
        if ($this->getscopeConfig(
            'i95dev_messagequeue/i95dev_extns/packet_size',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        )
        ) {
            $packetSize = $this->getscopeConfig(
                'i95dev_messagequeue/i95dev_extns/packet_size',
                ScopeInterface::SCOPE_WEBSITE,
                $this->storeManager->getDefaultStoreView()->getWebsiteId()
            );
            $packetSize = (int) $packetSize;
        } else {
            $packetSize = 1;
        }

        return $packetSize;
    }

    /**
     * Check if email notification is enabled
     *
     * @param string $entity
     * @return boolean
     * @createdBy Arushi Bansal
     */
    public function isEmailNotifyEnable($entity)
    {
        $isEnabled = $this->getscopeConfig(
            'i95dev_messagequeue/I95DevConnect_notifications/email_notifications',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
        if (!empty($isEnabled)) {
            $enabled = preg_split('/,/', $isEnabled);
            if (in_array($entity, $enabled)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Component name
     *
     * @return string
     * @createdBy Arushi Bansal
     */
    public function getComponent()
    {
        return $this->getscopeConfig(
            'i95dev_messagequeue/I95DevConnect_settings/component',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
    }

    /**
     * Get true if we can capture invoice
     *
     * @return bool
     * @createdBy Arushi Bansal
     */
    public function isCaptureInvoiceEnabled()
    {
        return $this->getscopeConfig(
            'i95dev_messagequeue/I95DevConnect_settings/capture_invoice',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
    }

    /**
     * Get manage stock configuration
     *
     * @return bool
     * @createdBy Arushi Bansal
     */
    public function getManageStock()
    {
        return $this->getscopeConfig(
            'cataloginventory/item_options/manage_stock',
            ScopeInterface::SCOPE_WEBSITE,
            $this->storeManager->getDefaultStoreView()->getWebsiteId()
        );
    }

    /**
     * Get customer attribute
     *
     * @param int $current_customer_id
     * @return string
     */
    public function getCustomAttribute($current_customer_id)
    {
        $customerCollection = $this->customerFactory->create()
            ->load($current_customer_id)->getData();

        return isset($customerCollection[self::TARGET_CUSTOMER_ID]) ?
            $customerCollection[self::TARGET_CUSTOMER_ID] : "Customer Sync In Process";
    }

    /**
     * Load page
     *
     * @param string $title
     * @param object $block
     * @param object $menu
     * @param array $breadcrumb
     * @return mixed
     */
    public function loadPage($title, $block, $menu, $breadcrumb)
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu($menu);
        $resultPage->getConfig()->getTitle()->prepend(__($title));
        $resultPage->addBreadcrumb(__($breadcrumb['label']), __($breadcrumb['title']));
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock($block)
        );
        return $resultPage;
    }

    /**
     * Return to index
     *
     * @return mixed
     */
    public function returnToIndex()
    {
        // @codingStandardsIgnoreStart
        $url = $this->_redirect->getRefererUrl();
        // @codingStandardsIgnoreEnd
        $login_url = $this->urlInterface
            ->getUrl('admin/index', ['referer' => base64_encode($url)]);

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($login_url);
        return $resultRedirect;
    }

    /**
     * Check if $erpCustomerGroupId is already exists in magento default groups.
     *
     * @createdBy Debashis S. Gopal
     * @param string $erpCustomerGroupId
     * @return int
     * @throws LocalizedException
     */
    public function checkInDefaultCustomerGroups($erpCustomerGroupId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('customer_group_code', $erpCustomerGroupId, 'eq')
            ->create();
        $searchResults = $this->groupRepository->getList($searchCriteria);
        $groupInfo = $searchResults->getItems();
        if (!empty($groupInfo)) {
            if (isset($groupInfo[0])) {
                return $groupInfo[0];
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Get items data
     *
     * @param int $itemId
     * @param array $entityData
     * @param array $entityItemsData
     * @return mixed
     * @noinspection DuplicatedCode
     */
    public function getItemsData($itemId, $entityData, $entityItemsData)
    {
        if (isset($entityData['qty'])) {
            if (isset($entityItemsData[$itemId]['qty']) && $entityItemsData[$itemId]['qty'] > 0) {
                $entityItemsData[$itemId]['qty'] += $entityData['qty'];
            } else {
                $entityItemsData[$itemId]['qty'] = $entityData['qty'];
            }
        } else {
            $entityItemsData[$itemId]['qty'] = 0;
        }

        return $entityItemsData;
    }

    /**
     * Get Parent items
     *
     * @param array $items
     * @return array[]
     */
    public function getParentItems($items)
    {
        $itemsIds = [];
        $parentItemIds = [];
        $productMapArray = [];
        $bundleChilds = [];
        foreach ($items as $itemObject) {
            if ($itemObject->getParentItemId() != null &&
                $itemObject->getParentItem()->getProductType() == 'bundle'
            ) {
                $options = $itemObject->getParentItem()->getProductOptions();
                $shipmentType = $options['shipment_type'] ?? 0;
                if ($shipmentType == \Magento\Catalog\Model\Product\Type\AbstractType::SHIPMENT_SEPARATELY) {
                    $bundleChilds[$itemObject->getItemId()] =
                    [
                        'qtyOrdered' => $itemObject->getQtyOrdered(),
                        'parentItemId' => $itemObject->getParentItemId(),
                        'parentQtyOrdered' => $itemObject->getParentItem()->getQtyOrdered()
                    ];
                } else {
                    $this->logger->createLog(
                        __METHOD__,
                        'Skip bundle child item ' . $itemObject->getId(),
                        LoggerInterface::INFO,
                        'info'
                    );
                }
            } else {
                $itemsIds[$itemObject->getProductId()] = $itemObject->getItemId();
                /** @updatedBy Debashis S. Gopal. Validating shipment item sku irrespective of their case **/
                $productMapArray[strtolower($itemObject->getSku())] = $itemObject->getProductId();
                $parentItem = $itemObject->getParentItem();
                if (isset($parentItem)) {
                    $parentItemIds[$itemObject->getItemId()] = $itemObject->getParentItemId();
                }
            }
        }

        return ['itemsIds' => $itemsIds, 'parentItemIds' => $parentItemIds,
                'productMapArray' => $productMapArray, 'bundleChilds' => $bundleChilds];
    }

    /**
     * Get Store Ids
     *
     * @param int $websiteId
     * @return []
     */
    public function getStoreId($websiteId)
    {
        try {
            return $this->storeWebsiteRelation->getStoreByWebsiteId($websiteId);
        } catch (LocalizedException $ex) {
            $this->logger->createLog(
                __METHOD__,
                $ex->getMessage(),
                LoggerInterface::I95EXC,
                self::CRITICAL
            );
        }

        return [];
    }
    
    /**
     * Check whether the I95DevConnect_MessageQueue is enabled or not
     *
     * @return boolean
     */
    public function getCategoryParentId($website_id=7)
    {
        return $this->scopeConfig->getValue(
            'i95dev_messagequeue/I95DevConnect_settings/category_parent_id',
            ScopeInterface::SCOPE_WEBSITE,
            $website_id
        );
    }
}
