<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Model\ConfigValues\Customer;

use Magento\Framework\Option\ArrayInterface;
use Magento\Tax\Model\ClassModel;

/**
 * Class for Tax Class
 */
class TaxClass implements ArrayInterface
{
    /**
     * @var ClassModel $customer
     */
    public $customer;

    /**
     *
     * @param ClassModel $customer
     */
    public function __construct(ClassModel $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Returns tax class option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $taxClasses = [];
        $customerData = $this->customer->getCollection()
             ->addAttributeToSelect(['class_type', 'class_name', 'class_id'])
             ->getData();

        foreach ($customerData as $val) {
            if ($val['class_type'] == 'CUSTOMER') {
                $taxClasses[] = ['value' => $val['class_id'], 'label' => __($val['class_name'])];
            }
        }
        return $taxClasses;
    }
}
