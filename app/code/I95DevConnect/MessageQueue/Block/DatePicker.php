<?php

/**
 * @author i95Dev Team
 * @copyright Copyright (c) 2024 i95Dev(https://www.i95dev.com)
 * @package I95DevConnect_MessageQueue
 */

namespace I95DevConnect\MessageQueue\Block;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Stdlib\DateTime;

/**
 * Block responsible for rendering Datepicker
 */
class DatePicker extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Render block function
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->setDateFormat(DateTime::DATE_PHP_FORMAT);
        return parent::render($element);
    }
}
