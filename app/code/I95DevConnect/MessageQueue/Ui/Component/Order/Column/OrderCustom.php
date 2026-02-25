<?php

/**
 * @author i95Dev <arushi bansal>
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Ui\Component\Order\Column;

use I95DevConnect\MessageQueue\Helper\Data;
use I95DevConnect\MessageQueue\Model\SalesOrderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class to OrderCustom
 */
class OrderCustom extends Column
{
    public const TARGET_ORDER_ID = 'target_order_id';
    public const ORIGIN = 'origin';

    /**
     * @var SalesOrderFactory
     */
    public $customSalesOrder;

    /**
     * @var Data
     */
    public $helperData;

    /**
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SalesOrderFactory $customSalesOrder
     * @param Data $helperData
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SalesOrderFactory $customSalesOrder,
        Data $helperData,
        array $components = [],
        array $data = []
    ) {

        $this->customSalesOrder = $customSalesOrder;
        $this->helperData = $helperData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare datasource
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $customOrderModel = $this->customSalesOrder->create();
                $customOrderData = $customOrderModel
                        ->getCollection()
                        ->addFieldToSelect([self::TARGET_ORDER_ID, self::ORIGIN])
                        ->addFieldToFilter('source_order_id', $item['increment_id']);
                if ($customOrderData->getSize() > 0) {
                    $this->prepareCustomOrderDataSource($customOrderData, $dataSource, $item);
                }
            }
        }
        return $dataSource;
    }

    /**
     * Prepare custom order data source
     *
     * @param array $customOrderData
     * @param object $dataSource
     * @param object $item
     * @return mixed
     * @noinspection PhpInconsistentReturnPointsInspection
     */
    public function prepareCustomOrderDataSource($customOrderData, $dataSource, &$item)
    {
        foreach ($customOrderData as $customOrderDataCollection) {
            switch ($this->getData('name')) {
                case self::TARGET_ORDER_ID:
                    $item[self::TARGET_ORDER_ID] = $customOrderDataCollection->getTargetOrderId();
                    break;
                case self::ORIGIN:
                    $item[self::ORIGIN] = $customOrderDataCollection->getOrigin();
                    break;
                default:
                    return $dataSource;
            }
        }
    }

    /**
     * Prepare
     *
     * @throws LocalizedException
     */
    public function prepare()
    {
        if (!$this->helperData->isEnabledInAnyWebsite()) {
            $this->setData(
                'config',
                array_replace_recursive(
                    ['componentDisabled' => true],
                    (array)$this->getData('config')
                )
            );
        }

        parent::prepare();
    }
}
