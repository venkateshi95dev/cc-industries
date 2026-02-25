<?php

namespace Crimson\MachCustomer\Model;

class CustomerGroupExtAtt extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Crimson\MachCustomer\Model\ResourceModel\CustomerGroupExtAtt');
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->_getData('customer_group_id');
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        $this->setData('customer_group_id', $id);
    }

    /**
     * @return mixed
     */
    public function getCustomerPriceLevel()
    {
        return $this->_getData('customer_price_level');
    }

    /**
     * @param string $customerPriceLevel
     * @return $this
     */
    public function setCustomerPriceLevel($customerPriceLevel)
    {
        $this->setData('customer_price_level', $customerPriceLevel);
    }

    /**
     * @return mixed
     */
    public function getMachTaxExempt()
    {
        return $this->_getData('mach_tax_exempt');
    }

    /**
     * @param string $machTaxExempt
     * @return $this
     */
    public function setMachTaxExempt($machTaxExempt)
    {
        $this->setData('mach_tax_exempt', $machTaxExempt);
    }

    /**
     * @return mixed
     */
    public function getMachTaxNonExempt()
    {
        return $this->_getData('mach_tax_non_exempt');
    }

    /**
     * @param string $machTaxNonExempt
     * @return $this
     */
    public function setMachTaxNonExempt($machTaxNonExempt)
    {
        $this->setData('mach_tax_non_exempt', $machTaxNonExempt);
    }

}