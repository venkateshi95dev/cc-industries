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

// phpcs:ignore
namespace Ebizcharge\Ebizcharge\Block\Adminhtml\Sales\Order\Invoice\NewInvoice;

use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\Order;
use Ebizcharge\Ebizcharge\Model\Order\Invoice;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;

/**
 * Surcharge Total Amount for Invoice create/update
 *
 * Class SurchargeTotals
 */
class SurchargeTotals extends Template
{

    protected CustomerFactory $customerFactory;

    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $_ebizLogger;

    /**
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param EbizchargeLogger $ebizLogger
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        EbizchargeLogger $ebizLogger,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        /** @var  customerFactory */
        $this->customerFactory = $customerFactory;
        /** @var  _ebizLogger */
        $this->_ebizLogger = $ebizLogger;
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
     * Retrieve current order model instance
     *
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }

    /**
     * Check is Surcharge Already Included
     *
     * @param int|string $invoiceId
     * @param InvoiceCollection $invoiceCollection
     * @return bool
     */
    public function checkSurchargeAlreadyIncluded($invoiceId, $invoiceCollection)
    {
        $surchargeIncluded = false;
        /** @var Invoice $invoice */
        foreach ($invoiceCollection as $invoice) {
            if ($invoice->getId() !== $invoiceId &&
                floatval($invoice->getEcSurchargePercentage()) > 0) {
                $surchargeIncluded = true;
            }
        }

        return $surchargeIncluded;
    }

    /**
     * To add surcharge amount with percentage in totals
     *
     * @return $this
     */
    public function initTotals()
    {
        /** To check surcharge is enabled on Ebiz Portal & on Magento as well */
        $isSurchargeEnabled = $this->customerFactory->create()->isSurchargeEnabled();

        try {
            /** @var Order $order */
            $order = $this->getInvoice()->getOrder();
            $checkSurcharge = $this->checkSurchargeAlreadyIncluded(
                $this->getInvoice()->getId(),
                $order->getInvoiceCollection()
            );
           // $surchargeAmount = $order->getEcSurchargeAmount();
            $surchargeAmount = floatval($this->getInvoice()->getec_surcharge_amount()) ?? 0;
            $surchargePercentage = floatval($order->getEcSurchargePercentage()) ?? 0;

            if (!$isSurchargeEnabled || $surchargePercentage <= 0 ) {
                return $this;
            }

            $surchargeAmount = $order->getEcSurchargeIneligible() ?
                SurchargeInterface::EBIZ_INELIGIBLE_LABEL : $surchargeAmount;

            $total = $this->customerFactory->create()->createSurchargeTotalObject(
                $surchargeAmount,
                $surchargePercentage
            );

            if ($total) {
                $this->getParentBlock()->addTotalBefore($total, 'shipping');
            }
        } catch (\Exception $exception) {
            $this->_ebizLogger->error($exception->getMessage());
        }

        return $this;
    }
}
