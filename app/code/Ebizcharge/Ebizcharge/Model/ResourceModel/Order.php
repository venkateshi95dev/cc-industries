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

namespace Ebizcharge\Ebizcharge\Model\ResourceModel;

use Ebizcharge\Ebizcharge\Api\Data\OrderInterface;
use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\OrderFactory;
use Ebizcharge\Ebizcharge\Model\ProductFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Sales\Model\ResourceModel\Attribute;
use Magento\Sales\Model\ResourceModel\Order as CoreOrderResource;
use Magento\Sales\Model\ResourceModel\Order\Handler\State as StateHandler;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Magento\SalesSequence\Model\Manager;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Ebiz Order Resource Model Class
 *
 * Class Order
 */
class Order extends CoreOrderResource
{
    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;

    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $sessionManagerInterface;

    /**
     * @var Config
     */
    protected Config $_ebizConfigModel;

    /**
     * @param Context $context
     * @param Snapshot $entitySnapshot
     * @param RelationComposite $entityRelationComposite
     * @param Attribute $attribute
     * @param Manager $sequenceManager
     * @param OrderFactory $orderFactory
     * @param CustomerFactory $customerFactory
     * @param ProductFactory $productFactory
     * @param Config $ebizConfigModel
     * @param SessionManagerInterface $sessionManagerInterface
     * @param StateHandler $stateHandler
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        Attribute $attribute,
        Manager $sequenceManager,
        OrderFactory $orderFactory,
        CustomerFactory $customerFactory,
        ProductFactory $productFactory,
        Config $ebizConfigModel,
        SessionManagerInterface $sessionManagerInterface,
        StateHandler $stateHandler,
        $connectionName = null
    ) {
        /** @var _orderFactory */
        $this->_orderFactory = $orderFactory;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _productFactory */
        $this->_productFactory = $productFactory;
        /** @var  _ebizConfigModel */
        $this->_ebizConfigModel = $ebizConfigModel;
        /** @var  sessionManagerInterface */
        $this->sessionManagerInterface = $sessionManagerInterface;

        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $attribute,
            $sequenceManager,
            $stateHandler,
            $connectionName
        );
    }

    /**
     * Get Order Id By Ebiz Internal Id
     *
     * @param string $ebizInternalId
     * @return string
     * @throws LocalizedException
     */
    public function getOrderIdByEbizInternalId($ebizInternalId = '')
    {
        /** @var $connection */
        $connection = $this->getConnection();
        /** @var $select */
        $select = $connection->select()
            ->from($this->getMainTable(), 'entity_id')
            ->where(OrderInterface::EC_ORDER_INTERNALID . ' = :' . OrderInterface::EC_ORDER_INTERNALID);
        $bind = [':' . OrderInterface::EC_ORDER_INTERNALID => (string)$ebizInternalId];
        /** @var $entityId */
        $entityId = $connection->fetchOne($select, $bind);

        return $entityId;
    }

    /**
     * Get Order Id by Ebiz Order Id
     *
     * @param string $ebizOrderId
     * @return string
     * @throws LocalizedException
     */
    public function getOrderIdByEbizOrderId($ebizOrderId = '')
    {
        /** @var $connection */
        $connection = $this->getConnection();
        /** @var $select */
        $select = $connection->select()
            ->from($this->getMainTable(), 'entity_id')
            ->where(OrderInterface::EC_ORDER_ID . ' = :' . OrderInterface::EC_ORDER_ID);
        $bind = [':' . OrderInterface::EC_ORDER_ID => $ebizOrderId];

        /** @var $entityId */
        $entityId = $connection->fetchOne($select, $bind);

        return $entityId;
    }

    /**
     * Function Save
     *
     * @param AbstractModel $object
     * @return $this|AbstractDb|CoreOrderResource|OrderResourceInterface
     * @throws AlreadyExistsException
     */
    public function save(AbstractModel $object)
    {
        /** Save Object */
        parent::save($object);
        /** after save sync order with Ebizcharge */
      //  $this->saveEbizchargeFields($object);
        return $this;
    }

    /**
     * Save Ebizcharge Extra Fields
     *
     * @param \Ebizcharge\Ebizcharge\Model\Order $order
     * @return $this
     * @throws LocalizedException
     */
    public function saveEbizchargeFields(\Ebizcharge\Ebizcharge\Model\Order $order)
    {
        $storeId = $this->_ebizConfigModel->getStoreId();
        $isEbizUploadEnabled = $this->_ebizConfigModel->isEconnectUploadEnabled($storeId);
        $isEbizOrderUploadEnabled = $this->_ebizConfigModel->isUplaodOrdersEnabled($storeId);

        /**
         * if enabled upload order or ebiz upload enabled
         */
        if (!$isEbizUploadEnabled || !$isEbizOrderUploadEnabled) {
            return $this;
        }
        /** @var $connection */
        $connection = $this->getConnection();
        $orderId = $order->getId();


        if($order->getEcOrderInternalId() !== "" || $order->getEcOrderId() !== "" || !$order->getEcOrderSyncStatus()) {

            /** @var updating Order resource getting Order from Ebizcharge */
            $ebizOrderParams = $this->_orderFactory->create()->syncOrderToEbizcharge($orderId);

            /** Ebiz Order Params */
            if (isset($ebizOrderParams['error']) && $ebizOrderParams['error'] === false) {

                /** @var  $ebizOrderInternalId */
                $ebizOrderInternalId = $ebizOrderParams[OrderInterface::EC_ORDER_INTERNALID] ?? '';
                $ebizOrderId = $ebizOrderParams[OrderInterface::EC_ORDER_ID] ?? '';
                $ebizSoftwareId = $ebizOrderParams[OrderInterface::EBIZCHARGE_SOFTWARE_ID] ?? '';
                $ebizDevisionId = $ebizOrderParams[OrderInterface::EBIZCHARGE_DIVISION_ID] ?? '';
                $ebizOrderPoNumber = $ebizOrderParams[OrderInterface::EC_ORDER_PO_NUMBER] ?? '';

                /** @var $where clause */
                $whereClause = $connection->quoteInto('entity_id' . " = ?", $orderId);

                /** @var $bind */
                $bind = [
                    OrderInterface::EC_ORDER_SYNC_STATUS => 1,
                    OrderInterface::EC_ORDER_INTERNALID => $ebizOrderInternalId,
                    OrderInterface::EC_ORDER_ID => $ebizOrderId,
                    OrderInterface::EBIZCHARGE_SOFTWARE_ID => $ebizSoftwareId,
                    OrderInterface::EBIZCHARGE_DIVISION_ID => $ebizDevisionId,
                    OrderInterface::EC_ORDER_CREATED_IN => $ebizSoftwareId,
                    OrderInterface::EC_ORDER_PO_NUMBER => $ebizOrderPoNumber,
                    OrderInterface::EC_ORDER_DATE_UPLOADED => date('Y-m-d H:i:s'),
                    OrderInterface::EC_ORDER_LASTSYNCDATE => date('Y-m-d H:i:s')
                ];

                /** updating the extra fields via Customer Resource Model */
                $this->getConnection()->update($this->getMainTable(), $bind, $whereClause);
            }
        }
        return $this;
    }

    /**
     * Process After Saves
     *
     * @param AbstractModel $object
     * @return $this|void
     */
    public function processAfterSaves(AbstractModel $object)
    {
        parent::processAfterSaves($object);

        return $this;
    }
}
