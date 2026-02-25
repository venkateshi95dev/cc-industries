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
namespace Bss\AjaxCart\Model\Config\Source;

class Countdown implements \Magento\Framework\Option\ArrayInterface
{
    public const POPUP_COUNTDOWN_DISABLED = 0;
    public const POPUP_COUNTDOWN_CONTINUE_BTN = 1;
    public const POPUP_COUNTDOWN_VIEW_CART_BTN = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return  [
            ['value' => self::POPUP_COUNTDOWN_DISABLED, 'label' => __('No')],
            ['value' => self::POPUP_COUNTDOWN_CONTINUE_BTN, 'label' => __('Continue button')],
            ['value' => self::POPUP_COUNTDOWN_VIEW_CART_BTN, 'label' => __('View Cart button')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::POPUP_COUNTDOWN_DISABLED => __('No'),
            self::POPUP_COUNTDOWN_CONTINUE_BTN => __('Continue button'),
            self::POPUP_COUNTDOWN_VIEW_CART_BTN => __('View Cart button')
        ];
    }
}
