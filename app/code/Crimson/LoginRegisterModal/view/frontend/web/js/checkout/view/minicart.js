define(function () {
    'use strict';
    return function (target) { // target == Result that 'Magento_Checkout/js/view/minicart' returns.
        // modify target
        var initSidebar = target.initSidebar;
        target.initSidebar = function() {
            var result = initSidebar.apply(this);
            var minicart = $('[data-block="minicart"]');
            minicart.on('click', 'a[data-action="modal-signup"]', function (event) {
                    event.stopPropagation();
                    event.stopDefault();
                    $('#popup-modal').modal("openModal");
                    return false;
            });
            return result;
        };
        return target;
    };
});
