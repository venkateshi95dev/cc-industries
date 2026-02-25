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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column;

use Ebizcharge\Ebizcharge\Model\OrderFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * This class creates link for order
 *
 * Class OrderLink
 */
class OrderLink extends Column
{
    /**

     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;

    /**
     * OrderLink constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param OrderFactory $orderFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        OrderFactory $orderFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var  urlBuilder */
        $this->urlBuilder = $urlBuilder;
        /** @var  _orderFactory */
        $this->_orderFactory = $orderFactory;
    }

    /**
     * Set order link html anchor tag
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!empty($item['rec_order_id']) && !empty($item['order_entity_id'])) {
                    /** @var  $incrementId */
                    $incrementId = $item['order_entity_id'];
                    /** @var  $order */
                    $order =  $this->_orderFactory->create()->loadByIncrementId($incrementId);

                    if ($order->getId() && (int)$item['status'] !== 1) {
                        $orderId = $order->getId();

                        $html = '<a href="' . $this->urlBuilder->getUrl(
                            'sales/order/view',
                            [
                                    'order_id' => $orderId
                                ]
                        ) . '" target="_blank">' . $item['rec_order_id'] . '</a>';
                        $item['rec_order_id'] = $html;
                    } else {
                        $item['rec_order_id'] = $item['rec_order_id'];
                    }
                }
            }
        }
        return $dataSource;
    }
}
