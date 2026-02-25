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
use Ebizcharge\Ebizcharge\Model\ConfigFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Adminhtml\Order\View as BaseView;

/**
 * Surcharge Total Amount
 *
 * Class SurchargeTotals
 */
class View extends BaseView
{

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'adminhtml_order';
        $this->_mode = 'view';

         parent::_construct();

        $this->removeButton('delete');
        $this->removeButton('reset');
        $this->removeButton('save');
        $this->setId('sales_order_view');
        $order = $this->getOrder();

        $objectManager = ObjectManager::getInstance();
        $ebizConfigFactory = $objectManager->get(ConfigFactory::class)->create();
        $storeId = $ebizConfigFactory->getStoreId();

        if (!$order || !$ebizConfigFactory->isActive($storeId)) {
            return;
        }

      //  if ($this->_isAllowedAction('Magento_Sales::actions_edit') && $order->canEdit()) {

            if ($this->_isAllowedAction('Magento_Sales::actions_edit') ) {

            $onclickJs = 'jQuery(\'#order_edit\').orderEditDialog({message: \''
                . $this->getEditMessage($order) . '\', url: \'' . $this->getEditUrl()
                . '\'}).orderEditDialog(\'showDialog\');';

            $orderEditParams =  [
                'label' => __('Edit'),
                'class' => 'edit primary',
                'onclick' => $onclickJs,
                'data_attribute' => [
                    'mage-init' => '{"orderEditDialog":{}}',
                ]
            ];

            if($ebizConfigFactory->getVoidOrderEditFlow($storeId) === "2"){

                $this->addButton(
                    'get_review_payment_update',
                    [
                        'label' => __('Edit'),
                        'class' => 'edit primary',
                        'onclick' => 'setLocation(\'' . $this->getEditUrl() . '\')'
                    ]
                );
            }else{

                $this->addButton(
                    'order_edit',
                    $orderEditParams
                );
            }


        }


        if ($this->_isAllowedAction('Magento_Sales::cancel') && $order->canCancel()) {
            $this->addButton(
                'order_cancel',
                [
                    'label' => __('Cancel'),
                    'class' => 'cancel',
                    'id' => 'order-view-cancel-button',
                    'data_attribute' => [
                        'url' => $this->getCancelUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::emails') && !$order->isCanceled()) {
            $message = __('Are you sure you want to send an order email to customer?');
            $this->addButton(
                'send_notification',
                [
                    'label' => __('Send Email'),
                    'class' => 'send-email',
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getEmailUrl()}')"
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::creditmemo') && $order->canCreditmemo()) {
            $message = __(
                'This will create an offline refund. ' .
                'To create an online refund, open an invoice and create credit memo for it. Do you want to continue?'
            );
            $onClick = "setLocation('{$this->getCreditmemoUrl()}')";
            if($order->getPayment()) {
                if ($order->getPayment()->getMethodInstance()->isGateway()) {
                    $onClick = "confirmSetLocation('{$message}', '{$this->getCreditmemoUrl()}')";
                }
            }
            $this->addButton(
                'order_creditmemo',
                ['label' => __('Credit Memo'), 'onclick' => $onClick, 'class' => 'credit-memo']
            );
        }

        // invoice action intentionally
        if ($this->_isAllowedAction('Magento_Sales::invoice') && $order->canVoidPayment()) {
            $message = __('Are you sure you want to void the payment?');
            $this->addButton(
                'void_payment',
                [
                    'label' => __('Void'),
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getVoidPaymentUrl()}')"
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::hold') && $order->canHold()) {
            $this->addButton(
                'order_hold',
                [
                    'label' => __('Hold'),
                    'class' => __('hold'),
                    'id' => 'order-view-hold-button',
                    'data_attribute' => [
                        'url' => $this->getHoldUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::unhold') && $order->canUnhold()) {
            $this->addButton(
                'order_unhold',
                [
                    'label' => __('Unhold'),
                    'class' => __('unhold'),
                    'id' => 'order-view-unhold-button',
                    'data_attribute' => [
                        'url' => $this->getUnholdUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::review_payment')) {
            if ($order->canReviewPayment()) {
                $message = __('Are you sure you want to accept this payment?');
                $this->addButton(
                    'accept_payment',
                    [
                        'label' => __('Accept Payment'),
                        'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('accept')}')"
                    ]
                );
                $message = __('Are you sure you want to deny this payment?');
                $this->addButton(
                    'deny_payment',
                    [
                        'label' => __('Deny Payment'),
                        'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('deny')}')"
                    ]
                );
            }
            if ($order->canFetchPaymentReviewUpdate()) {
                $this->addButton(
                    'get_review_payment_update',
                    [
                        'label' => __('Get Payment Update'),
                        'onclick' => 'setLocation(\'' . $this->getReviewPaymentUrl('update') . '\')'
                    ]
                );
            }
        }

        if ($this->_isAllowedAction('Magento_Sales::invoice') && $order->canInvoice()) {
            $_label = $order->getForcedShipmentWithInvoice() ? __('Invoice and Ship') : __('Invoice');
            $this->addButton(
                'order_invoice',
                [
                    'label' => $_label,
                    'onclick' => 'setLocation(\'' . $this->getInvoiceUrl() . '\')',
                    'class' => 'invoice'
                ]
            );
        }

        if ($this->_isAllowedAction(
                'Magento_Sales::ship'
            ) && $order->canShip() && !$order->getForcedShipmentWithInvoice()
        ) {
            $this->addButton(
                'order_ship',
                [
                    'label' => __('Ship'),
                    'onclick' => 'setLocation(\'' . $this->getShipUrl() . '\')',
                    'class' => 'ship'
                ]
            );
        }

        if ($this->_isAllowedAction(
                'Magento_Sales::reorder'
            ) && $this->_reorderHelper->isAllowed(
                $order->getStore()
            ) && $order->canReorderIgnoreSalable()
        ) {
            $this->addButton(
                'order_reorder',
                [
                    'label' => __('Reorder'),
                    'onclick' => 'setLocation(\'' . $this->getReorderUrl() . '\')',
                    'class' => 'reorder'
                ]
            );
        }
        parent::_construct();
    }

    /**
     * Get edit message
     *
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Framework\Phrase
     */
    protected function getEditMessage($order)
    {

        // see if order has non-editable products as items
        $nonEditableTypes = $this->getNonEditableTypes($order);
        if (!empty($nonEditableTypes)) {
            return __(
                'This order contains (%1) items and therefore cannot be edited through the admin interface. ' .
                'If you wish to continue editing, the (%2) items will be removed, ' .
                ' the order will be canceled and a new order will be placed.',
                implode(', ', $nonEditableTypes),
                implode(', ', $nonEditableTypes)
            );
        }
        return __('Are you sure? This order will be canceled and a new one will be created instead.');
    }

}
