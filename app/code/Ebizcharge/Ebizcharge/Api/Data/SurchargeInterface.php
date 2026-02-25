<?php
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

namespace Ebizcharge\Ebizcharge\Api\Data;

/**
 * EbizCharge Surcharge Interface
 *
 * Interface HowItWorksInterface
 */
interface SurchargeInterface
{
    /**
     * Constants for data array keys.
     */
    public const EC_SURCHARGE_AMOUNT = 'ec_surcharge_amount';

    /**
     * @const EC_SURCHARGE_PERCENTAGE
     */
    public const EC_SURCHARGE_PERCENTAGE = 'ec_surcharge_percentage';

    /**
     * @const EC_SURCHARGE_INELIGIBLE
     */
    public const EC_SURCHARGE_INELIGIBLE = 'ec_surcharge_ineligible';

    /**
     * Constants for configurations.
     */
    public const EBIZCHARGE_API_PAYMENTS_SURCHARGE = 'Surcharge';

    /**
     * Constants for API response keys & values.
     */
    public const EBIZ_SURCHARGE_TYPE_ID_DAILY_DISCOUNT = 'DailyDiscount';

    /**
     * @const EBIZ_SURCHARGE_ENABLED
     */
    public const EBIZ_SURCHARGE_ENABLED = 'surchargeEnabled';

    /**
     * @const EBIZ_SURCHARGE_FOR_ZIP
     */
    public const EBIZ_SURCHARGE_FOR_ZIP = 'surchargeForZip';

    /**
     * @const EBIZ_SURCHARGE_FOR_PAYMENT_METHOD
     */
    public const EBIZ_SURCHARGE_FOR_PAYMENT_METHOD = 'surchargeForPaymentMethod';

    /**
     * @const EBIZ_SURCHARGE_COUNTRY_ID
     */
    public const EBIZ_SURCHARGE_COUNTRY_ID = 'surchargeCountryId';

    /**
     * @const EBIZ_SURCHARGE_PERCENTAGE
     */
    public const EBIZ_SURCHARGE_PERCENTAGE = 'surchargePercentage';

    public const EBIZ_SURCHARGE_PERCENTAGE_TEXT = "surchargePercentage_txt";

    /**
     * @const EBIZ_SURCHARGE_AMOUNT
     */
    public const EBIZ_SURCHARGE_AMOUNT = 'surchargeAmount';

    /**
     * @const EBIZ_SURCHARGE_TERMS_NOTE
     */
    public const EBIZ_SURCHARGE_TERMS_NOTE = 'surchargeTermsNote';

    /**
     * @const EBIZ_SURCHARGE_CAPTION
     */
    public const EBIZ_SURCHARGE_CAPTION = 'surchargeCaption';

    /**
     * @const EBIZ_SURCHARGE_TYPE_ID
     */
    public const EBIZ_SURCHARGE_TYPE_ID = 'surchargeTypeId';

    /**
     * @const EBIZ_SURCHARGE_INELIGIBLE
     */
    public const EBIZ_SURCHARGE_INELIGIBLE = 'ineligible';

    /**
     * @const EBIZ_SURCHARGE_AMOUNT_WITH_SIGN
     */
    public const EBIZ_SURCHARGE_AMOUNT_WITH_SIGN = 'surchargeAmountWithSign';

    /**
     * Constants for Admin side labels.
     */
    public const EBIZ_INELIGIBLE_LABEL = 'Ineligible';

    /**
     * public const EBIZ_SURCHARGE_SURCHARGE_AMOUNT
     */
    public const EBIZ_SURCHARGE_SURCHARGE_AMOUNT = "surchargeAmount";

    /**
     * Constant for default response for calculate surcharge ajax call.
     *
     * @const DEFAULT_AJAX_CALCULATE_SURCHARGE_RESPONSE
     */
    public const DEFAULT_AJAX_CALCULATE_SURCHARGE_RESPONSE = [
        "surchargeEnabled" => false,
        "surchargeForZip" => false,
        "surchargeForPaymentMethod" => false,
        "surchargePercentage" => 0,
        "surchargeAmount" => 0,
        "surchargeCaption" => '',
        "surchargeTermsNote" => '',
        "surchargeAmountWithSign" => '',
        "ineligible" => false
    ];

    /**
     * @const EBIZCHARGE_CONFIG_TRANSACTION_DATA_IS_EMV_ENABLED
     */
    public const EBIZCHARGE_CONFIG_TRANSACTION_DATA_IS_EMV_ENABLED = "IsEMVEnabled";

    /**
     * @const EBIZCHARGE_CONFIG_TRANSACTION_DATA_ENABLE_AVS_WARNINGS
     */
    public const EBIZCHARGE_CONFIG_TRANSACTION_DATA_ENABLE_AVS_WARNINGS = "EnableAVSWarnings";

    /**
     * @const EBIZCHARGE_CONFIG_TRANSACTION_DATA_ENABLE_CVV_WARNINGS
     */
    public const EBIZCHARGE_CONFIG_TRANSACTION_DATA_ENABLE_CVV_WARNINGS = "EnableCVVWarnings";

    /**
     * @const EBIZCHARGE_CONFIG_TRANSACTION_DATA_USE_FULL_AMOUNT_FOR_AVS
     */
    public const EBIZCHARGE_CONFIG_TRANSACTION_DATA_USE_FULL_AMOUNT_FOR_AVS = "UseFullAmountForAVS";

    /**
     * @const EBIZCHARGE_CONFIG_TRANSACTION_DATA_DECLINE_TRANSACTION_IF_AVS_WARNINGS_DISABLED
     */
    public const EBIZCHARGE_CONFIG_TRANSACTION_DATA_DECLINE_TRANSACTION_IF_AVS_WARNINGS_DISABLED =
        "DeclineTransactionIfAVSWarningsAreDisabled";

    /**
     * @const EBIZCHARGE_CONFIG_TRANSACTION_DATA_VERIFY_CREDIT_CARD_BEFORE_SAVING
     */
    public const EBIZCHARGE_CONFIG_TRANSACTION_DATA_VERIFY_CREDIT_CARD_BEFORE_SAVING = "VerifyCreditCardBeforeSaving";

    /**
     * @const EBIZCHARGE_CONFIG_TRANSACTION_DATA_AUTO_DISCOUNT
     */
    public const EBIZCHARGE_CONFIG_TRANSACTION_DATA_AUTO_DISCOUNT = "AutoDiscount";

    /**
     * @const EBIZCHARGE_CONFIG_TRANSACTION_DATA_AUTO_ITEM_DISCOUNT
     */
    public const EBIZCHARGE_CONFIG_TRANSACTION_DATA_AUTO_ITEM_DISCOUNT = "AutoItemDiscount";

    /**
     * @const EBIZCHARGE_CONFIG_TRANSACTION_DATA_DISCOUNT_PERCENTAGE
     */
    public const EBIZCHARGE_CONFIG_TRANSACTION_DATA_DISCOUNT_PERCENTAGE = "DiscountPercentage";

    /**
     * @const EBIZCHARGE_CONFIG_TRANSACTION_DATA_USE_CAPTURE_ENHANCEMENT
     */
    public const EBIZCHARGE_CONFIG_TRANSACTION_DATA_USE_CAPTURE_ENHANCEMENT = "UseCaptureEnhancement";

    /**
     * @const EBIZCHARGE_CONFIG_TRANSACTION_DATA_IS_CREDIT_CARD_ENABLED
     */
    public const EBIZCHARGE_CONFIG_TRANSACTION_DATA_IS_CREDIT_CARD_ENABLED = "AllowCreditCardPayments";
}
