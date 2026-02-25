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
var config = {
	map:    {
        '*': {
            bssAjaxCart: 'Bss_AjaxCart/js/ajax',
            bssOwl: 'Bss_AjaxCart/js/owl.carousel',
            bssPopup: 'Bss_AjaxCart/js/popup',
            bssGoto: 'Bss_AjaxCart/js/goto',
            bssProductSuggest: 'Bss_AjaxCart/js/suggest',
            bssMagnific: 'Bss_AjaxCart/js/jquery.magnific-popup'
        }
    },
    config: {
        mixins: {
            'Magento_Catalog/js/product/view/provider': {
                'Bss_AjaxCart/js/product/view/provider-mixin': true
            }
        }
    }
};
