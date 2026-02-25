<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2019 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_CloudConnect
 */

namespace I95DevConnect\CloudConnect\Block\System\Config\Form\Field;

/**
 * Class to make field are readOnly of i95dev_adapter_configurations
 */

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class for making button Disable
 */
class Disable extends Field
{
    /**
     * Set field as readonly
     *
     * @param AbstractElement $element
     * @return type boolian
     * @author Chandra Prasad
     */
    // @codingStandardsIgnoreStart
    public function _getElementHtml(AbstractElement $element)
    {
        $element->setData('readonly', 1);

        return $element->getElementHtml();
    }
    // @codingStandardsIgnoreEnd
}
