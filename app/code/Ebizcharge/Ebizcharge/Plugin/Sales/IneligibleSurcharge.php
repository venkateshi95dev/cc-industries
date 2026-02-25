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

namespace Ebizcharge\Ebizcharge\Plugin\Sales;

use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Ebizcharge\Ebizcharge\Model\AbstractModel;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Block\Adminhtml\Totals;

/**
 * Ineligible Surcharge total label
 *
 * Class IneligibleSurcharge
 */
class IneligibleSurcharge extends AbstractModel
{
    /**
     * Around Format Value
     *
     * @param Totals $subject
     * @param callable $proceed
     * @param DataObject $total
     * @return string
     * @throws NoSuchEntityException
     */
    public function aroundFormatValue(
        Totals $subject,
        callable $proceed,
        DataObject $total
    ) {
        $configFactory = $this->configFactory->create();
        $storeId = $configFactory->getStoreId();
        $isEbizChargeActive = $configFactory->isActive($storeId);

        if ($isEbizChargeActive && $total->getValue() === SurchargeInterface::EBIZ_INELIGIBLE_LABEL) {
            return '<span class="price">' . SurchargeInterface::EBIZ_INELIGIBLE_LABEL . '</span>';
        }

        return $proceed($total);
    }
}
