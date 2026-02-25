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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\CustomerEdit\Tab\View;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Select;
use Magento\Framework\DataObject;

/**
 * Payment methods column edit/delete action
 *
 * Class ActionColumn
 */
class ActionColumn extends Select
{
    /**
     * Render Output
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row): string
    {
        /** @var get data $rowData */
        $rowData = $row->getData();
        $saveAction = 'save';

        /** checking if save card or bank account */
        if (isset($rowData['cardHolder'])) {
            $saveAction = 'saveCard';
        }

        $mid = $row->getData('methodId');
        $cid = $row->getData('ebizCustId');
        $customer_id = $row->getData('customerId');
        $editPaymentMethodUrl = $this->getUrl('ebizcharge_ebizcharge/*/add', [
            '_secure' => true,
            'id' => $customer_id,
            'customer_id' => $customer_id,
            'action' => 'edit',
            'mid' => $mid
        ]);
        $deletePaymentMethodUrl = $this->getUrl('ebizcharge_ebizcharge/*/'.$saveAction, [
            '_secure' => true,
            'id' => $customer_id,
            'action' => 'del',
            'customer_id' => $customer_id,
            'mid' => $mid
        ]);


        $html =
               '<a title="'.__('Edit your payment method.').'" href="' . $editPaymentMethodUrl . '"> '.__('Edit').'</a>';
        $html .='&nbsp;| &nbsp;';
        $html .='<a title="'.__('Delete your payment method.').'"  onclick="if (confirm(\'Are you sure you want to delete this payment method?\')){return true;}else{event.stopPropagation(); event.preventDefault();};" href="' . $deletePaymentMethodUrl . '" id="deleteCard">Delete</a>'
        ;

        return $html;
    }
}
