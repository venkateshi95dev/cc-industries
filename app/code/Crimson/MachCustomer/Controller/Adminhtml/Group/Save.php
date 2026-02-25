<?php

namespace Crimson\MachCustomer\Controller\Adminhtml\Group;

use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\Redirect;

/**
 * Class Save
 * @package Crimson\MachCustomer\Controller\Adminhtml\Group
 */
class Save extends \Magento\Customer\Controller\Adminhtml\Group\Save
{
    /**
     * Create or save customer group.
     *
     * @return Redirect|Forward
     */
    public function execute()
    {
        $taxClass = (int)$this->getRequest()->getParam('tax_class');

        /** @var \Magento\Customer\Api\Data\GroupInterface $customerGroup */
        $customerGroup = null;
        if ($taxClass) {
            $id = $this->getRequest()->getParam('id');
            $resultRedirect = $this->resultRedirectFactory->create();
            try {
                $customerGroupCode = (string)$this->getRequest()->getParam('code');
                if ($id !== null) {
                    $customerGroup = $this->groupRepository->getById((int)$id);
                    $customerGroupCode = $customerGroupCode ?: $customerGroup->getCode();
                } else {
                    $customerGroup = $this->groupDataFactory->create();
                }
                $customerGroup->setCode(!empty($customerGroupCode) ? $customerGroupCode : null);
                $customerGroup->setTaxClassId($taxClass);

                /**
                 * Adding the new field values.
                 */

                $customerGroup->getExtensionAttributes()->setCustomerPriceLevel(
                    $this->getRequest()->getParam('customer_price_level')
                );

                $customerGroup->getExtensionAttributes()->setMachTaxExempt(
                    $this->getRequest()->getParam('mach_tax_exempt')
                );

                $customerGroup->getExtensionAttributes()->setMachTaxNonExempt(
                    $this->getRequest()->getParam('mach_tax_non_exempt')
                );

                $this->groupRepository->save($customerGroup);

                $this->messageManager->addSuccess(__('You saved the customer group.'));
                $resultRedirect->setPath('customer/group');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                if ($customerGroup != null) {
                    $this->storeCustomerGroupDataToSession(
                        $this->dataObjectProcessor->buildOutputDataArray(
                            $customerGroup,
                            \Magento\Customer\Api\Data\GroupInterface::class
                        )
                    );
                }
                $resultRedirect->setPath('customer/group/edit', ['id' => $id]);
            }

            return $resultRedirect;
        } else {
            return $this->resultForwardFactory->create()->forward('new');
        }
    }
}
