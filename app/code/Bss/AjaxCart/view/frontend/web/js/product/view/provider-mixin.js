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
define([
    'underscore',
    'uiElement',
    'Magento_Catalog/js/product/storage/storage-service'
], function (_, Element, storage) {
    'use strict';
    var mixin = {
        idsStorageHandler: function (idsStorage) {
            this.idsStorage = idsStorage;
            if (window.checkout) {
                this.idsStorage.add(this.getIdentifiers());
            }
        }
    };
    return function (provider) {
        return provider.extend(mixin);
    }
});
