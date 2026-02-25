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
 * @category  BSS
 * @package   Bss_AjaxCart
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
define(
    [
    'jquery',
    'bssOwl'
    ], function ($) {
        'use strict';

        $.widget(
            'bss.suggest', {
                options: {
                    itemsNumber: 0
                },

                _create: function () {
                    var options = this.options;
                    var items0 = (1 > options.itemsNumber) ? options.itemsNumber : 1;
                    var items600 = (2 > options.itemsNumber) ? options.itemsNumber : 2;
                    var items1000 = (3 > options.itemsNumber) ? options.itemsNumber : 3;

                    var owl = $('.ajax-cart-owl-carousel').owlCarousel(
                        {
                            nav: true,
                            margin: 25,
                            stagePadding: 25,
                            autoplay: true,
                            autoplayHoverPause: true,
                            loop: true,
                            onInitialized: this._hideLoading,
                            onChange: function (e) {
                                var options = window.parent.ajaxCart.options;
                                var self = window.parent.ajaxCart;
                                var addToCartSelector = options.addToCartSelector;
                                $(e.currentTarget).find(addToCartSelector).off('click');
                                $(e.currentTarget).find(addToCartSelector).click(
                                    function (e) {
                                        e.preventDefault();
                                        clearInterval(window.count);
                                        var form = $(this).parents('form').get(0);
                                        var data = new FormData();
                                        if (form) {
                                            var isValid = true;
                                            if (options.isProductView) {
                                                try {
                                                    isValid = $(form).valid();
                                                } catch(err) {
                                                    isValid = true;
                                                }
                                            }

                                            if (isValid) {
                                                          var oldAction = $(form).attr('action');
                                                          var id = self._findId(this, oldAction, form);

                                                if ($.isNumeric(id)) {
                                                    data.append('id', id);
                                                    $(form).find('input, select').each(
                                                        function () {
                                                            if ($(this).attr('type') === 'file') {
                                                                  data.append($(this).attr('name'), $(this)[0].files[0]);
                                                            } else {
                                                                data.append($(this).attr('name'), $(this).val());
                                                            }
                                                        }
                                                    );

                                                    self._sendAjax(options.addUrl, data, oldAction);
                                                    return false;
                                                }

                                                  window.location.href = oldAction;
                                            }
                                        } else {
                                            if (typeof $(this).attr('data-post') !== 'undefined'){
                                                var dataPost = $.parseJSON($(this).attr('data-post'));
                                            } else {
                                                dataPost = null;
                                            }
                                            if (dataPost) {
                                                 var formKey = $("input[name='form_key']").val();
                                                 var oldAction = dataPost.action;
                                                 data.append('id', dataPost.data.product);
                                                 data.append('product', dataPost.data.product);
                                                 data.append('form_key', formKey);
                                                 data.append('uenc', dataPost.data.uenc);
                                                 self._sendAjax(options.addUrl, data, oldAction);
                                                 return false;
                                            } else {
                                                var id = self._findId(this);
                                                if (id) {
                                                    e.stopImmediatePropagation();
                                                    self._requestQuickview(options.quickViewUrl + 'id/' + id);
                                                    return false;
                                                }
                                            }
                                        }
                                    }
                                );

                            },
                            responsive: {
                                0: {
                                    items: items0
                                },
                                600: {
                                    items: items600
                                },
                                1000: {
                                    items: items1000
                                }
                            }
                        }
                    );
                    owl.on(
                        'initialized.owl.carousel', function (event) {
                            $("#init-ajaxCart").trigger('contentUpdated');
                        }
                    );

                    var heightArr = [];
                    $("#ajax-suggest .product.details.product-item-details").each(
                        function () {
                            heightArr.push($(this).height());
                        }
                    );

                    var maxheight = Math.max.apply(Math,heightArr);
                    $("#ajax-suggest .product.details.product-item-details").css("min-height", maxheight);
                },

                _hideLoading: function () {
                    $('.ajax-owl-loading').hide();
                    $(window).resize();
                }
            }
        );

        return $.bss.suggest;
    }
);
