define([
    'ko'
    ], function (ko) {
    'use strict';

    /**
     * Removes any functions that are defined on the given object, but not
     * from any objects in its prototype chain.
     *
     * @param Object obj
     */
    function removeOwnFunctionProperties(obj) {
        var propertyDescriptors = Object.getOwnPropertyDescriptors(obj);
        for (var key in propertyDescriptors) {
            var descriptor = propertyDescriptors[key];
            if (typeof descriptor.value === 'function') {
                delete obj[key];  // Does not delete the function from the object's prototype, only from the object itself.
            }
        }
    }

    return function (quote) {
        var billingAddressObservable = quote.billingAddress;

        quote.billingAddress = ko.computed({
            read: function () {
                return billingAddressObservable()
            },
            write: function (address) {
                if (address && address.street) {
                    removeOwnFunctionProperties(address.street)
                }

                billingAddressObservable(address)
            }
        });

        return quote;
    }
});
