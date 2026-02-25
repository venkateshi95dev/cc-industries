<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Block\System\Config;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\ValidatorException;

/**
 * Class for maintaining the status of ShippingMapping enabled field
 */
class ShippingMapStatus extends Value
{
    /**
     * Before save plugin
     *
     * @return void
     * @throws ValidatorException
     */
    public function beforeSave()
    {
        if ($this->isValueChanged()) {
            throw new ValidatorException(
                __('Cannot change value of Shipping mapping status')
            );
        }
        parent::beforeSave();
    }
}
