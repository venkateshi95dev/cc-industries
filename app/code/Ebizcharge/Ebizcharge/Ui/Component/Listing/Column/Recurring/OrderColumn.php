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

namespace Ebizcharge\Ebizcharge\Ui\Component\Listing\Column\Recurring;

use Ebizcharge\Ebizcharge\Model\OrderFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Recurring Order Column Ui Column Component
 *
 * Class OrderColumn
 */
class OrderColumn extends Column
{
    /**
     * @var Escaper
     */
    protected Escaper $_escaper;

    /**
     * @var UrlInterface
     */
    protected UrlInterface $_urlInterface;

    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;

    /**
     * OrderColumn constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param OrderFactory $orderFactory
     * @param UrlInterface $urlInterface
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        OrderFactory $orderFactory,
        UrlInterface $urlInterface,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var  escaper */
        $this->_escaper = $escaper;
        /** @var  _urlInterface */
        $this->_urlInterface = $urlInterface;
        /** @var  _orderFactory */
        $this->_orderFactory = $orderFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $columnName = nl2br($this->_escaper->escapeHtml($item[$this->getData('name')]));
                $orderId = $item['mage_order_id'];

                $item[$this->getData('name')] = $this->getLabelInfo($columnName, $orderId);
            }
        }

        return $dataSource;
    }

    /**
     * Get  html as per getLabelInfo
     *
     * @param mixed $gridLabel
     * @param null|mixed $incrementId
     * @return string
     */
    private function getLabelInfo($gridLabel, $incrementId = null): string
    {
        $gridLabel = $gridLabel !=="" ? $gridLabel : __('*');
        $cssClass = '';
        try {
            $orderFactory = $this->_orderFactory->create()->loadByIncrementId($incrementId);
            if ($orderFactory->getId()) {
                $orderIncrementId = $orderFactory->getIncrementId();
                $orderId = $orderFactory->getId();
                $gridLabel = $orderIncrementId;

                $url = $this->_urlInterface->getUrl('sales/order/view', ['order_id' => $orderId]);
                return '<a href="' . $url . '" target="_parent" title="' .
                    __("Please click to go to the Order.") . '" > <span class="' . $cssClass .
                    '"><span>' . $gridLabel . '</span></span></a>';
            }
            return '<span class="' . $cssClass . '"><span>' . $gridLabel . '</span></span>';
        } catch (LocalizedException $localizedException) {
            return '<span class="' . $cssClass . '"><span>' . $gridLabel . '</span></span>';
        }
    }
}
