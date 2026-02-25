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

namespace Ebizcharge\Ebizcharge\Model\ResourceModel\Order\Grid;

use Ebizcharge\Ebizcharge\Api\Data\OrderInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\ResourceModel\Order;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;

/**
 * Order Grid Collection class
 *
 * Class Collection
 */
class Collection extends OrderGridCollection
{
    /**
     * Sales Order Grid Table Name
     *
     * @const SALES_ORDER_GRID_TABLE_NAME
     */
    public const SALES_ORDER_GRID_TABLE_NAME = 'sales_order_grid';

    /**
     * Sales Order Table Name
     *
     */
    public const SALES_ORDER_TABLE_NAME = 'sales_order';

    /**
     * @var EbizchargeLogger
     */
    protected EbizchargeLogger $_ebizchargeLogger;

    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param EbizchargeLogger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        EbizchargeLogger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = self::SALES_ORDER_GRID_TABLE_NAME,
        $resourceModel = Order::class
    ) {
        /** @var _ebizchargeLogger */
        $this->_ebizchargeLogger = $logger;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel
        );
    }

    /**
     * Render filter Before
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        /** @var $joinTable */
        $joinTable = $this->getTable(self::SALES_ORDER_TABLE_NAME);

        $this->getSelect()->joinLeft(
            [
                'sales_order_table' => $joinTable
            ],
            'main_table.entity_id = sales_order_table.entity_id',
            [
                OrderInterface::EC_ORDER_CREATED_IN,
                OrderInterface::EC_ORDER_DIVISION_ID,
                OrderInterface::EC_ORDER_CREATED_IN,
                OrderInterface::EBIZCHARGE_SOFTWARE_ID
            ] // Further  attributes
        );
        parent::_renderFiltersBefore();
    }

    /**
     * Init Select
     *
     * @return void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('created_at', 'main_table.created_at');
    }
}
