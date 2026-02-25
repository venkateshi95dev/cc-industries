<?php
/**
 * @namespace   Crimson
 * @module      Sales
 * @author      Ian Coast
 * @email       icoast@crimsonagility.com
 * @date        2/28/2019 8:49 AM
 * @brief
 */

namespace Crimson\Sales\Model\ResourceModel\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class AbstractMachData
 * @package Crimson\Sales\Model\ResourceModel\Order
 */
abstract class AbstractMachData extends AbstractDb
{
    protected $preloadedData = [];

    protected function _getLinkedFieldId()
    {
        return 'order_id';
    }

    /**
     * @param $entityId
     *
     * @return array
     * @throws LocalizedException
     */
    public function getMachData($entityId): array
    {
        return $this->getOrderData($entityId);
    }

    /**
     * @param $orderId
     *
     * @return array
     * @throws LocalizedException
     */
    public function getOrderData($orderId): array
    {
        if (!$orderId) {
            return [];
        } elseif (isset($this->preloadedData[$orderId]) && is_array($this->preloadedData[$orderId])) {
            return $this->preloadedData[$orderId];
        }

        $select = $this->getConnection()->select()
            ->from($this->getMainTable())
            ->where($this->getConnection()->quoteInto($this->_getLinkedFieldId().' = ?', $orderId));

        return $this->getConnection()->fetchRow($select) ?: [];
    }

    /**
     * @param array $orderIds
     * @return $this
     * @throws LocalizedException
     */
    public function preloadOrderIds(array $orderIds): AbstractMachData
    {
        if (empty($orderIds)) {
            return $this;
        }

        $select = $this->getConnection()->select()
            ->from($this->getMainTable())
            ->where($this->getConnection()->quoteInto($this->_getLinkedFieldId().' IN (?)', $orderIds));

        $ordersData = $this->getConnection()->fetchAssoc($select);

        //only preload one set at a time.
        $this->clearPreloadedData();

        foreach ($ordersData as $orderData) {
            $this->preloadedData[$orderData[$this->_getLinkedFieldId()]] = $orderData;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clearPreloadedData(): AbstractMachData
    {
        $this->preloadedData = [];

        return $this;
    }

    /**
     * @return $this
     */
    public function saveOrderData(): AbstractMachData
    {
        return $this;
    }
}
