<?php

namespace Crimson\MachCustomer\Plugin;

use Crimson\MachCustomer\Model\CustomerGroupExtAttFactory;
use Crimson\MachCustomer\Model\ResourceModel\CustomerGroupExtAtt;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;

/**
 * Class CustomerGroupRepositoryInterfacePlugin
 * @package Crimson\MachCustomer\Plugin
 */
class CustomerGroupRepositoryInterfacePlugin
{

    /** @var CustomerGroupExtAttFactory $objectFactory */
    protected $objectFactory;

    /** @var CustomerGroupExtAtt $cgExtAttrRepositoryInterface */
    protected $cgExtAttrSourceModel;

    public function __construct(
        CustomerGroupExtAttFactory $objectFactory,
        CustomerGroupExtAtt $cgExtAttrSourceModel
    ) {
        $this->objectFactory = $objectFactory;
        $this->cgExtAttrSourceModel = $cgExtAttrSourceModel;
    }

    /**
     * @param GroupRepositoryInterface $subject
     * @param GroupInterface $entity
     * @return GroupInterface
     */
    public function afterGetById(GroupRepositoryInterface $subject, GroupInterface $entity): GroupInterface
    {
        $this->_attachExtensionAttributes($entity);
        return $entity;
    }

    /**
     * @param GroupRepositoryInterface $subject
     * @param GroupInterface $entity
     * @return GroupInterface
     */
    public function afterSave(GroupRepositoryInterface $subject, GroupInterface $entity): GroupInterface
    {
        $this->_attachExtensionAttributesAndSave($entity);
        return $entity;
    }

    /**
     * @param GroupInterface $group
     *
     * @return $this
     */
    protected function _attachExtensionAttributes(GroupInterface $group): CustomerGroupRepositoryInterfacePlugin
    {
        $object = $this->objectFactory->create();
        $this->cgExtAttrSourceModel->load($object,$group->getId());
        if($object && $object->getId() !== null){
            $group->getExtensionAttributes()->setCustomerPriceLevel($object->getData('customer_price_level'));
            $group->getExtensionAttributes()->setMachTaxExempt($object->getData('mach_tax_exempt'));
            $group->getExtensionAttributes()->setMachTaxNonExempt($object->getData('mach_tax_non_exempt'));
        }

        return $this;
    }

    /**
     * @param GroupInterface $group
     *
     * @return $this
     */
    protected function _attachExtensionAttributesAndSave(GroupInterface $group): CustomerGroupRepositoryInterfacePlugin
    {

        if($group && $group->getId() !== null){
            $object = $this->objectFactory->create();
            $this->cgExtAttrSourceModel->load($object,$group->getId());

            $data = [
                'customer_group_id'    => $group->getId(),
                'customer_price_level' => $group->getExtensionAttributes()->getCustomerPriceLevel(),
                'mach_tax_exempt'      => $group->getExtensionAttributes()->getMachTaxExempt(),
                'mach_tax_non_exempt'  => $group->getExtensionAttributes()->getMachTaxNonExempt(),
            ];

            $object->setData($data);
            $this->cgExtAttrSourceModel->save($object);
        }

        return $this;
    }
}
