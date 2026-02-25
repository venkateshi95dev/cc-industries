<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_AjaxCart
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AjaxCart\Model\Config\Backend\Number;

use Magento\Framework\Exception\ValidatorException;

class GreaterThanZero extends \Magento\Framework\App\Config\Value
{
    /**
     * Validate field value before save.
     *
     * @return void
     * @throws ValidatorException
     */
    public function beforeSave()
    {
        $label = $this->getData('field_config/label');

        if ($this->getValue() == '') {
            throw new ValidatorException(__($label . ' is required.'));
        } elseif (!is_numeric($this->getValue())) {
            throw new ValidatorException(__($label . ' is not a number.'));
        } elseif ($this->getValue() <= 0) {
            throw new ValidatorException(__($label . ' must greater than 0.'));
        }

        $this->setValue((int) $this->getValue());
        parent::beforeSave();
    }
}
