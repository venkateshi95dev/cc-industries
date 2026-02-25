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

use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;


/**
 * Recurring Amount Ui Column Component
 *
 * Class Amount
 */
class Amount extends Column
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
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param CustomerFactory $customerFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param Data $priceHelper
     * @param ConfigFactory $configFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface       $context,
        UiComponentFactory     $uiComponentFactory,
        Escaper                $escaper,
        CustomerFactory        $customerFactory,
        PriceCurrencyInterface $priceCurrency,
        Data                   $priceHelper,
        ConfigFactory          $configFactory,
        array                  $components = [],
        array                  $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);

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

                $totalAmount = nl2br($this->_escaper->escapeHtml($item[$this->getData('name')]));
                $totalAmount = strip_tags($this->getOrderedAmount($totalAmount, $item));

                $item[$this->getData('name')] = $totalAmount ? $totalAmount : __('*');
            }
        }

        return $dataSource;
    }

    /**
     * @param $totalAmount
     * @param $item
     * @return string
     */
    public function getOrderedAmount($totalAmount = 0, $item = []): string
    {

        $surchargeAmount = isset($item["surcharge_amount"]) ? $item["surcharge_amount"] : 0;
        $currencySymbol = $this->_priceCurrency->getCurrencySymbol();
        $surchargeAmount = str_replace($currencySymbol, "", $surchargeAmount);
        if ((float)$surchargeAmount > 0) {
            $totalAmount = (float)$totalAmount + (float)$surchargeAmount;
        }
        return $this->_priceHelper->currency($totalAmount, true, true);
    }
}
