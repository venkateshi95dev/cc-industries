<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_DiscountGroups
 */

namespace I95DevConnect\DiscountGroups\Model;

use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\Calculation\TotalBaseCalculator as MTotalBaseCalculator;
use Magento\Tax\Model\Config;
use Psr\Log\LoggerInterface;
use Magento\Tax\Model\Calculation;
class TotalBaseCalculator extends MTotalBaseCalculator
{
    /**
     * Quote session object
     *
     * @var Quote
     */
    protected $_session; // phpcs:ignore

    /**
     * Psr Logger
     *
     * @var LoggerInterface
     */
    protected $_logger; // phpcs:ignore

    /**
     * @var CustomerModelSession
     */
    protected $_customerSession; // phpcs:ignore

    /**
     *
     * @param TaxClassManagementInterface $taxClassService
     * @param TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory
     * @param AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory
     * @param AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory
     * @param Calculation $calculationTool
     * @param Config $config
     * @param int $storeId
     * @param Quote $quoteSession
     * @param Session $customerSession
     * @param LoggerInterface $logger
     * @param DataObject $addressRateRequest
     */
    public function __construct(
        TaxClassManagementInterface $taxClassService,
        TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory,
        AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory,
        AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory,
        Calculation $calculationTool,
        Config $config,
        $storeId,
        Quote $quoteSession,
        Session $customerSession,
        LoggerInterface $logger,
        DataObject $addressRateRequest = null
    ) {
        $this->_session = $quoteSession;
        $this->_customerSession = $customerSession;
        $this->_logger = $logger;
        parent::__construct(
            $taxClassService,
            $taxDetailsItemDataObjectFactory,
            $appliedTaxDataObjectFactory,
            $appliedTaxRateDataObjectFactory,
            $calculationTool,
            $config,
            $storeId,
            $addressRateRequest
        );
    }

    /**
     * Calculate tax in case of excluding tax in price
     *
     * @param QuoteDetailsItemInterface $item
     * @param float $quantity
     * @param bool $round
     * @return mixed
     */
    protected function calculateWithTaxNotInPrice(QuoteDetailsItemInterface $item, $quantity, $round = true)
    {
        $taxRateRequest = $this->getAddressRateRequest()->setProductClassId(
            $this->taxClassManagement->getTaxClassId($item->getTaxClassKey())
        );

        $appliedRates = $this->calculationTool->getAppliedRates($taxRateRequest);

        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($this->storeId);
        $discountAmount = $item->getDiscountAmount();
        $discountGroupAmount = $item->getDiscountGroupAmount();
        $discountTaxCompensationAmount = 0;

        // Calculate $rowTotal
        $price = $this->calculationTool->round($item->getUnitPrice());
        $rowTotal = $price * $quantity;
        $rowTaxes = [];
        $rowTaxesBeforeDiscount = [];
        $rowTaxesPercent = [];
        $appliedTaxes = [];
        //Apply each tax rate separately
        foreach ($appliedRates as $appliedRate) {
            $taxId = $appliedRate['id'];
            $taxRate = $appliedRate['percent'];
            $session = $this->_session->getQuote();
            $customerId = $session->getCustomerId();
            if ($customerId == '') {
                $customerId = $this->_customerSession->getCustomerId();
            }
            $sku = $item->getSku();
            $this->_logger->debug((string)$customerId); // Ensure string type
            $this->_logger->debug((string)$sku);        // Ensure string type
            $rowTaxPerRate = $this->calculationTool->calcTaxAmount($rowTotal, $taxRate, false, false);
            $deltaRoundingType = self::KEY_REGULAR_DELTA_ROUNDING;
            if ($applyTaxAfterDiscount) {
                $deltaRoundingType = self::KEY_TAX_BEFORE_DISCOUNT_DELTA_ROUNDING;
            }
            $rowTaxPerRate = $this->roundAmount($rowTaxPerRate, $taxId, false, $deltaRoundingType, $round, $item);
            $rowTaxAfterDiscount = $rowTaxPerRate;

            //Handle discount
            if ($applyTaxAfterDiscount) {
                //Need to handle originalDiscountAmount in future
                $taxableAmount = max($rowTotal - $discountAmount + $discountGroupAmount, 0);
                $rowTaxAfterDiscount = $this->calculationTool->calcTaxAmount(
                    $taxableAmount,
                    $taxRate,
                    false,
                    false
                );
                $rowTaxAfterDiscount = $this->roundAmount(
                    $rowTaxAfterDiscount,
                    $taxId,
                    false,
                    self::KEY_REGULAR_DELTA_ROUNDING,
                    $round,
                    $item
                );
            }

            $appliedTaxes[$taxId] = $this->getAppliedTax($rowTaxAfterDiscount, $appliedRate);
            $rowTaxes[] = $rowTaxAfterDiscount;
            $rowTaxesBeforeDiscount[] = $rowTaxPerRate;
            $rowTaxesPercent[] = $taxRate;
        }

        $rowTax = array_sum($rowTaxes);
        $rowTaxBeforeDiscount = array_sum($rowTaxesBeforeDiscount);
        $rowTaxPercent = array_sum($rowTaxesPercent);
        $rowTotalInclTax = $rowTotal + $rowTaxBeforeDiscount;
        $priceInclTax = $rowTotalInclTax / $quantity;
        if ($round) {
            $priceInclTax = $this->calculationTool->round($priceInclTax);
        }

        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode($item->getCode())
            ->setType($item->getType())
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($rowTotal)
            ->setRowTotalInclTax($rowTotalInclTax)
            ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
            ->setAssociatedItemCode($item->getAssociatedItemCode())
            ->setTaxPercent($rowTaxPercent)
            ->setAppliedTaxes($appliedTaxes);
    }
}
