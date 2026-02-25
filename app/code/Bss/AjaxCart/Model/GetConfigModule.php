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
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AjaxCart\Model;

/**
 * Class is using to get config in admin/configuration
 *
 */
class GetConfigModule implements \Bss\AjaxCart\Api\GetConfigModuleInterface
{
    /**
     * @var \Bss\AjaxCart\Helper\Data
     */
    protected $config;

    /**
     * Constructor
     *
     * @param \Bss\AjaxCart\Helper\Data $config
     */
    public function __construct(
        \Bss\AjaxCart\Helper\Data $config
    ) {
        $this->config = $config;
    }

    /**
     * Use get config in admin Configuration/Bss/AjaxCart
     *
     * @param int $storeId
     * @return array|string[]
     */
    public function getConfig(int $storeId)
    {
        $result["module_configs"] = [
            "general" => [
                "enable" => $this->config->isEnabled($storeId),
                "active_product_view" => $this->config->isEnabledProductView($storeId),
                "selector" => $this->config->getAddToCartSelector($storeId),
                "popup_animation" => $this->config->isShowProductImage($storeId)
            ],
            "success_popup_setting" => [
                "product_image" => $this->config->isShowProductImage($storeId),
                "product_image_size" => $this->config->getImageSize($storeId),
                "product_price" => $this->config->isShowProductPrice($storeId),
                "continue_button" => $this->config->isShowContinueBtn($storeId),
                "active_countdown" => $this->config->getCountDownActive($storeId),
                "countdown_time" => $this->config->getCountDownTime($storeId),
                "mini_cart" => $this->config->isShowCartInfo($storeId),
                "mini_checkout" => $this->config->isShowCheckoutLink($storeId),
                "suggest_product" => $this->config->isShowSuggestBlock($storeId),
                "suggest_title" => $this->config->getSuggestTitle($storeId),
                "suggest_source" => $this->config->getSuggestSource($storeId),
                "suggest_limit" => $this->config->getSuggestLimit($storeId)
            ],
            "success_popup_design" => [
                "button_text_color" => $this->config->getBtnTextColor($storeId),
                "continue_text" => $this->config->getBtnContinueText($storeId),
                "continue" => $this->config->getBtnContinueBackground($storeId),
                "continue_hover" => $this->config->getBtnContinueHover($storeId),
                "viewcart_text" => $this->config->getBtnViewcartText($storeId),
                "viewcart" => $this->config->getBtnViewcartBackground($storeId),
                "viewcart_hover" => $this->config->getBtnViewcartHover($storeId)
            ],
            "quickview_popup" => [
                "go_to_product" => $this->config->isShowQuickviewGotoLink($storeId),
                "additional_data" => $this->config->isShowQuickviewAddData($storeId)
            ],
            "fly_to_cart" => [
                "enable" => $this->config->getEnableFlyToCart(),
                "cart_flying_animation_speed" => $this->config->getFlyingSpeed($storeId),
                "transparent_image_while_flying" => $this->config->getTransparent($storeId)
            ]
        ];
        return $result;
    }
}
