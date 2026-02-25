/**
 * Century Business Solutions
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the centurybizsolutions.com license that is
 * available through the URL:  https://www.centurybizsolutions/License.txt
 *
 * DISCLAIMER
 *
 * Please do not edit or add to this file to upgrade this extension to newer
 * version in the future please contact to CENTURY BUSINESS SOLUTIONS.
 *
 * @category    Ebizcharge
 * @package     Ebizcharge_Ebizcharge
 * @copyright   Copyright (c) 2024 Century Business Solutions (https://www.centurybizsolutions.com/)
 * @license     https://www.centurybizsolutions.com/License.txt
 * @author      Century Business Solutions
 * @email       <support@centurybizsolutions.com>
 */

/* @api */

(function (factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'Magento_Payment/js/model/credit-card-validation/cvv-validator',
            'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
            'Magento_Payment/js/model/credit-card-validation/expiration-date-validator/expiration-year-validator',
            'Magento_Payment/js/model/credit-card-validation/expiration-date-validator/expiration-month-validator',
            'Magento_Payment/js/model/credit-card-validation/credit-card-data',
            'mage/translate'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($, cvvValidator, creditCardNumberValidator, yearValidator, monthValidator, creditCardData) {
    'use strict';

    /**
     * EBizValidator
     *
     * @type {{validateCreditCardDate: (function(*, *=, *=): boolean), selYear: null, selMonth: null}}
     */
    let EBizValidator = {
        selMonth: "",
        selYear: "",
        cardExpiry: null,
        currentMonth: null,
        currentYear: null,
        ebizChargeCode: "ebizcharge_ebizcharge",
        expiryMonths:[],
        expiryYears: [],

        /**
         * init function
         * @param $
         * @param cvvValidator
         * @param creditCardNumberValidator
         * @param yearValidator
         * @param monthValidator
         * @param creditCardData
         * @private
         */
        _init: function ($, cvvValidator, creditCardNumberValidator, yearValidator, monthValidator, creditCardData) {

            let currentDateTime = new Date();
            this.cardExpiry = $(".card-expiry");

            /**
             * get Selected Expiry
             */
            EBizValidator.getSelectedExpiry($);
            EBizValidator.getExpiryOnChange($);

            this.currentMonth = parseInt(currentDateTime.getUTCMonth() + 1);
            this.currentYear = parseInt(currentDateTime.getUTCFullYear());

            this.expiryMonths = [
                "expiry_month_new",
                "expiry_month_update",
            ];
            this.expiryYears = [
                "expiry_year_new",
                "expiry_year_update"
            ];

        },
        /**
         * get Expiry on Change
         * @param $
         */
        getExpiryOnChange: function ($) {
            EBizValidator.cardExpiry.on("change", function (evt) {
                let targetId = evt.target.id;
                let monthCounter = 0;

                for (monthCounter; monthCounter < EBizValidator.expiryMonths.length; monthCounter++) {
                    let expiryMonth = EBizValidator.ebizChargeCode+"_"+EBizValidator.expiryMonths[monthCounter];

                    if (targetId === expiryMonth) {
                        EBizValidator.selMonth = $(document.getElementById(expiryMonth)).val();
                    }
                    let expiryYear = EBizValidator.ebizChargeCode+"_"+EBizValidator.expiryYears[monthCounter];
                    if (targetId === expiryYear) {
                        EBizValidator.selYear = $(document.getElementById(expiryYear)).val();
                    }
                }

            });
        },
        /**
         * Get Selected Expiry
         * @param $
         */
        getSelectedExpiry: function ($) {
            let selectedMonthNew = $(document.getElementById(EBizValidator.ebizChargeCode+"_expiry_month_new"));
            let selectedMonthUpdate = $(document.getElementById(EBizValidator.ebizChargeCode+"_expiry_month_update"));
            let selectedYearNew = $(document.getElementById(EBizValidator.ebizChargeCode+"_expiry_year_new"));
            let selectedYearUpdate = $(document.getElementById(EBizValidator.ebizChargeCode+"_expiry_year_update"));

            if (!selectedMonthNew.prop('disabled')) {
                EBizValidator.selMonth = selectedMonthNew.val();
            }
            if (!selectedMonthUpdate.prop('disabled')) {
                EBizValidator.selMonth = selectedMonthUpdate.val();
            }

            if (!selectedYearNew.prop('disabled')) {
                EBizValidator.selYear = selectedYearNew.val();
            }
            if (!selectedYearUpdate.prop('disabled')) {
                EBizValidator.selYear = selectedYearUpdate.val();
            }
        },
        /**
         * Validate Credit Card Date
         * @param $
         * @param cMonth
         * @param cYear
         * @returns {boolean}
         */
        validateCreditCardExpiryDate: function ($, cMonth = null, cYear = null) {

            EBizValidator.getSelectedExpiry($);
            EBizValidator.getExpiryOnChange($);

            let selMonthNew = $(document.getElementById(EBizValidator.ebizChargeCode+"_expiry_month_new"));

            if (selMonthNew && selMonthNew.val()) {
                EBizValidator.selMonth = selMonthNew.val();
            }
            let selMonthUpdate = $(document.getElementById(EBizValidator.ebizChargeCode+"_expiry_month_update"));
            if (selMonthUpdate && selMonthUpdate.val()) {
                EBizValidator.selMonth = selMonthUpdate.val();
            }
            let selYearNew = $(document.getElementById(EBizValidator.ebizChargeCode+"_expiry_year_new"));
            if (selYearNew && selYearNew.val()) {
                EBizValidator.selYear = selYearNew.val();
            }
            let selYearUpdate = $(document.getElementById(EBizValidator.ebizChargeCode+"_expiry_year_update"));
            if (selYearUpdate && selYearUpdate.val()) {
                EBizValidator.selYear = selYearUpdate.val();
            }

            /**
             * is Valid Expiry Date
             * @type {boolean}
             */
            let isValidExpiryDate = false;
            let currentDateTime = new Date(EBizValidator.currentYear+"-"+EBizValidator.currentMonth);

            if ( EBizValidator.selMonth && EBizValidator.selYear) {
                let selectedDate = new Date(EBizValidator.selYear + '-' + EBizValidator.selMonth );
                if (selectedDate >= currentDateTime) {
                    isValidExpiryDate = true;
                }
            }

            return isValidExpiryDate;
        }
    }

    /**
     * EBizValidator
     */
    EBizValidator._init($, cvvValidator, creditCardNumberValidator, yearValidator, monthValidator, creditCardData);

    /**
     * run Validation through
     */
    $.each({
        'validate-card-type':  [
            function (number, item, allowedTypes) {
                var cardInfo,
                    i,
                    l;

                if (!creditCardNumberValidator(number).isValid) {
                    return false;
                }

                cardInfo = creditCardNumberValidator(number).card;
                console.log(cardInfo);
                for (i = 0, l = allowedTypes.length; i < l; i++) {
                    let allowedCardType = allowedTypes[i].type;
                    if(Array.isArray(allowedCardType)){
                        console.log(allowedCardType);
                        if (cardInfo.title == allowedCardType[0]) { //eslint-disable-line eqeqeq
                            return true;
                        }
                    }else {
                        if (cardInfo.title == allowedTypes[i].type) { //eslint-disable-line eqeqeq
                            return true;
                        }
                    }
                }

                return false;
            },
            $.mage.__('Please enter a valid credit card type number.')
        ],
        'validate-card-number': [

            /**
             * Validate credit card number based on mod 10
             *
             * @param {*} number - credit card number
             * @return {Boolean}
             */
            function (number) {
                return creditCardNumberValidator(number).isValid;
            },
            $.mage.__('Please enter a valid credit card number.')
        ],
        'validate-card-date': [

            /**
             * Validate credit card expiration month
             *
             * @param {String} date - month
             * @return {Boolean}
             */
            function (date) {
                let isValidMonth = false;
                // EBizValidator.selMonth = parseInt(date);

                if (EBizValidator.validateCreditCardExpiryDate($, EBizValidator.selMonth, EBizValidator.selYear)) {
                    isValidMonth = true;
                }
                return isValidMonth;
            },
            $.mage.__('Incorrect credit card expiration month.')
        ],
        'validate-card-year': [

            /**
             * Validate credit card expiration year
             *
             * @param {String} date - year
             * @return {Boolean}
             */
            function (date) {
                let isValidYear = false;
                EBizValidator.selYear = parseInt(date);

                console.log([EBizValidator.selMonth, EBizValidator.selYear]);

                if (EBizValidator.validateCreditCardExpiryDate($, EBizValidator.selMonth, EBizValidator.selYear)) {
                    isValidYear = true;
                }
                return isValidYear;
            },
            $.mage.__('Incorrect credit card expiration year.')
        ],
        'validate-card-expiry-date': [

            /**
             * Validate credit card expiration year
             *
             * @param {String} date - year
             * @return {Boolean}
             */
            function (date) {
                let isValidYear = false;
                EBizValidator.selYear = parseInt(date);

                if (EBizValidator.validateCreditCardExpiryDate($, EBizValidator.selMonth, EBizValidator.selYear)) {
                    isValidYear = true;
                }

                return isValidYear;
            },
            $.mage.__('Incorrect provided card expiry.')
        ],
        'validate-card-cvv': [

            /**
             * Validate cvv
             *
             * @param {String} cvv - card verification value
             * @return {Boolean}
             */
            function (cvv) {
                var maxLength = creditCardData.creditCard ? creditCardData.creditCard.code.size : 3;
                return cvvValidator(cvv, maxLength).isValid;
            },
            $.mage.__('Please enter a valid credit CVV.')
        ],
        'validate-bank-account-method': [
            function (bankAccountMethod) {
                let paymentAccountMethod = parseInt(bankAccountMethod);
                let isValid = false;

                if (paymentAccountMethod > 0) {
                    isValid = true;
                }
                return isValid;
            },
            $.mage.__('Please select valid Bank Account.')
        ],
        'validate-bank-account-number': [

            function (bankAccountNumber) {
                let isPaymentMethodValid = true;
                if (bankAccountNumber.length < 9) {
                    isPaymentMethodValid = false;
                }
                return isPaymentMethodValid;
            },
            $.mage.__('Please add valid bank account number min-9 and max-16.')
        ],
        'validate-bank-account-routing': [

            function (bankAccountRouting) {
                let isPaymentMethodValid = true;
                if (bankAccountRouting.length < 9) {
                    isPaymentMethodValid = false;
                }
                return isPaymentMethodValid;
            },
            $.mage.__('Please add valid bank Routing with min-9.')
        ],
        'validate-payment-method': [

            /**
             * Validate Payment Method
             *
             * @param paymentMethod
             * @returns {boolean}
             */
            function (paymentMethod) {
                let paymentMethodId = parseInt(paymentMethod);
                let isPaymentMethodValid = false;

                if (paymentMethodId > 0) {
                    isPaymentMethodValid = true;
                }
                return isPaymentMethodValid;
            },
            $.mage.__('Please select valid payment method.')
        ],
        'validate-card-cvv-amex': [

            /**
             * Validate cvv for Amex card
             *
             * @param {String} cvv - card verification value
             * @return {Boolean}
             */
            function (cvv) {
                var maxLength = creditCardData.creditCard ? creditCardData.creditCard.code.size : 4;

                return cvvValidator(cvv, maxLength).isValid;
            },
            $.mage.__('Please enter a valid credit CVV.')
        ],
        'validate-account-length': [

            /**
             * Validate Bank Account Number for Length
             *
             * @return {Boolean}
             * @param accountNumber
             */
            function (accountNumber) {
                let isBankAccount = $("#use_ach").is(":checked");
                if (isBankAccount) {
                    return true;
                }

                return yearValidator(accountNumber).isValid;
            },
            $.mage.__('Incorrect credit card expiration year.')
        ]
    }, function (i, rule) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });
}));
