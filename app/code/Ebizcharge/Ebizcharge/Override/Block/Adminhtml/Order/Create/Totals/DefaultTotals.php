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
namespace Ebizcharge\Ebizcharge\Override\Block\Adminhtml\Order\Create\Totals;

use Ebizcharge\Ebizcharge\Api\Data\SurchargeInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Totals\DefaultTotals as CoreDefaultTotals;

/**
 * Default Total Row Renderer
 *
 * Class DefaultTotals
 */
class DefaultTotals extends CoreDefaultTotals
{
    /**
     * Format price
     *
     * @param float $value
     * @return string
     */
    public function formatPrice($value)
    {
        if ($value === SurchargeInterface::EBIZ_INELIGIBLE_LABEL) {
            return '<span class="price">' . SurchargeInterface::EBIZ_INELIGIBLE_LABEL . '</span>';
        }

        return $this->priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore()
        );
    }
}
