define([
    'Magento_InventoryInStorePickupFrontend/js/view/store-selector',
    'mage/translate',
], function (Component, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            storeSelectorPopupTemplate:
                'Crimson_InStorePickup/store-selector/popup',
        },

        getErrorMessage: function () {
            return window.checkoutConfig.not_all_match_no_locations_found_error_msg ?? "We were unable to find nearby locations for provided search query.";
        }
    });
});
