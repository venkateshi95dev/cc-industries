/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global $H */
/*global $$ */
/*global jQuery */
/*@api*/
define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'prototype',
    'Silk_ProductImage/js/tabs',
    'Silk_ProductImage/js/add_products'
], function ($, mageTemplate) {
    'use strict';

    $.widget('mage.visualMerchandiserProductImage', {
        options: {
            'gridSelector': '[data-grid-id=img_product_products]',
            'tileSelector': '[data-role=img_product_merchandiser]',
            'tabsSelector': '[data-role=merchandiser-tabs]',
            'addProductsContainer': '[data-role=img_product_add_product_content]',
            'addProductsUrl': null,
            'savePositionsUrl': null,
            'getPositionsUrl': null,
            'currentDocumentId': null,
            'positionCacheKeyName': null,
            'positionCacheKey': null
        },
        sourcePosition: null,
        sourceIndex: null,
        currentView: null,

        /**
         * @private
         */
        _create: function () {
            this.gridView = $(this.options.gridSelector);
            this.tileView = $(this.options.tileSelector);

            this.element.prepend(
                $('<input type="hidden" />')
                    .attr('id', this.options.positionCacheKeyName)
                    .attr('name', this.options.positionCacheKeyName)
                    .attr('data-form-part', this.options.formName)
                    .val(this.options.positionCacheKey)
            );

            this.setupGridView();
            this.initGridEventHandlers();

            this.setupTileView();
            this.initTileEventHandlers();

            $(this.options.addProductsContainer).visualMerchandiserProductImageAddProducts({
                dialogUrl: this.options.addProductsUrl,
                dialogButton: '[data-ui-id=productimage-add-product-button]',
                dialogSave: $.proxy(this._dialogSave, this)
            });


            // Only updates when Ajax loads another page
            this.gridView.on(
                'contentUpdated', function () {
                    this.setupGridView();
                }.bind(this)
            );
            this.tileView.on(
                'contentUpdated', function () {
                    this.setupTileView();
                }.bind(this)
            );

            // Tabs setup
            $(this.options.tabsSelector).visualMerchandiserProductImageTabs({
                viewDidChange: $.proxy(this._setView, this)
            });
        },

        /**
         * @returns void
         */
        initGridEventHandlers: function () {
            this.gridView.on(
                'focus',
                'input[name=position]',
                this.positionFieldFocused.bindAsEventListener(this)
            ).on(
                'change',
                'input[name=position]',
                this.positionFieldChanged.bindAsEventListener(this)
            ).keypress(function (event) {
                if (event.which === Event.KEY_RETURN) {
                    $(event.target).trigger('blur');
                }
            });

            this.gridView.on('click', 'a[name=unassign]', function (e) {
                this.removeProduct(e);
            }.bind(this));
            this.gridView.on('click', '.move-top', this.moveToTop.bindAsEventListener(this));
            this.gridView.on('click', '.move-bottom', this.moveToBottom.bindAsEventListener(this));

            // Talks to paging/ajax component directly, converts events into calls
            this.gridView.on('click', '.action-next:not(.disabled)', function (e) {
                $(e.target).attr('data-value', this.getPage(this.gridView) + 1);
                $(this.options.tileSelector).visualMerchandiserProductImageTilePager('setPage', e);
            }.bind(this));
            this.gridView.on('click', '.action-previous:not(.disabled)', function (e) {
                $(e.target).attr('data-value', this.getPage(this.gridView) - 1);
                $(this.options.tileSelector).visualMerchandiserProductImageTilePager('setPage', e);
            }.bind(this));
            this.gridView.on('keypress', 'input[name=page]:not(.disabled)', function (e) {
                $(this.options.tileSelector).visualMerchandiserProductImageTilePager('inputPage', e);
            }.bind(this));
            this.gridView.on('change', 'select[name=limit]:not(.disabled)', function (e) {
                $(this.options.tileSelector).visualMerchandiserProductImageTilePager('loadByElement', e);
            }.bind(this));

            this.gridView.on('gridajaxsettings', function (event, settings) {
                settings.data[this.options.positionCacheKeyName] = this.getCacheKey();
            }.bind(this));
        },

        /**
         * @returns void
         */
        initTileEventHandlers: function () {
            this.tileView.on(
                'focus',
                'input[name=position]',
                this.positionFieldFocused.bindAsEventListener(this)
            ).on(
                'change',
                'input[name=position]',
                this.positionFieldChanged.bindAsEventListener(this)
            ).keypress(function (event) {
                if (event.which === Event.KEY_RETURN) {
                    $(event.target).trigger('blur');
                }
            });

            this.tileView.on('click', '.remove-product', this.removeProduct.bindAsEventListener(this));
            this.tileView.on('click', '.icon-gripper', function () {
                return false;
            });
            this.tileView.on('click', '.move-top', this.moveToTop.bindAsEventListener(this));
            this.tileView.on('click', '.move-bottom', this.moveToBottom.bindAsEventListener(this));

            // Talks to paging/ajax component directly, converts events into calls
            this.tileView.on('click', '.action-next:not(.disabled)', function () {
                this._getGridViewComponent().setPage(this.getPage(this.tileView) + 1);
            }.bind(this));
            this.tileView.on('click', '.action-previous:not(.disabled)', function () {
                this._getGridViewComponent().setPage(this.getPage(this.tileView) - 1);
            }.bind(this));
            this.tileView.on('keypress', 'input[name=page]:not(.disabled)', function (e) {
                this._getGridViewComponent().inputPage(e);
            }.bind(this));
            this.tileView.on('change', 'select[name=limit]:not(.disabled)', function (e) {
                this._getGridViewComponent().loadByElement(e.target);
            }.bind(this));

            this.tileView.on('gridajaxsettings', function (event, settings) {
                settings.data[this.options.positionCacheKeyName] = this.getCacheKey();
            }.bind(this));
        },

        /**
         * @param {Event} event
         * @param {Array} selected
         * @param {mage.visualMerchandiserProductImageAddProducts} modal
         * @private
         */
        _dialogSave: function (event, selected, modal) {
            var selectedSortData,
                sorted,
                finalData;

            selectedSortData = this.sortSelectedProducts(selected);
            sorted = this.getSortedPositionsFromData(selectedSortData);

            finalData = $H();
            sorted.each(function (item, idx) {
                finalData.set(item.key, String(idx));
            });

            $('#vm_document_products').val(Object.toJSON(finalData));

            this.savePositionCache($.proxy(this._modalDidSave, this, modal));
        },

        /**
         * @param {mage.visualMerchandiserProductImageAddProducts} modal
         * @private
         */
        _modalDidSave: function (modal) {
            this.reloadViews();
            modal.closeDialog();
        },

        /**
         * @returns void
         */
        reloadViews: function () {
            $.when(this._getGridViewComponent().reload()).then(function () {
                this.getNewPositions();
            }.bind(this));
            $(this.options.tileSelector).visualMerchandiserProductImageTilePager('reload');
        },

        /**
         * @returns {*}
         * @private
         */
        _getGridViewComponent: function () {
            // XXX: refactor
            var k = $(this.options.gridSelector).attr('id') + 'JsObject';

            return window[k];
        },

        /**
         * @param {Event} event
         * @param {View} view
         * @private
         */
        _setView: function (event, view) {
            this.currentView = view;
        },

        /**
         * @returns void
         */
        setupTileView: function () {
            var sortableParent = this.tileView.find('#img_product_merchandiser_list'),
                data;

            sortableParent.sortable({
                distance: 8,
                tolerance: 'pointer',
                cancel: 'input, button',
                forcePlaceholderSize: true,
                update: this.sortableDidUpdate.bind(this),
                start: this.sortableStartUpdate.bind(this),
                stop: $.proxy(function () {
                    if (this.isAutosortEnabled()) {
                        sortableParent.sortable('cancel');
                    }
                }, this)
            });
            data = {};
            sortableParent.data('sortable').items.each(function (instance) {
                var key = $(instance.item).find('input[name=entity_id]').val();

                data[key] = $(instance.item);
            });

            sortableParent.data('item_id_mapper', data);
        },

        /**
         * @returns void
         */
        setupGridView: function () {
            var sortableParent = this.gridView.find('table > tbody'),
                data;

            sortableParent.sortable({
                distance: 8,
                tolerance: 'pointer',
                cancel: 'input, button',
                forcePlaceholderSize: true,
                axis: 'y',
                update: this.sortableDidUpdate.bind(this),
                start: this.sortableStartUpdate.bind(this),
                stop: $.proxy(function () {
                    if (this.isAutosortEnabled()) {
                        sortableParent.sortable('cancel');
                    }
                }, this)
            });

            data = {};
            sortableParent.data('sortable').items.each(function (instance) {
                var key = $(instance.item).find('input[name=entity_id]').val();

                data[key] = $(instance.item);

                if ($(instance.item).attr('title') === '#') {
                    $(instance.item).removeAttr('title');
                }
            });
            sortableParent.data('item_id_mapper', data);
        },

        /**
         * @param {Array} selectedIds
         * @returns {Array}
         */
        sortSelectedProducts: function (selectedIds) {
            var sortData = this.getSortData(),
                selectedSortData = $H(),
                newSelectedIds,
                offset;

            newSelectedIds = selectedIds.filter(function (entityId) {
                return sortData.get(entityId) === undefined;
            });

            offset = newSelectedIds.length;
            $(selectedIds).each(function (idx, entityId) {
                var pos = 0;

                if (sortData.get(entityId) === undefined) {
                    pos = newSelectedIds.indexOf(entityId);
                } else {
                    pos = sortData.get(entityId) + offset;
                }
                selectedSortData.set(entityId, pos);
            });

            return selectedSortData;
        },

        /**
         * @param {Array} sortData
         * @returns {Array}
         */
        getSortedPositionsFromData: function (sortData) {
            // entity_id => pos
            var sortedArr = [];

            sortData.each(Array.prototype.push.bindAsEventListener(sortedArr));
            sortedArr.sort(this.sortArrayAsc.bind(this, sortData));

            return sortedArr;
        },

        /**
         * @param {String} view
         * @returns {Number}
         */
        getPage: function (view) {
            var parentView = $(view).parents('.merchandiser-tab');

            return parseInt(parentView.find('input[name=page]').val(), 10);
        },

        /**
         * @param {String} view
         * @returns {Number}
         */
        getPageSize: function (view) {
            var parentView = $(view).parents('.merchandiser-tab');

            return parseInt(parentView.find('select[name=limit]').val(), 10);
        },

        /**
         * @param {String} view
         * @returns {Number}
         */
        getStartIdx: function (view) {
            var perPage = this.getPageSize(view);

            return this.getPage(view) * perPage - perPage;
        },

        /**
         * @param {String} view
         * @returns {Number}
         */
        getFinalIndex: function (view) {
            return this.getPage(view) * this.getPageSize(view);
        },

        /**
         * @returns {Number}
         */
        getTotalIndex: function () {
            return parseInt($('#img_product_products-total-count').text(), 10) - 1;
        },

        /**
         * @returns {Boolean}
         */
        isAutosortEnabled: function () {
            return true;
        },

        /**
         * Used to re-populate text inputs and move items in non-active view
         * triggered by {sortable}
         *
         * @param {Event} event
         * @param {Object} ui
         */
        sortableDidUpdate: function (event, ui) {
            var to,
                from,
                otherViews;

            if (this.isAutosortEnabled()) {
                return;
            }

            to = ui.item.index();
            from = ui.item.data('originIndex');

            this.populateFromIdx(ui.item.parents('.ui-sortable').find('> *'));

            otherViews = this.element.find('.ui-sortable').not(ui.item.parent());
            otherViews.each(function (idx, view) {
                this.moveItemInView($(view), from, to);
            }.bind(this));

            this.sortDataObject({
                target: ui.item.parents('.ui-sortable')
            });
        },

        /**
         * Generic helper to move items in DOM and repopulate position indexes
         *
         * @param {Object} view
         * @param {Integer} from
         * @param {Integer} to
         */
        moveItemInView: function (view, from, to) {
            var items = view.find('>*');

            if (to > from) {
                $(items.get(from)).insertAfter($(items.get(to)));
            } else {
                $(items.get(from)).insertBefore($(items.get(to)));
            }
            items.removeClass('selected');
            this.populateFromIdx(items);
        },

        /**
         * Store the original index
         *
         * @param {Event} event
         * @param {Object} ui
         */
        sortableStartUpdate: function (event, ui) {
            ui.item.data('originIndex', ui.item.index());
        },

        /**
         * UI trigger for product remove
         *
         * @param {Event} event
         */
        removeProduct: function (event) {
            var row;

            event.preventDefault();
            row = $(event.currentTarget).parents('li,tr');

            this.removeRow(row);
        },

        /**
         * Remove product by refresing the grids
         *
         * @param {Object} row
         */
        removeRow: function (row) {
            var data = this.getSortData();

            data.unset(row.find('[name=entity_id]').val());
            $('#vm_document_products').val(Object.toJSON(data));
            this.savePositionCache(function () {
                this.reloadViews();
            }.bind(this));
        },

        /**
         * Triggered by clicking on move to top button
         *
         * @param {Event} event
         */
        moveToTop: function (event) {
            var input;

            event.preventDefault();

            if (this.isAutosortEnabled()) {
                return;
            }

            input = $(event.currentTarget).next('input[name=position]');
            this.moveToPosition(input, 0);
        },

        /**
         * Triggered by clicking on move to bottom button
         *
         * @param {Event} event
         */
        moveToBottom: function (event) {
            var input;

            event.preventDefault();

            if (this.isAutosortEnabled()) {
                return;
            }

            input = $(event.currentTarget).prev('input[name=position]');
            this.moveToPosition(input, this.getTotalIndex());
        },

        /**
         * @param {Object} input
         * @param {Integer} targetPosition
         */
        moveToPosition: function (input, targetPosition) {
            this.positionFieldFocused({
                'currentTarget': input
            });

            input.val(targetPosition);
            this.changePosition(input);
        },

        /**
         * Triggered by 'onchange' and keypress
         *
         * @param {Event} event
         */
        positionFieldChanged: function (event) {
            var input = $(event.currentTarget);

            if (input.val() !== parseInt(input.val(), 10).toString()) {
                input.val(this.sourcePosition);

                return;
            }
            this.changePosition(input);
        },

        /**
         * Do all the necessary calls to re-position an element
         *
         * @param {Object} input
         */
        changePosition: function (input) {
            var destinationPosition = parseInt(input.val(), 10),
                destinationIndex = destinationPosition - this.getStartIdx(input),
                data,
                sorted,
                result,
                movedItem;

            if (destinationPosition > this.getTotalIndex()) {
                input.val(this.getTotalIndex());
                this.changePosition(input);

                return;
            }

            // Moving within current page
            if (this.isValidMove(this.sourcePosition, destinationPosition)) {
                // Move on all views
                this.element.find('.ui-sortable').each(function (idx, item) {
                    this.moveItemInView($(item), this.sourceIndex, destinationIndex);
                }.bind(this));

                this.sortDataObject({
                    target: input.parents('.ui-sortable')
                });

                return;
            }

            // Moving off the current page
            if (
                this.isValidPosition(this.sourcePosition) &&
                destinationPosition >= 0 &&
                this.sourcePosition !== destinationPosition
            ) {
                data = this.getSortData();
                sorted = this.getSortedPositionsFromData(data);
                result = [];

                movedItem = sorted[this.sourcePosition];
                movedItem.value = String(destinationPosition);

                sorted.each(function (item, idx) {
                    if (idx !== this.sourcePosition && idx !== destinationPosition) {
                        result.push(item);
                    }

                    if (idx === destinationPosition) {
                        if (destinationPosition > this.sourcePosition) {
                            result.push(item);
                            result.push(movedItem);
                        } else {
                            result.push(movedItem);
                            result.push(item);
                        }
                    }
                }.bind(this));

                result.each(function (item, idx) {
                    data.set(item.key, String(idx));
                });

                $('#vm_document_products').val(Object.toJSON(data));

                this.savePositionCache(function () {
                    this.reloadViews();
                }.bind(this));

                return;
            }

        },

        /**
         * @param {Array} items
         */
        populateFromIdx: function (items) {
            var startIdx = this.getStartIdx(items);

            items.find('input[name=position][type=text]').each(function (idx, item) {
                $(item).val(startIdx + idx);
            });
        },

        /**
         * @param {Integer} src
         * @param {Integer} dst
         * @returns {Boolean}
         */
        isValidMove: function (src, dst) {
            return this.isValidPosition(src) && this.isValidPosition(dst) && src !== dst;
        },

        /**
         * @param {Integer} pos
         * @returns {Boolean}
         */
        isValidPosition: function (pos) {
            var view = this.currentView.find('>*:eq(0)'),
                maxPos = this.getFinalIndex(view),
                minPos = this.getStartIdx(view);

            return pos !== null && pos >= minPos && pos < maxPos;
        },

        /**
         * Stores position for later use by this.positionFieldChanged
         *
         * @param {Event} event
         */
        positionFieldFocused: function (event) {
            var idx = parseInt($(event.currentTarget).parents('tr,li').index(), 10),
                pos = idx + this.getStartIdx($(event.currentTarget));

            if (!this.isValidPosition(pos)) {
                this.sourcePosition = null;
                this.sourceIndex = null;
            } else {
                this.sourcePosition = pos;
                this.sourceIndex = idx;
            }
        },

        /**
         * @param {Object} sortData
         * @param {Object} a
         * @param {Object} b
         * @returns {Number}
         */
        sortArrayAsc: function (sortData, a, b) {
            var keyA = sortData.get(a.key),
                keyB = sortData.get(b.key),
                diff = parseFloat(a.value) - parseFloat(b.value);

            if (diff !== 0) {
                return diff;
            }

            if (keyA === undefined && keyB !== undefined) {
                return -1;
            }

            if (keyA !== undefined && keyB === undefined) {
                return 1;
            }

            return 0;
        },

        /**
         * @returns {Hash}
         */
        getSortData: function () {
            return $H(JSON.parse($('#vm_document_products').val()));
        },

        /**
         * Re-sort the actual positions array which will be sent to the server
         *
         * @param {Event} event
         */
        sortDataObject: function (event) {
            // Data format: {entity_id => sort index, ... }
            var data = this.getSortData(),
                startIdx = this.getStartIdx($(event.target));

            // Overwrite positions with items from UI
            $(event.target).find('> *').find('[name=entity_id]').each(function (idx, item) {
                data.set($(item).val(), startIdx);
                startIdx++;
            });

            $('#vm_document_products').val(Object.toJSON(data));

            this.savePositionCache();
        },

        /**
         * @returns {jQuery}
         */
        getCacheKey: function () {
            return $('#' + this.options.positionCacheKeyName).val();
        },

        /**
         * @param {Function} callback
         */
        savePositionCache: function (callback) {
            var data = {
                    'document_id': this.options.currentDocumentId,
                    'positions': $('#vm_document_products').val(),
                    'sort_order': $('select.sort_order').val()
                },
                loader = typeof callback !== 'undefined';

            data[this.options.positionCacheKeyName] = this.getCacheKey();

            $.ajax({
                type: 'POST',
                url: this.options.savePositionsUrl,
                data: data,
                context: $('body'),
                showLoader: loader
            }).success(function () {
                if (callback) {
                    callback();
                }
            });
        },

        /**
         * @param {Function} callback
         */
        getNewPositions: function (callback) {
            var data = {
                    'document_id': this.options.currentDocumentId
                },
                loader = typeof callback !== 'undefined';

            data[this.options.positionCacheKeyName] = this.getCacheKey();

            $.ajax({
                type: 'POST',
                url: this.options.getPositionsUrl,
                data: data,
                context: $('body'),
                showLoader: loader
            }).success(function (result) {
                $('#vm_document_products').val(result);
            });
        }
    });

    return $.mage.visualMerchandiserProductImage;
});
