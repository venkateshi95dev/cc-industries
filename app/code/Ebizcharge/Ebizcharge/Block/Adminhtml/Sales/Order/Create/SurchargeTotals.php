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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\Sales\Order\Create;

use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Totals as CoreTotals;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Surcharge Total Amount
 *
 * Class SurchargeTotals
 */
class SurchargeTotals extends CoreTotals
{
    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $customerFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Sales\Model\Config $salesConfig,
        CustomerFactory $customerFactory,
        array $data = []
    )
    {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency,$salesData,$salesConfig,  $data);
        /** @var  customerFactory */
        $this->customerFactory = $customerFactory;
    }

    /**
     * Get Source
     *
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * To add surcharge amount with percentage in totals
     *
     * @return $this
     */
    public function initTotals()
    {
        /** To check surcharge is enabled on EBizCharge Portal & on Magento as well */
        $isSurchargeEnabled = $this->customerFactory->create()->isSurchargeEnabled();
        $surchargeAmount = $this->getSource()->getEcSurchargeAmount();
        $surchargePercentage = floatval($this->getSource()->getEcSurchargePercentage());

        if (!$isSurchargeEnabled || $surchargePercentage <= 0) {
            return $this;
        }

        $surchargeAmount = $this->getSource()->getEcSurchargeIneligible() ?
            SurchargeInterface::EBIZ_INELIGIBLE_LABEL : $surchargeAmount;

        $totals = $this->customerFactory->create()->createSurchargeTotalObject(
            $surchargeAmount,
            $surchargePercentage
        );

        if ($totals) {
            $this->getParentBlock()->addTotalBefore($totals, 'shipping');
        }

        return $this;
    }
}
