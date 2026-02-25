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

use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Recurring Date Range Ui Column Component
 *
 * Class DateRange
 */
class DateRange extends Column
{
    /**
     * @var Escaper
     */
    protected Escaper $_escaper;

    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected PriceCurrencyInterface $_priceCurrency;

    /**
     * @var Data
     */
    protected Data $_priceHelper;

    /**
     * Amount constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param CustomerFactory $customerFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param Data $priceHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        CustomerFactory $customerFactory,
        PriceCurrencyInterface $priceCurrency,
        Data $priceHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var  escaper */
        $this->_escaper = $escaper;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _priceCurrency */
        $this->_priceCurrency = $priceCurrency;
        /** @var _priceHelper */
        $this->_priceHelper = $priceHelper;
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

                $dateRange = nl2br($this->_escaper->escapeHtml($item[$this->getData('name')]));
                $dateRange = $this->getDataRange($item);
                $item[$this->getData('name')] = $dateRange ? $dateRange : __('*');
            }
        }

        return $dataSource;
    }

    /**
     * Get Data Range
     *
     * @param mixed $item
     * @return string
     */
    public function getDataRange($item)
    {
        $startDate = date('Y-m-d', strtotime($item['eb_rec_start_date']));
        $endDate = date('Y-m-d', strtotime($item['eb_rec_end_date']));
        return $startDate.' To '.$endDate;
    }
}
