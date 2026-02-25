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
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Ui Component Address Column
 *
 * Class Address
 */
class Address extends Column
{
    /**
     * @var Escaper
     */
    protected Escaper $escaper;

    /**
     * @var OrderFactory
     */
    protected OrderFactory $_orderFactory;

    /**
     * Address constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param OrderFactory $orderFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        OrderFactory $orderFactory,
        array $components = [],
        array $data = []
    ) {
        /** @var  escaper */
        $this->escaper = $escaper;
        /** @var  _orderFactory */
        $this->_orderFactory = $orderFactory;

        parent::__construct($context, $uiComponentFactory, $components, $data);
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
                /** @var  $incrementId */
                $incrementId = $item['increment_id'];
                /** @var  $order */
                $order =  $this->_orderFactory->create()->loadByIncrementId($incrementId);

                // $item[$this->getData('name')] = nl2br($this->escaper->escapeHtml($item[$this->getData('name')]));
                if (isset($item[$this->getData('name')])) {
                    $columnName = nl2br($this->escaper->escapeHtml($item[$this->getData('name')]));
                    $item[$this->getData('name')] = $columnName ?: '';
                } else {
                    $item[$this->getData('name')] = $order->getEcDivisionId();
                }

            }
        }

        return $dataSource;
    }
}
