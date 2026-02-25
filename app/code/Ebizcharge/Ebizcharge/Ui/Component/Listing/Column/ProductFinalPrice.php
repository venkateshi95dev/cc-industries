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

use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\RecurringFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Pricing\Helper\Data as DataHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * This class is used to add different classes to status as per status value
 *
 * Class StatusRenderer
 */
class ProductFinalPrice extends Column
{
    /**
     * @var DataHelper
     */
    protected DataHelper $_dataHelper;
    /**
     * @var Data
     */
    protected Data $_priceHelper;
    /**
     * @var ConfigFactory
     */
    protected ConfigFactory $configFactory;
    /**
     * @var Escaper
     */
    protected Escaper $escaper;
    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;
    /**
     * @var PriceCurrencyInterface
     */
    protected PriceCurrencyInterface $priceCurrency;
    /**
     * @var Data
     */
    protected Data $priceHelper;
    /**
     * @var RecurringFactory
     */
    protected RecurringFactory $recurringFactory;


    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param DataHelper $dataHelper
     * @param Escaper $escaper
     * @param CustomerFactory $customerFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param DataHelper $priceHelper
     * @param ConfigFactory $configFactory
     * @param RecurringFactory $recurringFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface       $context,
        UiComponentFactory     $uiComponentFactory,
        DataHelper             $dataHelper,
        Escaper                $escaper,
        CustomerFactory        $customerFactory,
        PriceCurrencyInterface $priceCurrency,
        Data                   $priceHelper,
        ConfigFactory          $configFactory,
        RecurringFactory       $recurringFactory,
        array                  $components = [],
        array                  $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        /** @var  _dataHelper */
        $this->_dataHelper = $dataHelper;
        /** @var  escaper */
        $this->_escaper = $escaper;
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _priceCurrency */
        $this->_priceCurrency = $priceCurrency;
        /** @var _priceHelper */
        $this->_priceHelper = $priceHelper;
        /** @var  configFactory */
        $this->configFactory = $configFactory;
        /** @var  recurringFactory */
        $this->recurringFactory = $recurringFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $finalPrice = $this->getData('name');

            foreach ($dataSource['data']['items'] as & $item) {
                $totalAmount = nl2br($item[$this->getData('name')]);
                $totalAmount = strip_tags($this->getOrderedAmount($totalAmount, $item));
                $item[$this->getData('name')] = $totalAmount ? $totalAmount : __('*');
            }
        }
        return $dataSource;
    }

    /**
     * @param $totalAmount
     * @param array $item
     * @return string
     */
    private function getOrderedAmount($totalAmount = null, array $item = []): string
    {
        $totalAmount = $totalAmount ?? 0;
        $recurringId = isset($item["recurring_id"]) ? $item["recurring_id"] : 0;
        $recurring = $this->recurringFactory->create()->load($recurringId);
        $totalAmount = isset($item["grand_total"]) ? $item["grand_total"] : 0;
        $surchargeAmount = isset($item["surcharge_amount"]) ? $item["surcharge_amount"] : 0;

        if ($recurring->getId()) {
            $surchargeAmount =  (string)$recurring->getSurchargeAmount();
            $totalAmount =  (string)$recurring->getGrandTotal();
        }
        $currencySymbol = $this->_priceCurrency->getCurrencySymbol();
        $surchargeAmount = str_replace($currencySymbol, "", $surchargeAmount);

        if ((float)$surchargeAmount > 0) {
            $totalAmount = (float)($totalAmount) + (float)$surchargeAmount;
        }
        return $this->_priceHelper->currency($totalAmount, true, true);
    }
}
