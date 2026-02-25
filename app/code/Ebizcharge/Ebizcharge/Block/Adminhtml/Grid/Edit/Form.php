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

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\Grid\Edit;

use Ebizcharge\Ebizcharge\Model\CustomerFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Adminhtml Add bank account form
 *
 * Class Form
 */
class Form extends Generic
{
    /**
     * @var CustomerFactory
     */
    private CustomerFactory $_customerFactory;

    /**
     * @var TranApi
     */
    private TranApi $_soapApiModel;

    /**
     * @var SecureHtmlRenderer
     */
    private SecureHtmlRenderer $_secureRenderer;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param CustomerFactory $customerFactory
     * @param TranApi $soapApiModel
     * @param SecureHtmlRenderer $secureRenderer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CustomerFactory $customerFactory,
        TranApi $soapApiModel,
        SecureHtmlRenderer $secureRenderer,
        array $data = []
    ) {
        /** Parent Reconstruct */
        parent::__construct($context, $registry, $formFactory, $data);
        /** @var _customerFactory */
        $this->_customerFactory = $customerFactory;
        /** @var _soapApiModel */
        $this->_soapApiModel = $soapApiModel;
        /** @var  _secureRenderer */
        $this->_secureRenderer = $secureRenderer;
    }

    /**
     * Prepare bank account form.
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'enctype' => 'multipart/form-data',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]
        );

        $form->setHtmlIdPrefix('ebizId_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Bank Account Information'),
                'class' => 'fieldset-wide main-panel-container'
            ]
        );

        $fieldset->addField(
            'action',
            'hidden',
            [
                'name' => 'action',
                'id' => 'action',
            ]
        );
        $fieldset->addField(
            'cid',
            'hidden',
            [
                'name' => 'cid',
                'id' => 'cid',
            ]
        );
        $fieldset->addField(
            'mid',
            'hidden',
            [
                'name' => 'mid',
                'id' => 'mid',
            ]
        );

        $fieldset->addField(
            'customerId',
            'hidden',
            [
                'name' => 'customerId',
                'id' => 'customerId',
            ]
        );
        $fieldset->addField(
            'is_default',
            'hidden',
            [
                'name' => 'is_default',
                'id' => 'is_default',
                'class' => 'is_default',
                'value' => 0
            ]
        );

        $fieldset->addField(
            'accountHolder',
            'text',
            [
                'name' => 'accountHolder',
                'label' => __('Account Holder'),
                'id' => 'accountHolder',
                'title' => __('Account Holder'),
                'class' => 'required-entry',
                'placeholder' => __('Account Holder'),
                'required' => true,
            ]
        );

        $achNumberFieldParams = [
            'name' => 'achNumber',
            'label' => __('Account Number'),
            'id' => 'achNumber',
            'placeholder' => __('Account Number'),
            'title' => __('Please enter a valid bank account number'),
            'class' => $this->_request->getParam('action') === 'edit'
                ? ' disabled'
                : ' required-entry min-length validate-digits validate-zero-or-greater',
            'maxlength' => '14',
            'required' => true,
        ];
        if ($this->_request->getParam('action') === 'edit') {
            $achNumberFieldParams['readonly'] = true;
        }

        $fieldset->addField(
            'achNumber',
            'text',
            $achNumberFieldParams
        );

        $achRouteFieldParams =   [
            'name' => 'achRoute',
            'label' => __('Routing Number'),
            'id' => 'achRoute',
            'title' => __('Routing Number'),
            'placeholder' => __('Routing Number'),
            'class' => $this->_request->getParam('action') == 'edit' ? ' disabled'
                : ' required-entry min-length validate-digits validate-zero-or-greater',
            'maxlength' => '9',
            'required' => true,
        ];

        if ($this->_request->getParam('action') === 'edit') {
            $achRouteFieldParams['readonly'] = true;
        }

        $fieldset->addField(
            'achRoute',
            'text',
            $achRouteFieldParams
        );

        $fieldset->addField(
            'achType',
            'select',
            [
                'name' => 'achType',
                'label' => __('Account Type'),
                'id' => 'achType',
                'title' => __('Account Type'),
                'values' => $this->getAccountTypes(),
                'class' => 'status',
                'required' => true,
            ]
        );
        $fieldset->addField(
            'isDefault',
            'checkbox',
            [
                'name' => 'isDefault',
                'label' => __('Default Account'),
                'id' => 'isDefault',
                'title' => __('Default Account'),
                'class' => 'status',
                'after_element_html' => $this->_secureRenderer->renderTag(
                    'script',
                    [],
                    'require(["jquery"], function ($) {
                         $("#ebizId_isDefault").click(function(){
                         console.log(this);
                            if($("#ebizId_isDefault").is(\':checked\')){
                            $("#ebizId_is_default").val(1);
                            }else{
                            $("#ebizId_is_default").val(0);
                            }
                         });

                         $("#ebizId_isDefault").val() == 1 && $( "#ebizId_isDefault" ).prop("checked", true ) &&
                          $( "#ebizId_isDefault" ).prop( "readonly", true );

                        $(document).on("click", "#save", function(){
                          if($("#edit_form").valid()) {
                                $("body").trigger("processStart");
                          }
                         });

                         $(".min-length").attr("minlength", "9");

                    });',
                    false
                ),
                'required' => false,
            ]
        );

        $form->setValues($this->getFormData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get Account Types
     *
     * @return array
     */
    private function getAccountTypes()
    {
        return [
            'checking' => __('Checking'),
            'saving' => __('Saving')
        ];
    }

    /**
     * Get Form Data
     *
     * @return array
     */
    private function getFormData()
    {
        /** @var $customerId */
        $customerId = $this->_request->getParam('id');
        $customer = $this->_customerFactory->create()->load($customerId);
        $customerToken = $customer->getEcCustToken();

        $formData = [
            'customerId' => $customerId
        ];

        /** Get FormData */
        if ($this->_request->getParam('action') == 'edit') {
            $formData['action'] = 'edit';

            $ebzcCustomerId = $this->_request->getParam('cid');
            $ebzcMethodId = $this->_request->getParam('mid');

            $formData['cid'] = $ebzcCustomerId;
            $formData['mid'] = $ebzcMethodId;

            /** @var $bankAccount */
            $bankAccount = $this->_soapApiModel->getCustomerPaymentMethodProfile($customerToken, $ebzcMethodId);

            /** Bank Account Check */
            if ($bankAccount) {
                $formData['achType'] = $bankAccount->AccountType ?? null;
                $formData['accountHolder'] = $bankAccount->AccountHolderName ?? '';
                $formData['achRoute'] = $bankAccount->Routing ?? '';
                $formData['achNumber'] = $bankAccount->Account ?? '';
                $formData['is_default'] = $bankAccount->SecondarySort == 0 ? 1 : null;
                $formData['isDefault'] = $bankAccount->SecondarySort == 0 ? 1 : null;
            }
        }

        return $formData;
    }
}
