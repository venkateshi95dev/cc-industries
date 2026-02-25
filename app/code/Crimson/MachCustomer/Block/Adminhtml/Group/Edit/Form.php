<?php

namespace Crimson\MachCustomer\Block\Adminhtml\Group\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Form
 * @package Crimson\MachCustomer\Block\Adminhtml\Group\Edit
 */
class Form extends \Magento\Customer\Block\Adminhtml\Group\Edit\Form
{

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $groupId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_GROUP_ID);
        /** @var \Magento\Customer\Api\Data\GroupInterface $customerGroup */
        if ($groupId === null) {
            $customerGroup = $this->groupDataFactory->create();
            $defaultCustomerTaxClass = $this->_taxHelper->getDefaultCustomerTaxClass();
        } else {
            $customerGroup = $this->_groupRepository->getById($groupId);
            $defaultCustomerTaxClass = $customerGroup->getTaxClassId();
        }

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Group Information')]);

        $validateClass = sprintf(
            'required-entry validate-length maximum-length-%d',
            \Magento\Customer\Model\GroupManagement::GROUP_CODE_MAX_LENGTH
        );
        $name = $fieldset->addField(
            'customer_group_code',
            'text',
            [
                'name' => 'code',
                'label' => __('Group Name'),
                'title' => __('Group Name'),
                'note' => __(
                    'Maximum length must be less then %1 characters.',
                    \Magento\Customer\Model\GroupManagement::GROUP_CODE_MAX_LENGTH
                ),
                'class' => $validateClass,
                'required' => true
            ]
        );

        if ($customerGroup->getId() == 0 && $customerGroup->getCode()) {
            $name->setDisabled(true);
        }

        $fieldset->addField(
            'tax_class_id',
            'select',
            [
                'name' => 'tax_class',
                'label' => __('Tax Class'),
                'title' => __('Tax Class'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $this->_taxCustomer->toOptionArray(),
            ]
        );

        //New fields
        $fieldset->addField(
            'customer_price_level',
            'text',
            [
                'name' => 'customer_price_level',
                'label' => __('Customer Price Level'),
                'title' => __('Customer Price Level'),
                'class' => 'validate-number',
                'required' => false
            ]
        );

        $fieldset->addField(
            'mach_tax_exempt',
            'select',
            [
                'name' => 'mach_tax_exempt',
                'label' => __('Mach Tax Exempt'),
                'title' => __('Mach Tax Exempt'),
                'class' => 'validate-number',
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'required' => false
            ]
        );

        $fieldset->addField(
            'mach_tax_non_exempt',
            'select',
            [
                'name' => 'mach_tax_non_exempt',
                'label' => __('Mach Tax Non Exempt'),
                'title' => __('Mach Tax Non Exempt'),
                'class' => 'validate-number',
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'required' => false
            ]
        );

        if ($customerGroup->getId() !== null) {
            // If edit add id
            $form->addField('id', 'hidden', ['name' => 'id', 'value' => $customerGroup->getId()]);
        }

        if ($this->_backendSession->getCustomerGroupData()) {
            $form->addValues($this->_backendSession->getCustomerGroupData());
            $this->_backendSession->setCustomerGroupData(null);
        } else {
            // TODO: need to figure out how the DATA can work with forms
            $form->addValues(
                [
                    'id' => $customerGroup->getId(),
                    'customer_group_code' => $customerGroup->getCode(),
                    'tax_class_id' => $defaultCustomerTaxClass,
                    'customer_price_level' => (int)$customerGroup->getExtensionAttributes()->getCustomerPriceLevel(),
                    'mach_tax_exempt' => (int)$customerGroup->getExtensionAttributes()->getMachTaxExempt(),
                    'mach_tax_non_exempt' => (int)$customerGroup->getExtensionAttributes()->getMachTaxNonExempt(),
                ]
            );
        }

        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('customer/*/save'));
        $form->setMethod('post');
        $this->setForm($form);
    }

}
