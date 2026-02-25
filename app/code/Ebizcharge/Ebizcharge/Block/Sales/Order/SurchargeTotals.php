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

namespace Ebizcharge\Ebizcharge\Block\Sales\Order;

use Ebizcharge\Ebizcharge\Model\Config;
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
    /**
     * @var Config
     */
    protected Config $_ebizConfig;
    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;

    /**
     * @param Config $ebizConfig
     * @param CustomerFactory $customerFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Config $ebizConfig,
        CustomerFactory $customerFactory,
        Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        /** @var  _ebizConfig */
        $this->_ebizConfig = $ebizConfig;
        /** @var  _customerFactory */
        $this->_customerFactory = $customerFactory;
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
        $isSurchargeEnabled = $this->_customerFactory->create()->isSurchargeEnabled();
        $surchargePercentage = floatval($this->getSource()->getEcSurchargePercentage());
        $surchargeAmount = $this->getSource()->getEcSurchargeAmount();

        if (!$isSurchargeEnabled || $surchargePercentage <= 0) {
            return $this;
        }

        $total = $this->_customerFactory->create()->createSurchargeTotalObject(
            $surchargeAmount,
            $surchargePercentage
        );

        if ($total) {
            $this->getParentBlock()->addTotalBefore($total, 'shipping');
        }

        return $this;
    }
}
