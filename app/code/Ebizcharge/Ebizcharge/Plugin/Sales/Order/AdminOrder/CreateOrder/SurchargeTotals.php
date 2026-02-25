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

namespace Ebizcharge\Ebizcharge\Plugin\Sales\Order\AdminOrder\CreateOrder;

use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Block\Adminhtml\Order\Create\Totals;

/**
 * Surcharge Totatls
 */
class SurchargeTotals extends AbstractModel
{

    /**
     * After Get Totals
     *
     * @param Totals $orderTotals
     * @param array $resultTotals
     * @return array
     * @throws NoSuchEntityException
     */
    public function afterGetTotals(Totals $orderTotals, array $resultTotals = [])
    {

        /**
         * Store ID
         */
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);
        $quote = $orderTotals->getQuote();
        $grandTotal = $quote->getGrandTotal();
        $quoteItems = $quote->getItems() ? $quote->getItems() : [];
        $surchargeAmount = 0;

        if ($isEbizChargeActive) {

            if (count($quoteItems) === 0) {
                // $resultTotals["surcharge"] = $surchargeAmount;
                // return $resultTotals;
            }

            /** To check surcharge is enabled on EBizCharge Portal & on Magento as well */
            $customerFactory = $this->customerFactory->create();
            $surchargeSettings = $customerFactory->getSurchargeSettings($storeId);
            $isSurchargeEnabled = isset($surchargeSettings["surchargeEnabled"]) ?

                (bool)$surchargeSettings["surchargeEnabled"] : false;
            $surchargePercentage = isset($surchargeSettings["surchargePercentage"]) ? (float)$surchargeSettings["surchargePercentage"] : 0;

            if (!$isSurchargeEnabled || $surchargePercentage <= 0) {
                return $resultTotals;
            }

            $surchargeAmount = round($grandTotal * (float)$surchargePercentage / 100, 2);
            $surchargeTotal = $this->customerFactory->create()->createSurchargeTotalObject(
                $surchargeAmount,
                $surchargePercentage
            );
            /**
             * Grand Total Object
             */
            $grandTotalObj = isset($resultTotals["grand_total"]) ? $resultTotals["grand_total"] : null;
            $grandTotal = $quote->getGrandTotal() ?? 0;

            /**
             * if Surcharge Amount is greater than 0
             */
            if ($surchargeAmount > 0) {
                $grandTotal = (float)$grandTotal + (float)$surchargeAmount;
                $grandTotalObj->setValue($grandTotal);
                $quote->setEcSurchargeAmount($surchargeAmount)
                    ->setEcSurchargePercentage($surchargePercentage)
                    ->save();
            }
            $resultTotals["surcharge"] = $surchargeTotal;
        }

        return $resultTotals;
    }
}
