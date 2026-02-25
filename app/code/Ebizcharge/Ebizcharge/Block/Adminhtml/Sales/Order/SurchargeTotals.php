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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\Sales\Order;

use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Surcharge Total Amount
 *
 * Class SurchargeTotals
 */
class SurchargeTotals extends Template
{

    protected CustomerFactory $customerFactory;

    /**
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        Context         $context,
        CustomerFactory $customerFactory,
        array           $data = []
    )
    {
        parent::__construct(
            $context,
            $data
        );
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
        /** To check surcharge is enabled on Ebiz Portal & on Magento as well */
        $isSurchargeEnabled = $this->customerFactory->create()->isSurchargeEnabled();
        $surchargeAmount = $this->getSource()->getEcSurchargeAmount();
        $surchargePercentage = floatval($this->getSource()->getEcSurchargePercentage());
      //  dump($isSurchargeEnabled);exit;

        if (!$isSurchargeEnabled || $surchargePercentage <= 0) {
            return $this;
        }

        $surchargeAmount = $this->getSource()->getEcSurchargeIneligible() ?
            SurchargeInterface::EBIZ_INELIGIBLE_LABEL : $surchargeAmount;

        $total = $this->customerFactory->create()->createSurchargeTotalObject(
            $surchargeAmount,
            $surchargePercentage
        );

        if ($total) {
            $this->getParentBlock()->addTotalBefore($total, 'shipping');
        }

        return $this;
    }
}
