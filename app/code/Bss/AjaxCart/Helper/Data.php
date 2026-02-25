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
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_AjaxCart
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AjaxCart\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 */
class Data extends AbstractHelper
{
    /**
     * Is ajax cart enabled.
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'ajaxcart/general/active',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is ajax cart enabled in product view.
     *
     * @return bool
     */
    public function isEnabledProductView($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'ajaxcart/general/active_product_view',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get add to cart button selector.
     *
     * @return string
     */
    public function getAddToCartSelector($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/general/selector',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is show product image in success popup.
     *
     * @return bool
     */
    public function isShowProductImage($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'ajaxcart/success_popup/product_image',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get product image size in success popup.
     *
     * @return string
     */
    public function getImageSize($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/success_popup/product_image_size',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is show added product price in success popup.
     *
     * @return bool
     */
    public function isShowProductPrice($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'ajaxcart/success_popup/product_price',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is show continue button in success popup.
     *
     * @return bool
     */
    public function isShowContinueBtn($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'ajaxcart/success_popup/continue_button',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get countdown active for which button.
     *
     * @return string
     */
    public function getCountDownActive($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/success_popup/active_countdown',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get countdown time in second.
     *
     * @return string
     */
    public function getCountDownTime($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/success_popup/countdown_time',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is show cart info in success popup.
     *
     * @return bool
     */
    public function isShowCartInfo($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'ajaxcart/success_popup/mini_cart',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is show checkout link in success popup.
     *
     * @return bool
     */
    public function isShowCheckoutLink($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'ajaxcart/success_popup/mini_checkout',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is show suggested products.
     *
     * @return bool
     */
    public function isShowSuggestBlock($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'ajaxcart/success_popup/suggest_product',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get suggest title.
     *
     * @return string
     */
    public function getSuggestTitle($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/success_popup/suggest_title',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get suggested source.
     *
     * @return int
     */
    public function getSuggestSource($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/success_popup/suggest_source',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get suggested limit.
     *
     * @return int
     */
    public function getSuggestLimit($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/success_popup/suggest_limit',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get button text color.
     *
     * @return mixed|string
     */
    public function getBtnTextColor($storeId = null)
    {
        $color = $this->scopeConfig->getValue(
            'ajaxcart/success_popup_design/button_text_color',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return ($color == '') ? 'ffffff' : $color;
    }

    /**
     * Get continue button text.
     *
     * @return string
     */
    public function getBtnContinueText($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/success_popup_design/continue_text',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get continue button background.
     *
     * @return mixed|string
     */
    public function getBtnContinueBackground($storeId = null)
    {
        $backGround = $this->scopeConfig->getValue(
            'ajaxcart/success_popup_design/continue',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return ($backGround == '') ? '1979c3' : $backGround;
    }

    /**
     * Get continue button color when hover.
     *
     * @return mixed|string
     */
    public function getBtnContinueHover($storeId = null)
    {
        $hover = $this->scopeConfig->getValue(
            'ajaxcart/success_popup_design/continue_hover',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return ($hover == '') ? '006bb4' : $hover;
    }

    /**
     * Get view cart button text.
     *
     * @return string
     */
    public function getBtnViewcartText($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/success_popup_design/viewcart_text',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get view cart button background.
     *
     * @return mixed|string
     */
    public function getBtnViewcartBackground($storeId = null)
    {
        $backGround = $this->scopeConfig->getValue(
            'ajaxcart/success_popup_design/viewcart',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return ($backGround == '') ? '1979c3' : $backGround;
    }

    /**
     * Get view cart button color when hover.
     *
     * @return mixed|string
     */
    public function getBtnViewcartHover($storeId = null)
    {
        $hover = $this->scopeConfig->getValue(
            'ajaxcart/success_popup_design/viewcart_hover',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return ($hover == '') ? '006bb4' : $hover;
    }

    /**
     * Is show go to product link in quick view.
     *
     * @return bool
     */
    public function isShowQuickviewGotoLink($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'ajaxcart/quickview_popup/go_to_product',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is show additional data in quick view.
     *
     * @return bool
     */
    public function isShowQuickviewAddData($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'ajaxcart/quickview_popup/additional_data',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get display product prices type in catalog.
     *
     * @return string
     */
    public function getProductTaxDisplayType($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'tax/display/type',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get cart subtotal display price.
     *
     * @return string
     */
    public function getSubtotalDisplayType($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'tax/cart_display/subtotal',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get status fly to cart
     *
     * @return mixed
     */
    public function getEnableFlyToCart($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/fly_to_cart/enable',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get fly to cart speed
     *
     * @return mixed
     */
    public function getFlyingSpeed($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/fly_to_cart/cart_flying_animation_speed',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get transparent
     *
     * @return mixed
     */
    public function getTransparent($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'ajaxcart/fly_to_cart/transparent_image_while_flying',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Popup Animation
     *
     * @return string
     */
    public function getConfigAnimation($storeId = null)
    {
        $getConfig = $this->scopeConfig->getValue(
            'ajaxcart/general/popup_animation',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        switch ($getConfig) {
            case 1:
                $animation = 'mfp-zoom-out';
                break;
            case 2:
                $animation = 'mfp-3d-unfold';
                break;
            case 3:
                $animation = 'mfp-move-from-top';
                break;
            case 4:
                $animation = 'mfp-move-horizontal';
                break;
            case 5:
                $animation = 'mfp-zoom-in';
                break;
            default:
                $animation = 'none';
        }
        return $animation;
    }
}
