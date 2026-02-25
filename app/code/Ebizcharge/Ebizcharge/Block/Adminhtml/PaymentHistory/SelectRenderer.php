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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\PaymentHistory;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Select;
use Magento\Framework\DataObject;

/**
 * Payment History Select Renderer
 *
 * Class SelectRenderer
 */
class SelectRenderer extends Select
{
    /**
     * Render HTML
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row): string
    {
        $refNum = $row->getData('refNum');
        $customerEmail = $row->getData('customerEmail');

        return '<select name="actions" class="print-actions admin__control-select">
                    <option value="">Select</option>
                    <option class="print_receipt" value="' . $refNum . '"
                     data-action="print_receipt">Print Receipt</option>
                    <option class="print_email" data-action="print_email" data-refNum="' . $refNum . '"
                     data-email="' . $customerEmail . '"
                      value="' . $refNum.'||'.$customerEmail . '">Email Receipt</option>
                </select>';
    }
}
