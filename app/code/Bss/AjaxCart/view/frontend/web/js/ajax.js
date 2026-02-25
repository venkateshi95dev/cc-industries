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
    'jquery',
    'Magento_Customer/js/customer-data',
    'bssMagnific',
    'mage/translate'
], function ($, customerData) {
    'use strict';

    $.widget('bss.ajaxcart', {
        progressStarted: false,
        iframeObject: null,
        isAddedFromWishlist: false,

        options: {
            addUrl: '',
            quickview: false,
            isProductView: false,
            isSuggestPopup: false,
            quickViewUrl: '',
            addToCartSelector: '.action.tocart',
            content: '',
            isFirst: false
        },

        productIdInputName: ["product", "product-id", "data-product-id", "data-product"],

        /**
         * @private
         * @inheritDoc
         */
        _create: function () {
            this.isAddedFromWishlist = this._isAddedFromWishlist();
            this._initAjaxCart();
            this._ajaxFlyToCart();
            window.ajaxCart = this;
        },

        /**
         * Ajax fly to cart
         * @private
         */
        _ajaxFlyToCart: function () {
            var enable = this.options.flyToCart.enable,
                speed = this.options.flyToCart.speed * 1000,
                transparent = this.options.flyToCart.transparent;
            $(this.options.addToCartSelector).on('click', function () {
                var cart = $('.minicart-wrapper');
                var imgtodrag = $(this).closest('.product-item').find('img');
                if (!imgtodrag.length > 0) {
                    $(this).closest('.column').find('.fotorama__stage__frame').each(function () {
                        if ($(this).data('active') === true) {
                            imgtodrag = $(this);
                        }
                    });

                }
            });

        },

        /**
         * Init ajax cart
         * @private
         */
        _initAjaxCart: function () {
            var options = this.options;
            var speed = this.options.flyToCart.speed * 1000;
            var self = this;
            $(options.addToCartSelector).off('click');
            self.element.on('click', options.addToCartSelector, function (e) {
                let id;
                let oldAction;
                e.preventDefault();
                var form = $(this).parents('form').get(0);
                var data = new FormData(form);

                var _btnAddTo = this;
                var isOpenedFrame = self._disOpenedPopupAddCart();

                if (form) {
                    var isValid = true;
                    if (options.isProductView) {
                        try {
                            isValid = $(form).valid();
                        } catch (err) {
                            isValid = true;
                        }
                    }

                    if (isValid) {
                        if (typeof $(this).attr('data-post') !== "undefined") {
                            dataPost = $.parseJSON($(this).attr('data-post'));
                        } else {
                            dataPost = null
                        }
                        oldAction = $(form).attr('action');
                        id = self._findId(this, oldAction, form);

                        $(this).closest('li').data('product-item-id', id);
                        if ($.isNumeric(id)) {
                            if (self.isAddedFromWishlist) {
                                var qty = self._findQty(this);
                                data.append('qty', qty);
                                data.append('cmsName', self.options.fullActionName);
                            }

                            data.append('id', id);
                            if (dataPost) {
                                data.append('item', dataPost.data.item);
                            }

                            $(form).find('input, select').each(function () {
                                if ($(this).attr('type') === 'file') {
                                    data.append($(this).attr('name'), $(this)[0].files[0]);
                                }
                            });
                            var url_post = $(form).attr('action');
                            if (url_post.indexOf('?options=') != -1) {
                                data.append('specifyOptions', '1');
                            }

                            if (options.quickview) {
                                window.parent.ajaxCart._sendAjax(options.addUrl, data, oldAction, _btnAddTo, isOpenedFrame);
                                return false;
                            }
                            setTimeout(function () {
                                self._sendAjax(options.addUrl, data, oldAction, _btnAddTo);
                            }, speed);

                            return false;
                        }
                        window.location.href = oldAction;
                    }
                } else {
                    var dataPost = $.parseJSON($(this).attr('data-post'));
                    if (dataPost) {
                        var formKey = $("input[name='form_key']").val();
                        oldAction = dataPost.action;
                        let item = $(this).closest('li.product-item');
                        id = $(item).find('[data-product-id]').attr('data-product-id');

                        if ($.isNumeric(id)) {
                            data.append('id', id);
                        }
                        data.append('item', dataPost.data.item);
                        data.append('product', dataPost.data.product);
                        data.append('form_key', formKey);
                        data.append('added_from_wishlist', dataPost.data.added_from_wishlist);
                        data.append('uenc', dataPost.data.uenc);
                        setTimeout(function () {
                            self._sendAjax(options.addUrl, data, oldAction, _btnAddTo);
                        }, speed);
                        return false;
                    } else {
                        id = self._findId(this);
                        if (id) {
                            e.stopImmediatePropagation();
                            self._requestQuickview(options.quickViewUrl + 'id/' + id);
                            return false;
                        }
                    }
                }
            });
        },

        /**
         * @param btn
         * @returns {*|jQuery|number}
         * @private
         */
        _findQty: function (btn) {
            var item = $(btn).closest('li.product-item');
            var qty = $(item).find(".input-text.qty").val();
            if ($.isNumeric(qty)) {
                return qty;
            }
            return 0;
        },

        /**
         * @param btn
         * @param oldAction
         * @param form
         * @returns {*|jQuery|boolean|*}
         * @private
         */
        _findId: function (btn, oldAction, form) {
            var self = this;
            var id = $(btn).attr('data-product-id');

            if ($.isNumeric(id)) {
                return id;
            }

            var item = $(btn).closest('li.product-item');
            id = $(item).find('[data-product-id]').attr('data-product-id');

            if ($.isNumeric(id)) {
                return id;
            }

            if (oldAction) {
                var formData = oldAction.split('/');
                for (var i = 0; i < formData.length; i++) {
                    if (self.productIdInputName.indexOf(formData[i]) >= 0) {
                        if ($.isNumeric(formData[i + 1])) {
                            id = formData[i + 1];
                        }
                    }
                }

                if ($.isNumeric(id)) {
                    return id;
                }
            }

            if (form) {
                $(form).find('input').each(function () {
                    if (self.productIdInputName.indexOf($(this).attr('name')) >= 0) {
                        if ($.isNumeric($(this).val())) {
                            id = $(this).val();
                        }
                    }
                });

                if ($.isNumeric(id)) {
                    return id;
                }
            }

            var priceBox = $(btn).closest('.price-box.price-final_price');
            id = $(priceBox).attr('data-product-id');

            if ($.isNumeric(id)) {
                return id;
            }

            return false;
        },
        /**
         * Send ajax to call action add to cart
         * @param addUrl
         * @param data
         * @param oldAction
         * @param _btnAddTo
         * @param isOpenedFrame
         * @private
         */
        _sendAjax: function (addUrl, data, oldAction, _btnAddTo = undefined, isOpenedFrame = true) {
            var options = this.options,
                self = this,
                enable = this.options.flyToCart.enable;
            $("body").trigger('processStart');
            this.progressStarted = true;

            var productId = data.get('id');
            // If added from wishlist using quickview
            // Merge additional params to data
            if (self.iframeObject && self.isAddedFromWishlist) {
                data.set('added_from_wishlist', 1);
            }

            $.ajax({
                type: 'post',
                url: addUrl,
                data: data,
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.popup) {
                        $('#bss_ajaxcart_popup').html(data.popup);
                        if (!window.isFirst) {
                            window.isFirst = true
                        } else {
                            self.options.content = data.popup;
                        }
                        if (_btnAddTo !== undefined) {
                            self._flyToCart($(_btnAddTo), options, isOpenedFrame);
                        }
                        // If customer is in wishlist, remove item from sidebar wishlist and main wishlist
                        if (undefined !== _btnAddTo && self.isAddedFromWishlist) {
                            if (undefined !== data.wishlist_item) {
                                var itemSelector = '#item_' + data.wishlist_item;
                                $(itemSelector).remove();
                            }
                            customerData.reload(['wishlist']);
                            if (customerData.get('wishlist')().items === undefined ||
                                !customerData.get('wishlist')().items.length ||
                                (customerData.get('wishlist')().items !== undefined &&
                                    customerData.get('wishlist')().items.length == 1)) {
                                var emptyMessage = $.mage.__('You have no items in your wish list.');
                                var emptyWishlistElem = '<div class="message info empty"><span>' + emptyMessage + '</span></div>';
                                $('#wishlist-view-form').empty().html(emptyWishlistElem);
                                $('.wishlist-toolbar').remove();
                            }

                            $('#wishlist-view-form').trigger('contentUpdate');
                            $('.block block-wishlist').trigger('contentUpdate');
                        }
                    } else if (data.error && data.view) {
                        if (!options.quickview && !options.isProductView) {
                            // This case show a popup which view product which has options required,
                            // configurable product (if did not choose any option) or same one
                            // Popup was showed and ask we pick an required option to add to car.
                            // So we should so some logic here
                            if (self.iframeObject && data.message) {
                                var tmpMessages = '<div role="alert" class="messages">\n' +
                                    '        <div class="message-notice notice message" data-ui-id="message-notice">\n' +
                                    '            <div>' + data.message + '</div>\n' +
                                    '        </div>\n' +
                                    '    </div>';
                                var iframeContext = $('.' + self.iframeObject.st.mainClass).find('iframe').contents();
                                iframeContext.find('.page.messages').html(tmpMessages);
                                iframeContext.find('html,body').animate({
                                    scrollTop: 0
                                }, 200);
                            } else {
                                self._requestQuickview(data.url, options.animationPopup);
                            }
                        }
                    }
                    if (self.progressStarted) {
                        $("body").trigger('processStop');
                    }
                },
                /** @inheritdoc */
                error: function () {
                    window.location.href = oldAction;
                },
                /** @inheritdoc */
                complete: function () {
                    customerData.reload(['wishlist']);
                    if (self.progressStarted) {
                        $("body").trigger('processStop');
                    }
                }
            });
        },
        /**
         * If product has required options
         * Open the iframe allow customer add to cart
         * @param url
         * @param animation
         * @private
         */
        _requestQuickview: function (url, animation) {
            var self = this;
            $.magnificPopup.open(
                {
                    items: {
                        src: url,
                        type: 'iframe'
                    },
                    removalDelay: 500, //delay removal by X to allow out-animation
                    callbacks: {
                        beforeOpen: function () {
                            self.iframeObject = this;
                            this.st.iframe.markup = this.st.iframe.markup.replace('mfp-iframe-scaler', 'mfp-iframe-scaler mfp-with-anim');
                            this.st.mainClass = animation;
                        },
                        beforeClose: function () {
                            clearInterval(window.count);
                            self.iframeObject = null;
                        },
                        close: function () {
                            // Update cart items if we are in checkout cart page
                            if (self.options.fullActionName === 'checkout_cart_index') {
                                $('.form.form-cart').submit();
                            }
                            window.isFirst = false;
                        }
                    },
                    midClick: true
                }
            );
        },
        /**
         * Popup show when done on add to cart
         * @param animationPopup
         * @private
         */
        _showPopup: function (animationPopup) {
            var isMobile = false,
                self = this,
                selector = '';
            if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4))) {
                isMobile = true;
            }
            $("#bss_ajaxcart_popup").css({"display": "block"});
            if (self.options.content !== '') {
                selector = '<div id="bss_ajaxcart_popup" class="white-popup mfp-with-anim" style="display: block;">' + self.options.content + '</div>';
            } else {
                selector = '#bss_ajaxcart_popup';
            }
            $.magnificPopup.open(
                {
                    items: {
                        src: selector
                    },
                    removalDelay: 500, //delay removal by X to allow out-animation
                    callbacks: {
                        beforeOpen: function () {
                            self.iframeObject = this;
                            this.st.mainClass = animationPopup;
                        },
                        beforeClose: function () {
                            self.iframeObject = null;
                            clearInterval(window.count);
                        },
                        close: function () {
                            if (self.options.fullActionName === 'checkout_cart_index') {
                                $('.form.form-cart').submit();
                            }
                        }
                    },
                    midClick: true // allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source.
                }
            );
            $("#bss_ajaxcart_popup").trigger('contentUpdated');
        },

        /**
         * Fly to cart effect
         * @param $btnAdd
         * @param options
         * @param isOpenedFrame
         */
        _flyToCart: function ($btnAdd, options, isOpenedFrame) {
            var enable = this.options.flyToCart.enable,
                speed = this.options.flyToCart.speed * 1000,
                transparent = this.options.flyToCart.transparent,
                self = this;
            var cart = $('.minicart-wrapper');
            var imgtodrag = $btnAdd.closest('.product-item').find('img');
            if (this.options.fullActionName === 'catalog_product_compare_index') {
                imgtodrag = $btnAdd.closest('.cell.product.info').find('img');
            }
            if (!imgtodrag.length > 0) {
                $btnAdd.closest('.column').find('.fotorama__stage__frame').each(function (idx, elem) {
                    if ($(elem).data('active') === true) {
                        imgtodrag = $(elem);
                    }
                });

            }

            var contentContainerTop = 0;
            var contentContainerLeft = 0;
            // If iframe is opened and iframe object is not null,
            // Close iframe before option popup cart
            if (self.iframeObject) {
                contentContainerTop = $(self.iframeObject.contentContainer).offset().top;
                contentContainerLeft = $(self.iframeObject.contentContainer).offset().left;
                // $('button.mfp-close').click();
                // self.iframeObject.close();
            }

            // Do fly effect to cart
            if (enable === '1' && imgtodrag.length > 0) {
                var imgclone = imgtodrag.clone().offset({
                    top: imgtodrag.offset().top + contentContainerTop,
                    left: imgtodrag.offset().left + contentContainerLeft
                }).css({
                    'opacity': transparent,
                    'position': 'absolute',
                    'height': imgtodrag.height(),
                    'width': imgtodrag.width(),
                    'z-index': '10000',
                    'bottom': 'auto',
                    'right': 'auto',
                })
                    .appendTo($('body'))
                    .animate({
                        'top': cart.offset().top,
                        'left': cart.offset().left,
                        'width': 50,
                        'height': 50
                    }, speed, 'easeOutExpo', function () {
                        self._addPopup(options, isOpenedFrame);
                    });
                imgclone.animate({
                    'width': 0,
                    'height': 0
                });
            } else {
                self._addPopup(options, isOpenedFrame);
            }
        },

        /**
         * Use fly effect or not
         * @param options
         * @param isOpenedFrame
         */
        _addPopup: function (options, isOpenedFrame) {
            var speed = this.options.flyToCart.speed * 1000;
            var enabledFly = this.options.flyToCart.enable;
            var self = this;

            if (enabledFly === '1') {
                var doShowPopup = setInterval(function () {
                    self._showPopup(options.animationPopup);
                    clearInterval(doShowPopup);
                }, speed);
            } else {
                self._showPopup(options.animationPopup);
            }
        },

        /**
         * Whatever we are in popup product view
         * @returns {boolean}
         */
        _disOpenedPopupAddCart: function () {
            return !window.fotoramaVersion && !window.frameElement;
        },

        /**
         * If add to cart from wishlist then return true
         * @returns {boolean}
         * @private
         */
        _isAddedFromWishlist: function () {
            return this.options.fullActionName == 'wishlist_index_index';
        }
    });

    return $.bss.ajaxcart;
});
