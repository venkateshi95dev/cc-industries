<?php

namespace Crimson\MachCustomer\Model\ResourceModel\Group\Grid;

/**
 * Class Collection
 * @package Crimson\MachCustomer\Model\ResourceModel\Group\Grid
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Group\Grid\Collection
{

    /**
     * Resource initialization
     * @return $this
     */
    protected function _initSelect(): Collection
    {
        parent::_initSelect();
        $this->addExtAttributes();
        return $this;
    }

    /**
     * Add extension attributes
     *
     * @return $this
     */
    public function addExtAttributes(): Collection
    {
        $this->getSelect()->joinLeft(
            ['customer_group_extension_attributes' => $this->getTable('customer_group_extension_attributes')],
            "main_table.customer_group_id = customer_group_extension_attributes.customer_group_id",
            [
                'customer_price_level' => 'customer_group_extension_attributes.customer_price_level',
                'mach_tax_exempt' => 'customer_group_extension_attributes.mach_tax_exempt',
                'mach_tax_non_exempt' => 'customer_group_extension_attributes.mach_tax_non_exempt'
            ]
        );

        return $this;
    }

}
