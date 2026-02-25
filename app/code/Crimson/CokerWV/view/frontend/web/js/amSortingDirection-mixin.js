define([
    'jquery'
], function ($) {
    'use strict';

    return function (originalWidget) {
        $.widget('mage.amSortingDirection', originalWidget, {
            observeSortOrder: function () {
                var self = this;
                self.element.on('change', function () {
                    var directionSwitcher = $(self.selectors.directionSwitcher);
                    directionSwitcher.fadeTo(0, 0);
                    directionSwitcher.css('pointer-events', 'none');
                });
            }
        });

        return $.mage.amSortingDirection;
    };
});
