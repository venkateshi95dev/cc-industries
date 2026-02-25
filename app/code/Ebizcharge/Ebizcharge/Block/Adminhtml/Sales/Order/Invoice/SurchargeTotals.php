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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\Sales\Order\Invoice;

use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Logger\EbizchargeLogger;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\Order\Invoice;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Adminhtml\Order\Invoice\Totals as CoreInvoiceTotals;

/**
 * Surcharge Total Amount for Invoice
 *
 * Class SurchargeTotals
 */
class SurchargeTotals extends CoreInvoiceTotals
{

    /**
     * @var EbizchargeLogger
     */
    private EbizchargeLogger $_ebizLogger;

    protected CustomerFactory $customerFactory;


    public function __construct(

        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        CustomerFactory $customerFactory,
        EbizchargeLogger $ebizLogger,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);

        /** @var  _ebizLogger */
        $this->_ebizLogger = $ebizLogger;
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
     * Retrieve current order model instance
     *
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
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
            $invoice = $this->getInvoice();
            $surchargeAmount = $invoice->getEcSurchargeAmount();
            $surchargePercentage = floatval($invoice->getEcSurchargePercentage()) > 0 ? floatval($invoice->getEcSurchargePercentage()) : 3;
            $surchargeAmount = floatval($invoice->getEcSurchargeAmount());

            if (!$isSurchargeEnabled || $surchargeAmount <= 0) {
                return $this;
            }


            $surchargeAmount = $invoice->getEcSurchargeIneligible() ?
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
