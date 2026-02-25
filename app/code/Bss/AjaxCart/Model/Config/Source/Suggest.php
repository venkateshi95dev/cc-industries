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

class Suggest implements \Magento\Framework\Option\ArrayInterface
{
    public const SUGGEST_SOURCE_RELATED = 0;
    public const SUGGEST_SOURCE_UPSELL = 1;
    public const SUGGEST_SOURCE_XSELL = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return  [
            ['value' => self::SUGGEST_SOURCE_RELATED, 'label' => __('Related Products')],
            ['value' => self::SUGGEST_SOURCE_UPSELL, 'label' => __('Up-Sell Products')],
            ['value' => self::SUGGEST_SOURCE_XSELL, 'label' => __('Cross-Sell Products')]
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
            self::SUGGEST_SOURCE_RELATED => __('Related Products'),
            self::SUGGEST_SOURCE_UPSELL => __('Up-Sell Products'),
            self::SUGGEST_SOURCE_XSELL => __('Cross-Sell Products')
        ];
    }
}
