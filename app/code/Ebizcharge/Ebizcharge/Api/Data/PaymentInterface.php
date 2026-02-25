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

declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Api\Data;

/**
 * Interface PaymentInterface
 *
 * Payment Data Interface
 */
interface PaymentInterface
{

    /**
     * @const EBIZCHARGE_PAYMENT_METHOD
     */
    public const EBIZCHARGE_PAYMENT_METHOD = 'ebizcharge_ebizcharge';


    /**
     * @const KEY_ADDITIONAL_DATA
     */

    public const KEY_ADDITIONAL_DATA = "additional_data";

    /**
     * @const: PAYMENT_ENV_TYPE_FRONTEND
     */
    public const PAYMENT_ENV_TYPE_FRONTEND = "frontend";

    /**
     *
     * @const: PAYMENT_ENV_TYPE_BACKEND
     */
    public const PAYMENT_ENV_TYPE_BACKEND = "backend";

    /**
     * CVV2 Card code result M
     */
    public const CVV2_CARD_CODE_RESULT_M = "M";

    /**
     * Card Code Result N
     *
     * @const CVV2_CARD_CODE_RESULT_N
     */
    public const CVV2_CARD_CODE_RESULT_N = "N";

    /**
     * Card Code Result P
     *
     * @const CVV2_CARD_CODE_RESULT_P
     */
    public const CVV2_CARD_CODE_RESULT_P = "P";

    /**
     * Cvv2 Card Code Result s
     *
     * @const CVV2_CARD_CODE_RESULT_S
     */
    public const CVV2_CARD_CODE_RESULT_S = "S";

    /**
     * Cvv2 Card Code Result U
     *
     * @const CVV2_CARD_CODE_RESULT_U
     */
    public const CVV2_CARD_CODE_RESULT_U = "U";

    /**
     * Cvv2 Card Code Result X
     *
     * @const CVV2_CARD_CODE_RESULT_X
     */
    public const CVV2_CARD_CODE_RESULT_X = "X";

    /**
     * Cvv2 Card Code Result Blank
     *
     * @const: CVV2_CARD_CODE_RESULT_BLANK
     */
    public const CVV2_CARD_CODE_RESULT_BLANK = "";

    /**
     * CVV2 Card Code Message Labels
     * CVV Labels
     */

    /**
     * CVV2 Card code result Message M
     *
     * @const: CVV2_CARD_CODE_RESULT_MSG_LABEL_M
     */
    public const CVV2_CARD_CODE_RESULT_MSG_LABEL_M = "Matched the CVV2 code";

    /**
     * Card Code Result Message N
     *
     * @const CVV2_CARD_CODE_RESULT_MSG_LABEL_N
     */
    public const CVV2_CARD_CODE_RESULT_MSG_LABEL_N = "No match; indicates the code entered is incorrect.";

    /**
     * Card Code Result P
     *
     * @const CVV2_CARD_CODE_RESULT_MSG_LABEL_P
     */
    public const CVV2_CARD_CODE_RESULT_MSG_LABEL_P = "Not processed; the code was not validated.";

    /**
     * Cvv2 Card Code Result s
     *
     * @const CVV2_CARD_CODE_RESULT_MSG_LABEL_S
     */
    // phpcs:ignore
    public const CVV2_CARD_CODE_RESULT_MSG_LABEL_S = "Issuer indicates that CVV2 data should be present on the card, but the merchant has indicated data is not present on the card";

    /**
     * Cvv2 Card Code Result U
     *
     * @const CVV2_CARD_CODE_RESULT_MSG_LABEL_U
     */
    // phpcs:ignore
    public const CVV2_CARD_CODE_RESULT_MSG_LABEL_U = "Issuer Not Certified; means that the card issuing bank does not participate in the CVV2 program or hasn\'t provided the key so that the code can be validated.";

    /**
     * Cvv2 Card Code Result X
     *
     * @const CVV2_CARD_CODE_RESULT_MSG_LABEL_X
     */
    public const CVV2_CARD_CODE_RESULT_MSG_LABEL_X = "No response from association.";

    /**
     * Cvv2 Card Code Result Blank
     *
     * @const: CVV2_CARD_CODE_RESULT_MSG_LABEL_BLANK
     */
    // phpcs:ignore
    public const CVV2_CARD_CODE_RESULT_MSG_LABEL_BLANK = "No CVV2/CVC data available for transaction. No code was sent and there was no indication the code was not present on the card.";

    /**
     *
     * CVV2 / CVV Display Messages
     */
    /**
     * CVV2 Card code result Display Message M
     *
     * @const: CVV2_CARD_CODE_RESULT_DISPLAY_MSG_M
     */
    public const CVV2_CARD_CODE_RESULT_DISPLAY_MSG_M = "Matched;";

    /**
     * Card Code Result Display Message N
     *
     * @const CVV2_CARD_CODE_RESULT_DISPLAY_MSG_N
     */
    public const CVV2_CARD_CODE_RESULT_DISPLAY_MSG_N = "Not a match;";

    /**
     * Card Code  Display Result P
     *
     * @const CVV2_CARD_CODE_RESULT_DISPLAY_MSG_P
     */
    public const CVV2_CARD_CODE_RESULT_DISPLAY_MSG_P = "Not processed";

    /**
     * Cvv2 Card Code  Display Result s
     *
     * @const CVV2_CARD_CODE_RESULT_DISPLAY_MSG_S
     */
    public const CVV2_CARD_CODE_RESULT_DISPLAY_MSG_S = "Not processed";

    /**
     * Cvv2 Card Code  Display Result U
     *
     * @const CVV2_CARD_CODE_RESULT_DISPLAY_MSG_U
     */
    public const CVV2_CARD_CODE_RESULT_DISPLAY_MSG_U = "Not processed";

    /**
     * Cvv2 Card Code  Display Result X
     *
     * @const CVV2_CARD_CODE_RESULT_DISPLAY_MSG_X
     */
    public const CVV2_CARD_CODE_RESULT_DISPLAY_MSG_X = "Not processed";

    /**
     * Cvv2 Card Code  Display Result Blank
     *
     * @const: CVV2_CARD_CODE_RESULT_DISPLAY_MSG_BLANK
     */
    public const CVV2_CARD_CODE_RESULT_DISPLAY_MSG_BLANK = "Not processed";

    /**
     * AVS Card Code Blank
     *
     * @const: AVS_CARD_CODE_RESULT_BLANK
     */
    public const AVS_CARD_CODE_RESULT_BLANK = "";

    /**
     * AVS Card Code Result YYY
     *
     * @const: AVS_CARD_CODE_RESULT_YYY
     */
    public const AVS_CARD_CODE_RESULT_YYY = "YYY";

    /**
     * AVS Card Code Result NYZ
     *
     * @const: AVS_CARD_CODE_RESULT_NYZ
     */
    public const AVS_CARD_CODE_RESULT_NYZ = "NYZ";

    /**
     * AVS Card Code Result YNA
     *
     * @const: AVS_CARD_CODE_RESULT_YNA
     */
    public const AVS_CARD_CODE_RESULT_YNA = "YNA";

    /**
     * AVS Card Code Result NNW
     *
     * @const: AVS_CARD_CODE_RESULT_NNN
     */
    public const AVS_CARD_CODE_RESULT_NNN = "NNN";

    /**
     * AVS Card Code Result YYX
     *
     * @const: AVS_CARD_CODE_RESULT_YYX
     */
    public const AVS_CARD_CODE_RESULT_YYX = "YYX";

    /**
     * AVS Card Code Result YYW
     *
     * @const: AVS_CARD_CODE_RESULT_NYW
     */
    public const AVS_CARD_CODE_RESULT_NYW = "NYW";

    /**
     * AVS Card Code Result XXW
     *
     * @const: AVS_CARD_CODE_RESULT_XXW
     */
    public const AVS_CARD_CODE_RESULT_XXW = "XXW";

    /**
     * AVS Card Code Result XXU
     *
     * @const: AVS_CARD_CODE_RESULT_XXU
     */
    public const AVS_CARD_CODE_RESULT_XXU = "XXU";

    /**
     * AVS Card Code Result XXR
     *
     * @const: AVS_CARD_CODE_RESULT_XXR
     */
    public const AVS_CARD_CODE_RESULT_XXR = "XXR";

    /**
     * AVS Card Code Result XXS
     *
     * @const: AVS_CARD_CODE_RESULT_XXS
     */
    public const AVS_CARD_CODE_RESULT_XXS = "XXS";

    /**
     * AVS Card Code Result XXE
     *
     * @const: AVS_CARD_CODE_RESULT_XXE
     */
    public const AVS_CARD_CODE_RESULT_XXE = "XXE";

    /**
     * AVS Card Code Result XXG
     *
     * @const: AVS_CARD_CODE_RESULT_XXG
     */
    public const AVS_CARD_CODE_RESULT_XXG = "XXG";

    /**
     * AVS Card Code Result YYG
     *
     * @const: AVS_CARD_CODE_RESULT_YYG
     */
    public const AVS_CARD_CODE_RESULT_YYG = "YYG";

    /**
     * AVS Card Code Result GGG
     *
     * @const: AVS_CARD_CODE_RESULT_GGG
     */
    public const AVS_CARD_CODE_RESULT_GGG = "GGG";

    /**
     * AVS Card Code Result YGG
     *
     * @const: AVS_CARD_CODE_RESULT_YGG
     */
    public const AVS_CARD_CODE_RESULT_YGG = "YGG";

    /**
     *
     * AVVS Display Label Messages
     */
    /**
     * AVS Card Code Result BLANK
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_BLANK
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_BLANK = "No AVS response";

    /**
     *
     * AVVS Display Label Messages
     */
    /**
     * AVS Card Code Result YYY
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_YYY
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_YYY = "Match; the Address and Zip Code | Postal Code entered matched.";

    /**
     * AVS Card Code Result NYZ
     *
     * @const: AVS_CARD_CODE_RESULT_NYZ
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_NYZ =
        "Address is not a match; indicates the address entered is incorrect.";

    /**
     * AVS Card Code Result YNA
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_YNA
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_YNA =
        "5-digit zip/postal code is not a match; indicates the Zip/postal code entered is incorrect.";

    /**
     * AVS Card Code Result NNW
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_NNN
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_NNN =
        "Address and 5-digit zip/postal code entered were not matches; indicates they are both incorrect.";

    /**
     * AVS Card Code Result YYX
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_YYX
     * @phpcs:disable
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_YYX = "Address is a match but 9-digit zip/postal code entered is not a match; indicates zip/postal code is incorrect.";

    /**
     * AVS Card Code Result YYW
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_YYW
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_YYW = "Address is not a match but 9-digit zip/postal code entered is a match; indicates the address entered is incorrect.";
    //phpcs:enable

    /**
     * AVS Card Code Result XXW
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_XXW
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_XXW = "Card number is not on file.";

    /**
     * AVS Card Code Result XXU
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_XXU
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_XXU = "Address information not verified for domestic transaction.";

    /**
     * AVS Card Code Result XXR
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_XXR
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_XXR = "Retry needed or system unavailable.";

    /**
     * AVS Card Code Result XXS
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_XXS
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_XXS = "Service not supported.";

    /**
     * AVS Card Code Result XXE
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_XXE
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_XXE = "Address verification not allowed for the card type.";

    /**
     * AVS Card Code Result XXG
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_XXG
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_XXG = "Global non-AVS participant.";

    /**
     * AVS Card Code Result YYG
     *
     * @const: AVS_CARD_CODE_RESULT_YYG
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_YYG =
        "International address entered is a match but zip/postal code is not compatible.";

    /**
     * AVS Card Code Result GGG
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_GGG
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_GGG = "International address and zip/postal code entered are matches.";

    /**
     * AVS Card Code Result YGG
     *
     * @const: AVS_CARD_CODE_RESULT_MSG_LABEL_YGG
     */
    public const AVS_CARD_CODE_RESULT_MSG_LABEL_YGG =
        "International address entered is not compatible but zip/postal code is match.";

    /**
     *
     * AVS Display Messages
     *
     */

    /**
     * AVS Card Code Display Message Result BLANK
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_BLANK
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_BLANK =
        "No AVS response {br} Typically no AVS data sent or swiped transaction";

    /**
     * AVS Card Code Display Message Result YYY
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_YYY
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_YYY = "Address: Match {br} Zip/Postal Code: Match";

    /**
     * AVS Card Code Display Message Result NYZ
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_NYZ
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_NYZ = "Address: Not a match {br} Zip/Postal Code: Match";

    /**
     * AVS Card Code Display Message Result YNA
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_YNA
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_YNA = "Address: Match {br} Zip/Postal Code: Not a match";

    /**
     * AVS Card Code Display Message Result NNW
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_NNN
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_NNN = "Address: Not a match {br} Zip/Postal Code: Not a match";

    /**
     * AVS Card Code Display Message Result YYX
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_YYX
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_YYX = "Address: Match {br} Zip/Postal Code: Not a match";

    /**
     * AVS Card Code Display Message Result YYW
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_NYW
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_NYW = "Address: Not a match {br} Zip/Postal Code: Match";

    /**
     * AVS Card Code Display Message Result XXW
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXW
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXW = "Card number not on file";

    /**
     * AVS Card Code Display Message Result XXU
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXU
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXU = "Address not verified for domestic transaction.";

    /**
     * AVS Card Code Display Message Result XXR
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXR
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXR = "Retry / system unavailable.";

    /**
     * AVS Card Code Display Message Result XXS
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXS
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXS = "Service not supported.";

    /**
     * AVS Card Code Display Message Result XXE
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXE
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXE = "Address verification not allowed for card type.";

    /**
     * AVS Card Code Display Message Result XXG
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXG
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_XXG = "Global non-AVS participant.";

    /**
     * AVS Card Code Display Message Result YYG
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_YYG
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_YYG =
        "International address: Match {br} Zip/Postal Code: Not compatible";

    /**
     * AVS Card Code Display Message Result GGG
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_GGG
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_GGG = "International address: Match {br} Zip/Postal Code: Match";

    /**
     * AVS Card Code Display Message Result YGG
     *
     * @const: AVS_CARD_CODE_RESULT_DISPLAY_MSG_YGG
     */
    public const AVS_CARD_CODE_RESULT_DISPLAY_MSG_YGG =
        "International address Not compatible {br} Zip/Postal Code: Match";

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_1
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_1 = '1';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_2
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_2 = '2';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_3
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_3 = '3';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_4
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_4 = '4';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_5
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_5 = '5';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_6
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_6 = '6';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_7
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_7 = '7';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_8
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_8 = '8';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_9
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_9 = '9';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_A
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_A = 'A';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_B
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_B = 'B';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_C
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_C = 'C';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_D
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_D = 'D';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_G
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_G = 'G';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_H
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_H = 'H';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_I
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_I = 'I';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_K
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_K = 'K';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_S
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_S = 'S';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_U
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_U = 'U';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_G1
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_G1 = 'G1';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_G2
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_G2 = 'G2';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_J1
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_J1 = 'J1';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_J2
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_J2 = 'J2';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_J3
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_J3 = 'J3';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_J4
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_J4 = 'J4';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_K1
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_K1 = 'K1';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_S1
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_S1 = 'S1';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_S2
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_S2 = 'S2';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_S3
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_S3 = 'S3';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_1
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_1 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_2
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_2 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_3
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_3 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_4
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_4 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_5
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_5 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_6
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_6 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_7
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_7 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_8
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_8 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_9
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_9 = '9Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_A
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_A = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_B
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_B = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_C
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_C = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_D
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_D = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_G
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_G = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_H
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_H = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_I
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_I = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_K
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_K = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_S
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_S = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_U
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_U = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_G1
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_G1 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_G2
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_G2 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_J1
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_J1 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_J2
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_J2 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_J3
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_J3 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_J4
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_J4 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_K1
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_K1 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_S1
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_S1 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_S2
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_S2 = 'Not matched';

    /**
     * @const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_S3
     */
    public const CAVV_CARD_CODE_RESULT_RESPONSE_LABEL_S3 = 'Not matched';

    /**
     * Card Result Response Declined
     *
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_-
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_ = 'Declined';

    /**
     * Card Result Response
     *
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_04
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_04 = 'Pickup Card';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_05
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_05 = 'Do not Honor';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_12
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_12 = 'Invalid Transaction';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_15
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_15 = 'Invalid Issuer';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_25
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_25 = 'Unable to locate Record';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_51
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_51 = 'Insufficient funds';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_55
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_55 = 'Invalid Pin';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_57
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_57 = 'Transaction Not Permitted';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_62
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_62 = 'Restricted Card';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_65
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_65 = 'Excess withdrawal count';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_75
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_75 = 'Allowable number of pin tries exceeded';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_78
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_78 = 'No checking account';

    /**
     * Decline Card Code Response 97
     *
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_97
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_LABEL_97 = 'Declined for CVV failure';

    /**
     * Card Result Response
     *
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_ = '-';

    /**
     * Card Result Response
     *
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_04
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_04 = '04';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_05
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_05 = '05';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_12
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_12 = '12';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_15
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_15 = '15';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_25
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_25 = '25';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_51
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_51 = '51';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_55
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_55 = '55';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_57
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_57 = '57';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_62
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_62 = '62';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_65
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_65 = '65';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_75
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_75 = '75';

    /**
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_78
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_78 = '78';

    /**
     * Decline Card Code Response 97
     *
     * @const DECLINE_CARD_CODE_RESULT_RESPONSE_97
     */
    public const DECLINE_CARD_CODE_RESULT_RESPONSE_97 = '97';

    /**
     * Code for the Payment Gateway
     *
     * @const CODE
     */
    public const CODE = 'ebizcharge_ebizcharge';

    /**
     *
     * Ebizcharge Command Refund
     *
     * @const: EBIZCHARGE_COMMAND_REFUND
     */
    public const EBIZCHARGE_COMMAND_REFUND = 'refund';

    /**
     *
     * Ebizcharge Command Capture
     *
     * @const: EBIZCHARGE_COMMAND_CAPTURE
     */
    public const EBIZCHARGE_COMMAND_CAPTURE = 'capture';

    /**
     *
     * Ebizcharge Command Quick Sale
     *
     * @const: EBIZCHARGE_COMMAND_QUICK_SALE
     */
    public const EBIZCHARGE_COMMAND_QUICK_SALE = 'quicksale';

    /**
     *
     * Ebizcharge Command Credit Void
     *
     * @const: EBIZCHARGE_COMMAND_CREDIT_VOID
     */
    public const EBIZCHARGE_COMMAND_CREDIT_VOID = 'creditvoid';

    /**
     * Ebizcharge Command Authonly
     *
     * @const: EBIZCHARGE_COMMAND_AUTHONLY
     */
    public const EBIZCHARGE_COMMAND_AUTHONLY = 'AuthOnly';

    /**
     * Ebizcharge Command Type Authonly
     *
     * @const: EBIZCHARGE_COMMAND_TYPE_AUTHONLY
     */
    public const EBIZCHARGE_COMMAND_TYPE_AUTHONLY = 'AuthOnly';

    /**
     * Ebizcharge Transaction Response
     *
     * @const PAYMENT_TRANSACTION_RESPONSE_TYPE_AUTHORIZE
     */
    public const PAYMENT_TRANSACTION_RESPONSE_TYPE_AUTHORIZE = 'A';

    /**
     * Ebizcharge Command Sale
     *
     * @const: EBIZCHARGE_COMMAND_SALE
     */
    public const EBIZCHARGE_COMMAND_SALE = 'sale';

    /**
     * Ebizcharge Payment Type GraphQL Web Form
     *
     * @const: EBIZCHARGE_METHOD_GRAPHQL_TYPE_WEB_FORM
     */
    public const EBIZCHARGE_METHOD_GRAPHQL_TYPE_WEB_FORM = 'graphql_webform';

    /**
     * Ebizcharge Payment Type GraphQL Saved
     *
     * @const: EBIZCHARGE_METHOD_GRAPHQL_TYPE_SAVED
     */
    public const EBIZCHARGE_METHOD_GRAPHQL_TYPE_SAVED = 'graphql_saved';

    /**
     * Ebizcharge Payment Type GraphQL New
     *
     * @const: EBIZCHARGE_METHOD_GRAPHQL_TYPE_NEW
     */
    public const EBIZCHARGE_METHOD_GRAPHQL_TYPE_NEW = 'graphql_new';

    /**
     * Ebizcharge Method GraphQL Type User
     *
     * @const EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_TYPE_RPM_CHECKOUT_USER
     */
    public const EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_TYPE_RPM_CHECKOUT_USER = 'RPMcheckoutUser';

    /**
     * Ebizcharge Method Graph QL Webform type Guest
     *
     * @const EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_TYPE_RPM_CHECKOUT_GUEST
     */
    public const EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_TYPE_RPM_CHECKOUT_GUEST = 'RPMcheckoutGuest';

    /**
     * Payment Method Type Web Form
     *
     * @const EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_PAYMENT_TYPE_CHECKOUT_WEBFORM
     */
    public const EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_PAYMENT_TYPE_CHECKOUT_WEBFORM = 'WebForm';

    /**
     * Add Payment Method
     *
     * @const EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_TYPE_CHECKOUT_Add_PAYMENT_METHOD
     */
    public const EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_TYPE_CHECKOUT_ADD_PAYMENT_METHOD = 'PmRequestForm';

    /**
     * Payment Type Email Form
     *
     * @const EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_TYPE_CHECKOUT_PAYMENT_EMAIL_FORM
     */
    public const EBIZCHARGE_METHOD_GRAPHQL_WEBFORM_TYPE_CHECKOUT_PAYMENT_EMAIL_FORM = 'EmailForm';

    /**
     *  Method Type Check
     *
     * @const: EBIZCHARGE_METHOD_TYPE_CHECK
     */
    public const EBIZCHARGE_METHOD_TYPE_CHECK = 'check';

    /**
     * Method type Credit
     *
     * @const: EBIZCHARGE_METHOD_TYPE_CREDIT
     */
    public const EBIZCHARGE_METHOD_TYPE_CREDIT = 'credits';

    /**
     * Method Type Void
     *
     * @const: EBIZCHARGE_METHOD_TYPE_VOID
     */
    public const EBIZCHARGE_METHOD_TYPE_VOID = 'voids';

    /**
     * Method Type Void
     *
     * @const: EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID
     */
    public const EBIZCHARGE_METHOD_TRANSACTION_TYPE_VOID = 'void';

    /**
     * Method Type Credit Void
     *
     * @const: EBIZCHARGE_METHOD_TYPE_CREDIT_VOID
     */
    public const EBIZCHARGE_METHOD_TYPE_CREDIT_VOID = 'creditvoid';

    /**
     * Payment Type Post Auth
     *
     * @const: EBIZCHARGE_METHOD_TYPE_POST_AUTH
     */
    public const EBIZCHARGE_METHOD_TYPE_POST_AUTH = 'postauth';

    /**
     * Pre Auth Amount
     * Before Saving Card
     *
     * @const: EBIZCHARGE_TRANSACTION_PRE_AUTH_AMOUNT
     */
    public const EBIZCHARGE_TRANSACTION_PRE_AUTH_AMOUNT = 0.05;

    /**
     * Payment Type Check Credit
     *
     * @const: EBIZCHARGE_METHOD_TYPE_CHECK_CREDIT
     */
    public const EBIZCHARGE_METHOD_TYPE_CHECK_CREDIT = 'CheckCredit';

    /**
     * Transaction Type Authorize
     *
     * @const: PAYMENT_TRANSACTION_TYPE_AUTHORIZE
     */
    public const PAYMENT_TRANSACTION_TYPE_AUTHORIZE = "authorize";

    /**
     * Payment Transaction Type Authorize and Capture
     *
     * @const: PAYMENT_TRANSACTION_TYPE_AUTHORIZE_CAPTURE
     */
    public const PAYMENT_TRANSACTION_TYPE_AUTHORIZE_CAPTURE = "authorize_capture";

    //Sale, AuthOnly, Credit, Check, and CheckCredit

    /**
     * ACH
     *
     * @const ACH
     */
    public const ACH = 'ACH';

    /**
     * Payment Method Type Params
     *
     * @const: PAYMENT_METHOD_TYPE_PARAMS
     */
    public const PAYMENT_METHOD_TYPE_PARAMS = 'payment_params';

    /**
     *
     * Payment Option type Hosted Web form
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_HOSTED_FORM
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_HOSTED_FORM = 'Hosted';

    /**
     *
     * Payment Option type Hosted Web form Tokenized Only
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_HOSTED_FORM_TOKENIZED_ONLY
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_HOSTED_FORM_TOKENIZED_ONLY = 'Tokenized_Only';


    /**
     * Method type Credit Card
     *
     * @const: EBIZCHARGE_METHOD_TYPE_CREDIT_CARD
     */
    public const EBIZCHARGE_METHOD_TYPE_CREDIT_CARD = 'credit_card';

    /**
     * Method type ACH Bank Account
     *
     * @const: EBIZCHARGE_METHOD_TYPE_ACH
     */
    public const EBIZCHARGE_METHOD_TYPE_ACH = 'ACH';

    /**
     * Method type PAY LATER
     *
     * @const: EBIZCHARGE_METHOD_TYPE_PAY_LATER
     */
    public const EBIZCHARGE_METHOD_TYPE_PAY_LATER = 'paylater';

    /**
     * Ebizcharge Payment Option Type New
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_NEW
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_NEW = 'new';
    /**
     * Ebizcharge Payment Option Type Card New
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_CARD_NEW
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_CARD_NEW = 'card_new';

    /**
     * Ebizcharge Payment Option Type Saved
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_SAVED
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_SAVED = 'saved';

    /**
     * Ebizcharge Payment Option Type Update
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_UPDATE
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_UPDATE = 'update';

    /**
     * Ebizcharge Payment Option Type Pay Later
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_PAY_LATER
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_PAY_LATER = 'paylater';

    /**
     * Ebizcharge Payment Option Type Download Order
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_DOWNLOAD_ORDER
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_DOWNLOAD_ORDER = 'downloadorder';

    /**
     * Ebizcharge Payment Option Type Recurring
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_RECURRING
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_RECURRING = 'recurring';

    /**
     *
     * Payment Option type Credit Card
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_CREDIT_CARD
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_CREDIT_CARD = "credit_card";

    /**
     * Payment Option type
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE = "ebzc_option_type";

    /**
     * Payment Option Type Cc Number
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_NUMBER
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_NUMBER = "cc_number";

    /**
     * Payment Option Type Expiry Month
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_EXP_MONTH
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_EXP_MONTH = "cc_exp_month";

    /**
     * Payment Option type expiry Year
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_EXP_YEAR
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_EXP_YEAR = "cc_exp_year";

    /**
     * Payment Option Type Ach Routing
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_ACH_ROUTING
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_ACH_ROUTING = "cc_routing";

    /**
     * Payment Option Type ach_type
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_ACH_TYPE
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_ACH_TYPE = "ach_type";

    /**
     * CC Number Masking Length
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_NUMBER_MASKING_LENGTH
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_NUMBER_MASKING_LENGTH = -4;

    /**
     * ACH Routing Masking Length
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_ACH_ROUTING_MASKING_LENGTH
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_ACH_ROUTING_MASKING_LENGTH = -3;
    /**
     *
     * CVV Code
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_ID
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_CC_ID = "cc_cid";

    /**
     *
     * Bank Account Type Checking
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_BANK_ACCOUNT_TYPE_CHECKING
     */
    public const EBIZCHARGE_PAYMENT_OPTION_BANK_ACCOUNT_TYPE_CHECKING = "checking";

    /**
     *
     * Bank Account Type savings
     *
     * @const: EBIZCHARGE_PAYMENT_OPTION_BANK_ACCOUNT_TYPE_SAVINGS
     */
    public const EBIZCHARGE_PAYMENT_OPTION_BANK_ACCOUNT_TYPE_SAVINGS = "savings";

    /**
     * EBizCharge transaction non tax
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_NON_TAX
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_NON_TAX = 'NonTax';

    /**
     * EBizCharge transaction tax
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_TAX
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_TAX = 'Tax';

    /**
     * EBizCharge transaction table
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_TABLE
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_TABLE = 'Table';

    /**
     * EBizCharge transaction subtotal
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_SUBTOTAL
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_SUBTOTAL = 'Subtotal';

    /**
     * EBizCharge transaction Shipping
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_SHIPPING
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_SHIPPING = 'Shipping';

    /**
     * EBizCharge transaction Ship From Zip
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_SHIP_FROM_ZIP
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_SHIP_FROM_ZIP = 'ShipFromZip';

    /**
     * EBizCharge transaction PO Number
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_PO_NUMBER
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_PO_NUMBER = 'PONum';

    /**
     * EBizCharge transaction Order Id
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_ORDER_ID
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_ORDER_ID = 'OrderID';

    /**
     * EBizCharge transaction Invoice
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_INVOICE
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_INVOICE = 'Invoice';

    /**
     * EBizCharge transaction Duty
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_DUTY
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_DUTY = 'Duty';

    /**
     * EBizCharge transaction Discount
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_DISCOUNT
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_DISCOUNT = 'Discount';

    /**
     * EBizCharge transaction Comments
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_COMMENTS
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_COMMENTS = 'Comments';

    /**
     * EBizCharge transaction Description
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_DESCRIPTION
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_DESCRIPTION = 'Description';

    /**
     * EBizCharge transaction Currency
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CURRENCY
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CURRENCY = 'Currency';

    /**
     * EBizCharge transaction Clerk
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CLERK
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CLERK = 'Clerk';

    /**
     * EBizCharge transaction Aount
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AMOUNT
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AMOUNT = 'Amount';

    /**
     * EBizCharge transaction Allow Partial Auth
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_ALLOW_PARTIAL_AUTH
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_ALLOW_PARTIAL_AUTH = 'AllowPartialAuth';

    /**
     * EBizCharge transaction Terminal
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_TERMINAL
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_TERMINAL = 'Terminal';

    /**
     * EBizCharge Transaction Tip
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_TIP
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_TIP = 'Tip';

    /**
     * EBizCharge Transaction Custom Number
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CUSTOM_NUM
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CUSTOM_NUM = 'CustNum';

    /**
     * EBizCharge Transaction Resultcode
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_RESULT_CODE
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_RESULT_CODE = 'ResultCode';

    /**
     * EBizCharge Transaction Result
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_RESULT
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_RESULT = 'Result';

    /**
     * EBizCharge Transaction Remaining Balance
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_REMAINING_BALANCE
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_REMAINING_BALANCE = 'RemainingBalance';

    /**
     * EBizCharge Transaction Reference Number
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_REFERENCE_NUMBER
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_REFERENCE_NUMBER = 'RefNum';

    /**
     * EBizCharge Transaction Is Duplicate
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_IS_DUPLICATE
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_IS_DUPLICATE = 'isDuplicate';

    /**
     * EBizCharge Transaction Error Code
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_ERROR_CODE
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_ERROR_CODE = 'ErrorCode';

    /**
     * EBizCharge Transaction Error
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_ERROR
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_ERROR = 'Error';

    /**
     *
     * @const EBIZCHARGE_PAYMENT_COLUMN_CC_TYPE_2
     */
    public const EBIZCHARGE_PAYMENT_COLUMN_CC_TYPE_2 = 'cc_type_2';

    /**
     * @const EBIZCHARGE_PAYMENT_COLUMN_BILL_TO_CUSTOMER
     */
    public const EBIZCHARGE_PAYMENT_COLUMN_BILL_TO_CUSTOMER = "bill_to_customer";

    /**
     * @const public const EBIZCHARGE_PAYMENT_BILL_TO_CUSTOMER_TYPE_WEB;
     */
    public const EBIZCHARGE_PAYMENT_BILL_TO_CUSTOMER_TYPE_WEB = "WEB";


    /**
     * EBizCharge Transaction Converted Amount Currency
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CONVERTED_AMOUNT_CURRENCY
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CONVERTED_AMOUNT_CURRENCY = 'ConvertedAmountCurrency';

    /**
     * EBizCharge Transaction Converted Amount
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CONVERTED_AMOUNT
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CONVERTED_AMOUNT = 'ConvertedAmount';

    /**
     * EBizCharge Transaction Conversion Rate
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CONVERSION_RATE
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CONVERSION_RATE = 'ConversionRate';

    /**
     * EBizCharge Transaction Card Code Result Code
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_CODE_RESULT_CODE
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_CODE_RESULT_CODE = 'CardCodeResultCode';

    /**
     * EBizCharge Transaction Card Code Result
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_CODE_RESULT
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_CODE_RESULT = 'CardCodeResult';

    /**
     * EBizCharge Transaction Batch Reference Number
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_BATCH_REFERENCE_NUMBER
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_BATCH_REFERENCE_NUMBER = 'BatchRefNum';

    /**
     * EBizCharge Transaction Batch Number
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_BATCH_NUMBER
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_BATCH_NUMBER = 'BatchNum';

    /**
     * EBizCharge Transaction Avs Result Code
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AVS_RESULT_CODE
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AVS_RESULT_CODE = 'AvsResultCode';

    /**
     * EBizCharge Transaction Avs Result
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AVS_RESULT
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AVS_RESULT = 'AvsResult';

    /**
     * EBizCharge Transaction Auth code
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AUTH_CODE
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AUTH_CODE = 'AuthCode';

    /**
     * EBizCharge Transaction Auth Amount
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AUTH_AMOUNT
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AUTH_AMOUNT = 'AuthAmount';

    /**
     * EBizCharge Transaction Status
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_STATUS
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_STATUS = 'Status';

    /**
     * EBizCharge Transaction Status Code
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_STATUS_CODE
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_STATUS_CODE = 'StatusCode';

    /**
     * EBizCharge Transaction Pares
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_PARES
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_PARES = 'Pares';

    /**
     * EBizCharge Transaction Mag Support
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_MAG_SUPPORT
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_MAG_SUPPORT = 'MagSupport';

    /**
     * EBizCharge Transaction Mag Stripe
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_MAG_STRIPE
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_MAG_STRIPE = 'MagStripe';

    /**
     * EBizCharge Transaction Internal Card Auth
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_INTERNAL_CARD_AUTH
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_INTERNAL_CARD_AUTH = 'InternalCardAuth';

    /**
     * EBizCharge Transaction Card Type
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_TYPE
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_TYPE = 'CardType';

    /**
     * EBizCharge Transaction Card Present
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_PRESENT
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_PRESENT = 'CardPresent';

    /**
     * EBizCharge Transaction Card Number
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_NUMBER
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_NUMBER = 'CardNumber';

    /**
     * EBizCharge Transaction Card Expiration
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_EXPIRATION
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_EXPIRATION = 'CardExpiration';

    /**
     * EBizCharge Transaction Card Code
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_CODE
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_CARD_CODE = 'CardCode';

    /**
     * EBizCharge Transaction Avs Zip
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AVS_ZIP
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AVS_ZIP = 'AvsZip';

    /**
     * EBizCharge Transaction Avs Street
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AVS_STREET
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_AVS_STREET = 'AvsStreet';

    /**
     * EBizCharge Transaction Term Type
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_TERM_TYPE
     */
    public const  EBIZCHARGE_PAYMENT_TRANSACTION_TYPE_TERM_TYPE = 'TermType';

    /**
     * EBizCharge Transaction Payment Method Id
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_METHOD_ID
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_METHOD_ID = 'PaymentMethodId';

    /**
     * EBizCharge Transaction Payment Card Number
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_CARD_NUMBER
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_CARD_NUMBER = 'CardNumber';

    /**
     * EBizCharge Transaction Payment Card Type
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_CARD_TYPE
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_CARD_TYPE = 'CardType';

    /**
     * EBizCharge Transaction Payment Card Code Result Code
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_CARD_CODE_RESULT_CODE
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_CARD_CODE_RESULT_CODE = 'CardCodeResultCode';

    /**
     * EBizCharge Transaction Payment Card Code Result
     *
     * @const: EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_CARD_CODE_RESULT
     */
    public const EBIZCHARGE_PAYMENT_TRANSACTION_PAYMENT_CARD_CODE_RESULT = 'CardCodeResult';

    /**
     * Credit Card Code
     *
     * @const EBIZCHARGE_PAYMENT_OPTION_TYPE_CREDIT_CARD_CODE
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_CREDIT_CARD_CODE = "cc";

    /**
     * Bank Account type Code
     *
     * @const EBIZCHARGE_PAYMENT_OPTION_TYPE_BANK_ACCOUNT_CODE
     */
    public const EBIZCHARGE_PAYMENT_OPTION_TYPE_BANK_ACCOUNT_CODE = "check";

    /**
     * Ebizcharge Application Payment Reference ID
     */
    public const EBIZCHARGE_APPLICATION_PAYMENT_REFERENCE_ID = "ebzc_application_payment_ref_id";

    /**
     * @const EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED
     */
    public const EBIZCHARGE_RESPONSE_RESULT_CODE_AUTHENTICATED = "A";

    /**
     * @const EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR
     */
    public const EBIZCHARGE_RESPONSE_RESULT_CODE_ERROR = "E";

    /**
     * @const EBIZCHARGE_RESPONSE_RESULT_CODE_DECLINED
     */
    public const EBIZCHARGE_RESPONSE_RESULT_CODE_DECLINED = "D";

    /**
     * @const EBIZCHARGE_USER_ACTION_TYPE_CANCELE
     */
    public const EBIZCHARGE_USER_ACTION_TYPE_CANCELE = "cancel";

    /**
     * @const EBIZCHARGE_USER_ACTION_TYPE_OK
     */
    public const EBIZCHARGE_USER_ACTION_TYPE_OK = "OK";

    /**
     * @const EBIZCHARGE_TRANSACTION_PRE_AUTH_MIN_AMOUNT;
     */
    public const EBIZCHARGE_TRANSACTION_PRE_AUTH_MIN_AMOUNT = 0.05;

    /**
     * @const EBIZCHARGE_FRAMEWORK_STATE_TYPE_ADMINHTML;
     */

    public const EBIZCHARGE_FRAMEWORK_STATE_TYPE_ADMINHTML = "adminhtml";

    /**
     * EbizCharge Transaction Type
     *
     * @const EBIZCHARGE_TRANSACTION_TYPE_AUTHORIZATION
     */
    public const EBIZCHARGE_TRANSACTION_TYPE_AUTHORIZATION = 'authorization';

    /**
     * Payment Type Option Select Saved Card
     *
     * @const: PAYMENT_TYPE_OPTION_SELECT_SAVED_CARD
     */
    public const PAYMENT_TYPE_OPTION_SELECT_SAVED_CARD = 'saved-card';

    /**
     * Payment Type Option Select Update Card
     *
     * @const: PAYMENT_TYPE_OPTION_SELECT_UPDATE_CARD
     */
    public const PAYMENT_TYPE_OPTION_SELECT_UPDATE_CARD = 'update-saved-card';

    /**
     * Payment Type Option Select Add New Card
     *
     * @const: PAYMENT_TYPE_OPTION_SELECT_ADD_NEW_CARD
     */
    public const PAYMENT_TYPE_OPTION_SELECT_ADD_NEW_CARD = 'add-new-card';

    /**
     * Payment Type Option Select Saved ACH
     *
     * @const: PAYMENT_TYPE_OPTION_SELECT_SAVED_ACH
     */
    public const PAYMENT_TYPE_OPTION_SELECT_SAVED_ACH = 'saved-ach';

    /**
     * Payment Type Option Select Update ACH
     *
     * @const: PAYMENT_TYPE_OPTION_SELECT_UPDATE_ACH
     */
    public const PAYMENT_TYPE_OPTION_SELECT_UPDATE_ACH = 'update-saved-ach';

    /**
     * Payment Type Option Select Add New ACH
     *
     * @const: PAYMENT_TYPE_OPTION_SELECT_ADD_NEW_ACH
     */
    public const PAYMENT_TYPE_OPTION_SELECT_ADD_NEW_ACH = 'add-new-ach';

    /**
     * Payment Type Option Select Pay Later
     *
     * @const: PAYMENT_TYPE_OPTION_SELECT_PAY_LATER
     */
    public const PAYMENT_TYPE_OPTION_SELECT_PAY_LATER = 'pay-later';

    /**
     * Payment Type Option Types
     *
     * @const: ACH_PAYMENT_OPTION_TYPES
     */
    public const ACH_PAYMENT_OPTION_TYPES = [
        self::EBIZCHARGE_PAYMENT_OPTION_BANK_ACCOUNT_TYPE_CHECKING,
        self::EBIZCHARGE_PAYMENT_OPTION_BANK_ACCOUNT_TYPE_SAVINGS

    ];

}
